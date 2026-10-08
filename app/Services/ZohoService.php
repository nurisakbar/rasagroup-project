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
        $this->orgId = config('services.zoho.org_id', env('ZOHO_ORG_ID'));
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
     * Create Order -> Invoice -> Payment flow
     */
    public function createOrderToPaymentFlow(Order $order)
    {
        try {
            $order->loadMissing(['user', 'items.product']);
            
            $customerId = $this->resolveCustomer($order->user);
            
            // 1. Create Sales Order
            $lineItems = [];
            foreach ($order->items as $item) {
                $itemId = $this->resolveItem($item->product);
                $lineItems[] = [
                    'item_id' => $itemId,
                    'quantity' => $item->quantity,
                    'rate' => $item->price,
                ];
            }

            // Create SO
            $soResponse = $this->client()->post("{$this->apiDomain}/books/v3/salesorders", [
                'customer_id' => $customerId,
                'reference_number' => $order->order_number,
                'date' => $order->created_at->format('Y-m-d'),
                'line_items' => $lineItems,
                'custom_fields' => [
                    ['api_name' => 'cf_source', 'value' => 'Website/App']
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
            $this->client()->post("{$this->apiDomain}/books/v3/salesorders/{$salesorderId}/status/open");

            // 2. Create Invoice
            $invoiceLineItems = [];
            foreach ($zohoLineItems as $zItem) {
                $invoiceLineItems[] = [
                    'item_id' => $zItem['item_id'],
                    'salesorder_item_id' => $zItem['line_item_id'],
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
            $invoiceBalance = $invResponse->json('invoice.balance');
            $order->update(['zoho_invoice_id' => $invoiceId]);

            // Sent Invoice
            $this->client()->post("{$this->apiDomain}/books/v3/invoices/{$invoiceId}/status/sent");

            // 3. Create Payment
            if ($order->total_amount > 0) {
                // If payment method exists, maybe map to Zoho payment_mode, fallback to banktransfer
                $mode = str_contains(strtolower($order->payment_method), 'va') ? 'banktransfer' : 'cash';
                if ($order->payment_method === 'faspay_qris') $mode = 'others';

                $payResponse = $this->client()->post("{$this->apiDomain}/books/v3/customerpayments", [
                    'customer_id' => $customerId,
                    'payment_mode' => $mode,
                    'amount' => $order->total_amount,
                    'date' => $order->finance_approved_at ? $order->finance_approved_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'invoices' => [
                        [
                            'invoice_id' => $invoiceId,
                            'amount_applied' => min($invoiceBalance, $order->total_amount)
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
