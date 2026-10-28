<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
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
     * Check if Zoho Books integration is configured
     */
    public static function isConfigured(): bool
    {
        return !empty(config('services.zoho.client_id'))
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
     * Get active access token (cached or generate new)
     */
    public function getAccessToken(): string
    {
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
        try {
            $order->loadMissing(['user', 'items.product', 'sourceWarehouse']);
            
            $customerId = $this->resolveCustomer($order->user);
            $locationId = $this->resolveLocationId($order->sourceWarehouse);
            
            // 1. Resolve or Create Sales Order
            $salesorderId = $order->zoho_salesorder_id;
            $zohoLineItems = [];

            $usesPpn = ($order->pakai_ppn || ($order->user && $order->user->usesPpn()));
            $taxId = $usesPpn ? $this->resolvePpnTaxId() : null;

            // Calculate Gross DPP from line items
            $grossDpp = 0.0;
            foreach ($order->items as $item) {
                $grossDpp += round($item->price * $item->quantity, 2);
            }

            // Resolve Discount
            $shippingCost = (float) ($order->shipping_cost ?? 0);
            $totalAmount = (float) $order->total_amount;
            $goodsTotal = max(0.0, $totalAmount - $shippingCost - (float) ($order->payment_fee ?? 0));

            $discountAmount = 0.0;
            if ((float) ($order->discount_amount ?? 0) > 0) {
                $discountAmount = (float) $order->discount_amount;
            } else {
                $netDpp = $usesPpn ? round($goodsTotal / 1.11, 2) : $goodsTotal;
                if ($grossDpp > $netDpp) {
                    $discountAmount = round($grossDpp - $netDpp, 2);
                }
            }

            // Calculate estimated tax and total with discount
            $taxableDpp = max(0.0, $grossDpp - $discountAmount);
            $estimatedTax = ($usesPpn && $taxId) ? round($taxableDpp * 0.11, 2) : 0.0;
            $estimatedTotal = $taxableDpp + $estimatedTax + $shippingCost;
            $roundingAdjustment = round($totalAmount - $estimatedTotal, 2);

            $discountPayload = [];
            if ($discountAmount > 0.01) {
                $discountPayload = [
                    'discount' => $discountAmount,
                    'discount_type' => 'entity_level',
                    'is_discount_before_tax' => true,
                ];
            }

            $adjustmentPayload = [];
            if (abs($roundingAdjustment) > 0.001 && abs($roundingAdjustment) <= 5.0) {
                $adjustmentPayload = [
                    'adjustment' => $roundingAdjustment,
                    'adjustment_description' => 'Pembulatan',
                ];
            }

            if (!$salesorderId) {
                $lineItems = [];
                foreach ($order->items as $item) {
                    $itemId = $this->resolveItem($item->product);
                    $lineItem = [
                        'item_id' => $itemId,
                        'quantity' => $item->quantity,
                        'rate' => $item->price,
                        'location_id' => $locationId,
                    ];
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
                ], $discountPayload, $adjustmentPayload);

                $soResponse = $this->client()->post("{$this->apiDomain}/books/v3/salesorders", $soData);

                if (!$soResponse->successful()) {
                    throw new \Exception('Failed to create Sales Order: ' . $soResponse->body());
                }

                $salesorderId = $soResponse->json('salesorder.salesorder_id');
                $zohoLineItems = $soResponse->json('salesorder.line_items');
                $order->update(['zoho_salesorder_id' => $salesorderId]);

                // Open SO
                $this->openSalesOrder($salesorderId);
            } else {
                // If invoice is not created yet, retrieve SO line items
                if (!$order->zoho_invoice_id) {
                    $soData = $this->getSalesOrder($salesorderId);
                    $zohoLineItems = $soData['line_items'] ?? [];
                    if (!empty($soData['branch_id'])) {
                        $locationId = (string) $soData['branch_id'];
                    }
                }
            }

            // 2. Resolve or Create Invoice
            $invoiceId = $order->zoho_invoice_id;
            $invoiceBalance = 0.0;

            if (!$invoiceId) {
                $invoiceLineItems = [];
                foreach ($zohoLineItems as $zItem) {
                    $invItem = [
                        'item_id' => $zItem['item_id'],
                        'salesorder_item_id' => $zItem['line_item_id'] ?? null,
                        'quantity' => $zItem['quantity'],
                        'rate' => $zItem['rate'],
                        'location_id' => $zItem['location_id'] ?? $locationId,
                    ];
                    $appliedTaxId = !empty($zItem['tax_id']) ? $zItem['tax_id'] : $taxId;
                    if ($appliedTaxId) {
                        $invItem['tax_id'] = $appliedTaxId;
                    }
                    $invoiceLineItems[] = $invItem;
                }

                $invData = array_merge([
                    'customer_id' => $customerId,
                    'branch_id' => $locationId,
                    'location_id' => $locationId,
                    'reference_number' => $order->order_number,
                    'line_items' => $invoiceLineItems,
                    'shipping_charge' => $order->shipping_cost ?? 0,
                ], $discountPayload, $adjustmentPayload);

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
