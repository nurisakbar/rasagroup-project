<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
            $order->loadMissing(['user', 'items.product']);
            
            $customerId = $this->resolveCustomer($order->user);
            
            // 1. Resolve or Create Sales Order
            $salesorderId = $order->zoho_salesorder_id;
            $zohoLineItems = [];

            if (!$salesorderId) {
                $lineItems = [];
                foreach ($order->items as $item) {
                    $itemId = $this->resolveItem($item->product);
                    $lineItems[] = [
                        'item_id' => $itemId,
                        'quantity' => $item->quantity,
                        'rate' => $item->price,
                    ];
                }

                $soResponse = $this->client()->post("{$this->apiDomain}/books/v3/salesorders", [
                    'customer_id' => $customerId,
                    'reference_number' => $order->order_number,
                    'date' => $order->created_at->format('Y-m-d'),
                    'line_items' => $lineItems,
                    'custom_fields' => [
                        ['api_name' => 'cf_source', 'value' => 'rasaconnect']
                    ],
                    'shipping_charge' => $order->shipping_cost ?? 0,
                ]);

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
                }
            }

            // 2. Resolve or Create Invoice
            $invoiceId = $order->zoho_invoice_id;
            $invoiceBalance = 0.0;

            if (!$invoiceId) {
                $invoiceLineItems = [];
                foreach ($zohoLineItems as $zItem) {
                    $invoiceLineItems[] = [
                        'item_id' => $zItem['item_id'],
                        'salesorder_item_id' => $zItem['line_item_id'] ?? null,
                        'quantity' => $zItem['quantity'],
                        'rate' => $zItem['rate'],
                    ];
                }

                $invResponse = $this->client()->post("{$this->apiDomain}/books/v3/invoices", [
                    'customer_id' => $customerId,
                    'reference_number' => $order->order_number,
                    'line_items' => $invoiceLineItems,
                    'shipping_charge' => $order->shipping_cost ?? 0,
                ]);

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

                $payResponse = $this->client()->post("{$this->apiDomain}/books/v3/customerpayments", [
                    'customer_id' => $customerId,
                    'payment_mode' => $mode,
                    'amount' => $order->total_amount,
                    'date' => $order->finance_approved_at ? $order->finance_approved_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'invoices' => [
                        [
                            'invoice_id' => $invoiceId,
                            'amount_applied' => min($invoiceBalance, (float) $order->total_amount)
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

            Log::info("Successfully synced Order {$order->order_number} to Zoho.");

        } catch (\Exception $e) {
            Log::error('Zoho Integration Error: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
            throw $e;
        }
    }
}
