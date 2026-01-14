<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private $apiKey;
    private $baseUrl = 'https://api.xendit.co';
    private $invoiceUrl = 'https://checkout.xendit.co';

    public function __construct()
    {
        $this->apiKey = config('services.xendit.secret_key');
    }

    /**
     * Create invoice for payment
     */
    public function createInvoice($order, $customer, $items)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post($this->baseUrl . '/v2/invoices', [
                    'external_id' => $order->order_number,
                    'amount' => $order->total_amount,
                    'description' => 'Pembayaran untuk pesanan #' . $order->order_number,
                    'invoice_duration' => 86400, // 24 hours
                    'customer' => [
                        'given_names' => $customer['name'],
                        'email' => $customer['email'],
                        'mobile_number' => $customer['phone'] ?? null,
                    ],
                    'customer_notification_preference' => [
                        'invoice_created' => ['email', 'sms'],
                        'invoice_reminder' => ['email', 'sms'],
                        'invoice_expired' => ['email', 'sms'],
                        'invoice_paid' => ['email', 'sms'],
                    ],
                    'success_redirect_url' => route('checkout.success', $order),
                    'failure_redirect_url' => route('checkout.index') . '?error=xendit_failed',
                    'items' => $items,
                    'fees' => [
                        [
                            'type' => 'ADMIN',
                            'value' => 0, // No admin fee
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('Xendit invoice created', [
                    'invoice_id' => $result['id'] ?? null,
                    'invoice_url' => $result['invoice_url'] ?? null,
                    'status' => $result['status'] ?? null,
                    'has_invoice_url' => isset($result['invoice_url']),
                ]);
                
                return $result;
            }

            Log::error('Xendit API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Xendit Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get invoice by ID
     */
    public function getInvoice($invoiceId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get($this->baseUrl . '/v2/invoices/' . $invoiceId);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Xendit Get Invoice Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        $webhookToken = config('services.xendit.webhook_token');
        
        if (empty($webhookToken)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookToken);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get payment methods available
     */
    public function getAvailablePaymentMethods()
    {
        return [
            'credit_card' => 'Kartu Kredit',
            'debit_card' => 'Kartu Debit',
            'bank_transfer' => 'Transfer Bank',
            'ewallet' => 'E-Wallet',
            'qr_code' => 'QRIS',
            'paylater' => 'PayLater',
        ];
    }
}



