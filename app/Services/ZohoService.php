<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\Pricing\SalesLinePricing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZohoService
{
    protected string $apiDomain = 'https://www.zohoapis.com';
    protected string $orgId;
    
    public function __construct()
    {
        $this->orgId = (string) config('services.zoho.org_id', env('ZOHO_ORG_ID'));
    }

    /**
     * Saklar integrasi Zoho (ZOHO_ENABLED). Dimatikan di server dev agar order uji tidak masuk ke Zoho.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('services.zoho.enabled', true);
    }

    /**
     * Check if Zoho Books integration is configured
     */
    public static function isConfigured(): bool
    {
        return static::isEnabled()
            && !empty(config('services.zoho.client_id'))
            && !empty(config('services.zoho.client_secret'))
            && !empty(config('services.zoho.refresh_token'))
            && !empty(config('services.zoho.org_id'));
    }

    /**
     * Get list of branches/locations from Zoho Books
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBranches(): array
    {
        if (!static::isConfigured()) {
            return [];
        }

        return Cache::remember('zoho_branches_list', 86400, function () {
            try {
                $response = $this->client()->get("{$this->apiDomain}/books/v3/branches");
                if ($response->successful()) {
                    return $response->json('branches') ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning('ZohoService: failed to fetch branches from Zoho', ['error' => $e->getMessage()]);
            }

            return [];
        });
    }

    /**
     * Resolve the Zoho Location/Branch ID for a warehouse
     */
    public function resolveLocationId(?Warehouse $warehouse): string
    {
        $defaultBranchId = '7386545000000093096'; // Head Office (Primary)

        if (!$warehouse) {
            return $defaultBranchId;
        }

        $branches = $this->getBranches();
        $activeBranchIds = [];
        $primaryBranchId = $defaultBranchId;

        foreach ($branches as $b) {
            $bId = (string) ($b['branch_id'] ?? '');
            if (!empty($b['is_primary_branch'])) {
                $primaryBranchId = $bId;
            }
            if (!empty($b['is_branch_active']) && ($b['branch_status'] ?? '') === 'active') {
                $activeBranchIds[] = $bId;
            }
        }

        // 1. Direct explicit mapping from warehouse model
        if (!empty($warehouse->zoho_location_id)) {
            $mappedId = (string) $warehouse->zoho_location_id;
            // If the mapped location is active in Zoho, use it
            if (empty($activeBranchIds) || in_array($mappedId, $activeBranchIds, true)) {
                return $mappedId;
            }

            Log::info("ZohoService: Mapped location {$mappedId} for warehouse {$warehouse->name} is inactive in Zoho. Falling back to primary branch {$primaryBranchId}.");
            return $primaryBranchId;
        }

        // 2. Name / Code heuristic fallback
        $name = strtolower($warehouse->name ?? '');

        foreach ($branches as $b) {
            $bId = (string) ($b['branch_id'] ?? '');
            $bName = strtolower((string) ($b['branch_name'] ?? ''));

            if (!empty($activeBranchIds) && !in_array($bId, $activeBranchIds, true)) {
                continue;
            }

            if (str_contains($name, 'bekasi') && str_contains($bName, 'bekasi')) return $bId;
            if (str_contains($name, 'maggiore') && str_contains($bName, 'maggiore')) return $bId;
            if (str_contains($name, 'tomang') && str_contains($bName, 'tomang')) return $bId;
            if (str_contains($name, 'surabaya') && str_contains($bName, 'surabaya')) return $bId;
        }

        return $primaryBranchId;
    }

    /**
     * Resolve PPN (11%) Tax ID in Zoho Books
     */
    public function resolvePpnTaxId(): ?string
    {
        return Cache::remember('zoho_ppn_tax_id', 86400, function () {
            try {
                $response = $this->client()->get("{$this->apiDomain}/books/v3/settings/taxes");
                if ($response->successful()) {
                    $taxes = $response->json('taxes') ?? [];
                    foreach ($taxes as $tax) {
                        if (($tax['tax_percentage'] ?? 0) == 11 && strtolower((string) ($tax['status'] ?? '')) === 'active') {
                            return (string) $tax['tax_id'];
                        }
                    }
                    foreach ($taxes as $tax) {
                        if (str_contains(strtolower((string) ($tax['tax_name'] ?? '')), 'ppn') && strtolower((string) ($tax['status'] ?? '')) === 'active') {
                            return (string) $tax['tax_id'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ZohoService: failed to fetch taxes from Zoho', ['error' => $e->getMessage()]);
            }

            return '7386545000001141159'; // Default active PPN 11% Tax ID in Zoho Books
        });
    }

    /**
     * Account ID Zoho untuk akun penjualan (default kode 41101 "Sales"), dicari dari chart of accounts.
     * Gagal keras bila akun tidak ditemukan agar penjualan tidak terposting ke akun yang salah.
     */
    public function resolveSalesAccountId(): string
    {
        $code = trim((string) config('services.zoho.sales_account_code', '41101'));
        $cacheKey = 'zoho_sales_account_id_' . $code;

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = $this->client()->get("{$this->apiDomain}/books/v3/chartofaccounts", [
            'filter_by' => 'AccountType.Income',
            'per_page' => 200,
        ]);

        if ($response->successful()) {
            foreach ($response->json('chartofaccounts') ?? [] as $account) {
                if (trim((string) ($account['account_code'] ?? '')) === $code && ! empty($account['account_id'])) {
                    $accountId = (string) $account['account_id'];
                    Cache::put($cacheKey, $accountId, 86400);

                    return $accountId;
                }
            }
        }

        throw new \Exception("Zoho: akun penjualan dengan kode {$code} tidak ditemukan di chart of accounts.");
    }

    /**
     * Get active access token (cached or generate new)
     */
    public function getAccessToken(): string
    {
        if (! static::isEnabled()) {
            throw new \RuntimeException('Integrasi Zoho dimatikan (ZOHO_ENABLED=false).');
        }

        return Cache::remember('zoho_access_token', 3500, function () {
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'refresh_token' => config('services.zoho.refresh_token', env('ZOHO_REFRESH_TOKEN')),
                'client_id' => config('services.zoho.client_id', env('ZOHO_CLIENT_ID')),
                'client_secret' => config('services.zoho.client_secret', env('ZOHO_CLIENT_SECRET')),
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'];
            }

            Log::error('Failed to get Zoho Access Token', [
                'response' => $response->body()
            ]);

            throw new \Exception('Gagal mendapatkan Zoho Access Token: ' . $response->body());
        });
    }

    /**
     * Base HTTP Client configured with Authorization and Org ID
     */
    protected function client()
    {
        if (! static::isEnabled()) {
            throw new \RuntimeException('Integrasi Zoho dimatikan (ZOHO_ENABLED=false).');
        }

        return Http::withToken($this->getAccessToken())
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withQueryParameters(['organization_id' => $this->orgId]);
    }

    /**
     * Resolve Customer ID in Zoho
     */
    public function resolveCustomer(User $user): string
    {
        if ($user->zoho_customer_id) {
            return $user->zoho_customer_id;
        }

        // Search by email
        $response = $this->client()->get("{$this->apiDomain}/books/v3/contacts", [
            'email_contains' => $user->email,
        ]);

        if ($response->successful() && !empty($response->json('contacts'))) {
            $contact = $response->json('contacts')[0];
            $user->update(['zoho_customer_id' => $contact['contact_id']]);
            return $contact['contact_id'];
        }

        // Create new
        $response = $this->client()->post("{$this->apiDomain}/books/v3/contacts", [
            'contact_name' => $user->name,
            'company_name' => $user->name,
            'contact_type' => 'customer',
            'customer_sub_type' => 'individual',
            'billing_address' => [
                'attention' => $user->name,
                'address' => 'Customer Address', // Fallback
                'country' => 'Indonesia',
            ],
            'contact_persons' => [
                [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                ]
            ]
        ]);

        if ($response->successful()) {
            $contactId = $response->json('contact.contact_id');
            $user->update(['zoho_customer_id' => $contactId]);
            return $contactId;
        }

        Log::error('Zoho: Failed to create customer', ['response' => $response->body()]);
        throw new \Exception('Failed to create customer in Zoho');
    }

    /**
     * Resolve Item ID in Zoho
     */
    public function resolveItem(Product $product): string
    {
        if ($product->zoho_item_id) {
            return $product->zoho_item_id;
        }

        $sku = $product->code ?: 'SKU-' . $product->id;

        // Search by SKU via Inventory API
        $response = $this->client()->get("{$this->apiDomain}/inventory/v1/items", [
            'sku' => $sku,
        ]);

        if ($response->successful() && !empty($response->json('items'))) {
            $item = $response->json('items')[0];
            $product->update(['zoho_item_id' => $item['item_id']]);
            return $item['item_id'];
        }

        // Create new item in Books
        $response = $this->client()->post("{$this->apiDomain}/books/v3/items", [
            'name' => $product->display_name ?? $product->name,
            'sku' => $sku,
            'rate' => $product->price ?? 0,
            'item_type' => 'inventory', // Assuming inventory, might be 'sales' if not tracked
        ]);

        if ($response->successful()) {
            $itemId = $response->json('item.item_id');
            $product->update(['zoho_item_id' => $itemId]);
            return $itemId;
        }

        Log::error('Zoho: Failed to create item', ['response' => $response->body()]);
        throw new \Exception('Failed to create item in Zoho: ' . $product->name);
    }

    /**
     * Mark a draft invoice as sent in Zoho Books
     * Endpoint: POST /books/v3/invoices/{invoice_id}/status/sent
     * Documentation: https://www.zoho.com/books/api/v3/invoices/#mark-an-invoice-as-sent
     */
    public function markInvoiceAsSent(string $invoiceId): bool
    {
        $response = $this->client()->post("{$this->apiDomain}/books/v3/invoices/{$invoiceId}/status/sent");

        if (!$response->successful() || $response->json('code') !== 0) {
            $msg = $response->json('message') ?? $response->body();
            // Idempotent: If invoice is already marked as sent or paid, treat as success
            if (str_contains(strtolower($msg), 'already') || str_contains(strtolower($msg), 'sent') || str_contains(strtolower($msg), 'paid')) {
                Log::info("Zoho: Invoice {$invoiceId} is already marked as sent/processed.");
                return true;
            }

            Log::error("Zoho: Failed to mark invoice {$invoiceId} as sent", [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body()
            ]);

            throw new \Exception("Failed to mark Invoice {$invoiceId} as sent in Zoho: {$msg}");
        }

        Log::info("Zoho: Successfully marked invoice {$invoiceId} as sent.");
        return true;
    }

    /**
     * Open a draft Sales Order in Zoho Books
     * Endpoint: POST /books/v3/salesorders/{salesorder_id}/status/open
     */
    public function openSalesOrder(string $salesorderId): bool
    {
        $response = $this->client()->post("{$this->apiDomain}/books/v3/salesorders/{$salesorderId}/status/open");

        if (!$response->successful() || $response->json('code') !== 0) {
            $msg = $response->json('message') ?? $response->body();
            if (str_contains(strtolower($msg), 'already') || str_contains(strtolower($msg), 'open')) {
                Log::info("Zoho: Sales Order {$salesorderId} is already open.");
                return true;
            }

            Log::error("Zoho: Failed to open Sales Order {$salesorderId}", [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body()
            ]);

            throw new \Exception("Failed to open Sales Order {$salesorderId} in Zoho: {$msg}");
        }

        Log::info("Zoho: Successfully opened Sales Order {$salesorderId}.");
        return true;
    }

    /**
     * Get Invoice details from Zoho Books
     */
    public function getInvoice(string $invoiceId): ?array
    {
        $response = $this->client()->get("{$this->apiDomain}/books/v3/invoices/{$invoiceId}");
        if ($response->successful()) {
            return $response->json('invoice');
        }
        return null;
    }

    /**
     * Get Sales Order details from Zoho Books
     */
    public function getSalesOrder(string $salesorderId): ?array
    {
        $response = $this->client()->get("{$this->apiDomain}/books/v3/salesorders/{$salesorderId}");
        if ($response->successful()) {
            return $response->json('salesorder');
        }
        return null;
    }

    /**
     * Create Order -> Invoice -> Payment flow
     */
    public function createOrderToPaymentFlow(Order $order)
    {
        if (! static::isEnabled()) {
            Log::info('Zoho: sync dilewati karena integrasi Zoho dimatikan (ZOHO_ENABLED=false).', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return;
        }

        try {
            $order->loadMissing(['user', 'items.product', 'sourceWarehouse']);
            
            $customerId = $this->resolveCustomer($order->user);
            $locationId = $this->resolveLocationId($order->sourceWarehouse);
            
            // 1. Resolve or Create Sales Order
            $salesorderId = $order->zoho_salesorder_id;
            $zohoLineItems = [];

            $usesPpn = ($order->pakai_ppn || ($order->user && $order->user->usesPpn()));
            $taxId = $usesPpn ? $this->resolvePpnTaxId() : null;

            // Harga per item, sama dengan payload QAD: rate = harga list (DPP katalog), diskon = % per baris.
            // Diskon lama di level order ikut dilebur ke tiap baris (lihat SalesLinePricing).
            $linePricing = [];
            $netDpp = 0.0;
            foreach ($order->items as $index => $item) {
                $pricing = SalesLinePricing::forItem($item, (float) $item->soldBaseUnitPrice());
                $linePricing[$index] = $pricing;
                $netDpp += round($pricing['net_price'] * (float) $item->quantity, 2);
            }

            $shippingCost = (float) ($order->shipping_cost ?? 0);
            $totalAmount = (float) $order->total_amount;
            $estimatedTax = ($usesPpn && $taxId) ? round($netDpp * 0.11, 2) : 0.0;
            $estimatedTotal = $netDpp + $estimatedTax + $shippingCost;
            $roundingAdjustment = round($totalAmount - $estimatedTotal, 2);

            $adjustmentPayload = [];
            if (abs($roundingAdjustment) > 0.001 && abs($roundingAdjustment) <= 5.0) {
                $adjustmentPayload = [
                    'adjustment' => $roundingAdjustment,
                    'adjustment_description' => 'Pembulatan',
                ];
            }

            $itemLevelDiscount = [
                'discount_type' => 'item_level',
                'is_discount_before_tax' => true,
            ];

            $salesAccountId = $this->resolveSalesAccountId();
            $soRecord = [];

            if (!$salesorderId) {
                $lineItems = [];
                foreach ($order->items->values() as $index => $item) {
                    $pricing = $linePricing[$index];
                    $lineItem = [
                        'item_id' => $this->resolveItem($item->product),
                        'quantity' => $item->quantity,
                        'rate' => $pricing['list_price'],
                        'location_id' => $locationId,
                    ];
                    if ($pricing['discount_percent'] > 0) {
                        $lineItem['discount'] = $pricing['discount_percent'] . '%';
                    }
                    if ($taxId) {
                        $lineItem['tax_id'] = $taxId;
                    }
                    $lineItems[] = $lineItem;
                }

                $soData = array_merge([
                    'customer_id' => $customerId,
                    'branch_id' => $locationId,
                    'location_id' => $locationId,
                    'reference_number' => $order->order_number,
                    'date' => $order->created_at->format('Y-m-d'),
                    'line_items' => $lineItems,
                    'custom_fields' => [
                        ['api_name' => 'cf_source', 'value' => 'rasaconnect']
                    ],
                    'shipping_charge' => $order->shipping_cost ?? 0,
                ], $itemLevelDiscount, $adjustmentPayload);

                $soResponse = $this->client()->post("{$this->apiDomain}/books/v3/salesorders", $soData);

                if (!$soResponse->successful()) {
                    throw new \Exception('Failed to create Sales Order: ' . $soResponse->body());
                }

                $soRecord = $soResponse->json('salesorder') ?? [];
                $salesorderId = $soRecord['salesorder_id'] ?? null;
                $zohoLineItems = $soRecord['line_items'] ?? [];
                $order->update(['zoho_salesorder_id' => $salesorderId]);

                // Open SO
                $this->openSalesOrder($salesorderId);
            } else {
                // If invoice is not created yet, retrieve SO line items
                if (!$order->zoho_invoice_id) {
                    $soRecord = $this->getSalesOrder($salesorderId) ?? [];
                    $zohoLineItems = $soRecord['line_items'] ?? [];
                    if (!empty($soRecord['branch_id'])) {
                        $locationId = (string) $soRecord['branch_id'];
                    }
                }
            }

            // 2. Resolve or Create Invoice
            $invoiceId = $order->zoho_invoice_id;
            $invoiceBalance = 0.0;

            if (!$invoiceId) {
                // SO lama (sebelum diskon per item) memakai harga net + diskon level dokumen: invoice mengikuti SO tsb.
                $soIsItemLevel = ($soRecord['discount_type'] ?? '') === 'item_level'
                    && count($zohoLineItems) === count($linePricing);

                $invoiceLineItems = [];
                foreach (array_values($zohoLineItems) as $index => $zItem) {
                    $invItem = [
                        'item_id' => $zItem['item_id'],
                        'salesorder_item_id' => $zItem['line_item_id'] ?? null,
                        'quantity' => $zItem['quantity'],
                        'rate' => $soIsItemLevel ? $linePricing[$index]['list_price'] : $zItem['rate'],
                        'location_id' => $zItem['location_id'] ?? $locationId,
                        'account_id' => $salesAccountId,
                    ];
                    if ($soIsItemLevel && $linePricing[$index]['discount_percent'] > 0) {
                        $invItem['discount'] = $linePricing[$index]['discount_percent'] . '%';
                    }
                    $appliedTaxId = !empty($zItem['tax_id']) ? $zItem['tax_id'] : $taxId;
                    if ($appliedTaxId) {
                        $invItem['tax_id'] = $appliedTaxId;
                    }
                    $invoiceLineItems[] = $invItem;
                }

                if ($soIsItemLevel) {
                    $invoiceTotals = array_merge($itemLevelDiscount, $adjustmentPayload);
                } else {
                    $invoiceTotals = [];
                    if ((float) ($soRecord['discount'] ?? 0) > 0) {
                        $invoiceTotals['discount'] = $soRecord['discount'];
                        $invoiceTotals['discount_type'] = 'entity_level';
                        $invoiceTotals['is_discount_before_tax'] = (bool) ($soRecord['is_discount_before_tax'] ?? true);
                    }
                    if (abs((float) ($soRecord['adjustment'] ?? 0)) > 0.001) {
                        $invoiceTotals['adjustment'] = (float) $soRecord['adjustment'];
                        $invoiceTotals['adjustment_description'] = $soRecord['adjustment_description'] ?? 'Pembulatan';
                    }
                }

                $invData = array_merge([
                    'customer_id' => $customerId,
                    'branch_id' => $locationId,
                    'location_id' => $locationId,
                    'reference_number' => $order->order_number,
                    'line_items' => $invoiceLineItems,
                    'shipping_charge' => $order->shipping_cost ?? 0,
                ], $invoiceTotals);

                $invResponse = $this->client()->post("{$this->apiDomain}/books/v3/invoices", $invData);

                if (!$invResponse->successful()) {
                    throw new \Exception('Failed to create Invoice: ' . $invResponse->body());
                }

                $invoiceId = $invResponse->json('invoice.invoice_id');
                $invoiceBalance = (float) $invResponse->json('invoice.balance');
                $order->update(['zoho_invoice_id' => $invoiceId]);

                // Mark Invoice as Sent
                $this->markInvoiceAsSent($invoiceId);
            } else {
                $invData = $this->getInvoice($invoiceId);
                $invoiceBalance = (float) ($invData['balance'] ?? $order->total_amount);
                if (!empty($invData['branch_id'])) {
                    $locationId = (string) $invData['branch_id'];
                }

                // Ensure invoice is marked as sent if it is still in draft
                if (($invData['status'] ?? '') === 'draft') {
                    $this->markInvoiceAsSent($invoiceId);
                }
            }

            // 3. Create Payment
            if (!$order->zoho_payment_id && $order->total_amount > 0) {
                // If payment method exists, maybe map to Zoho payment_mode, fallback to banktransfer
                $pm = strtolower((string) ($order->payment_method ?? ''));
                $mode = str_contains($pm, 'va') ? 'banktransfer' : 'cash';
                if ($order->payment_method === 'faspay_qris') $mode = 'others';

                $paymentAmount = ($invoiceBalance > 0 && abs($invoiceBalance - (float) $order->total_amount) < 1.0)
                    ? $invoiceBalance
                    : (float) $order->total_amount;

                $payResponse = $this->client()->post("{$this->apiDomain}/books/v3/customerpayments", [
                    'customer_id' => $customerId,
                    'branch_id' => $locationId,
                    'location_id' => $locationId,
                    'payment_mode' => $mode,
                    'amount' => $paymentAmount,
                    'date' => $order->finance_approved_at ? $order->finance_approved_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'invoices' => [
                        [
                            'invoice_id' => $invoiceId,
                            'amount_applied' => $paymentAmount,
                        ]
                    ],
                    'reference_number' => $order->order_number
                ]);

                if ($payResponse->successful()) {
                    $paymentId = $payResponse->json('payment.payment_id');
                    $order->update(['zoho_payment_id' => $paymentId]);
                } else {
                    throw new \Exception('Failed to create Payment: ' . $payResponse->body());
                }
            }

            Log::info("Successfully synced Order {$order->order_number} to Zoho (Branch: {$locationId}).");

        } catch (\Exception $e) {
            Log::error('Zoho Integration Error: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
            throw $e;
        }
    }
}
