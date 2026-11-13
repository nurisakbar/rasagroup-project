<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyExternalFinanceApprovalNeeded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Order $order, public bool $force = false)
    {
    }

    public function handle(): void
    {
        $url = config('services.finance_approval.webhook_url');

        if (blank($url)) {
            Log::info('Finance approval webhook skipped: FINANCE_APPROVAL_WEBHOOK_URL kosong', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
            ]);

            $this->order->update([
                'edms_notification_status' => 'SKIPPED',
                'edms_notification_error' => 'FINANCE_APPROVAL_WEBHOOK_URL belum dikonfigurasi di environment.',
            ]);
            return;
        }

        $this->order->loadMissing(['user', 'sourceWarehouse', 'items.product']);

        if (!$this->force && !$this->order->isAwaitingFinanceApproval()) {
            Log::info('Finance approval webhook skipped: order tidak menunggu approval', [
                'order_id' => $this->order->id,
                'finance_approved' => $this->order->finance_approved,
                'payment_method' => $this->order->payment_method,
            ]);
            return;
        }

        $payload = $this->buildPayload();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'RasaConnect/1.0 (Linux; Laravel)',
        ];

        $token = config('services.finance_approval.webhook_token');
        if (filled($token)) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $secret = config('services.finance_approval.webhook_secret');
        if (filled($secret)) {
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($payload), $secret);
        }

        Log::info('Finance approval webhook: mengirim notifikasi', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'url' => $url,
        ]);

        try {
            $response = Http::timeout((int) config('services.finance_approval.timeout', 15))
                ->withHeaders($headers)
                ->post($url, $payload);

            if ($response->failed()) {
                $errBody = $response->body();
                Log::error('Finance approval webhook gagal', [
                    'order_id' => $this->order->id,
                    'status' => $response->status(),
                    'body' => $errBody,
                ]);

                $this->order->update([
                    'edms_notification_status' => 'FAILED',
                    'edms_notified_at' => now(),
                    'edms_notification_error' => 'HTTP ' . $response->status() . ': ' . substr($errBody, 0, 500),
                ]);

                $response->throw();
            }

            $resBody = $response->json() ?? $response->body();
            Log::info('Finance approval webhook sukses', [
                'order_id' => $this->order->id,
                'status' => $response->status(),
                'body' => $resBody,
            ]);

            $this->order->update([
                'edms_notification_status' => 'SUCCESS',
                'edms_notified_at' => now(),
                'edms_notification_response' => is_array($resBody) ? json_encode($resBody) : (string) $resBody,
                'edms_notification_error' => null,
            ]);
        } catch (\Throwable $e) {
            $this->order->update([
                'edms_notification_status' => 'FAILED',
                'edms_notified_at' => now(),
                'edms_notification_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function buildPayload(): array
    {
        $order = $this->order;
        $user = $order->user;
        $credit = \App\Support\FinanceApprovalCredit::forOrder($order);

        return [
            // Required by EDMS
            'transaction_id' => $order->id,
            'transaction_number' => $order->order_number,
            'customer_name' => $user?->name ?? 'Unknown',
            'amount' => (float) $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $order->formatted_payment_method,
            // Kredit pembeli untuk pertimbangan approval finance
            'ar_outstanding' => $credit['ar_outstanding'],
            'credit_limit' => $credit['credit_limit'],
            'existing_top_transactions' => $credit['existing_top_transactions'],
            'accumulated_debt' => $credit['accumulated_debt'],
            'remaining_credit_limit' => $credit['remaining_credit_limit'],
            // Pending invoice: order tempo (TOP) pelanggan yang belum lunas
            'pending_invoice_count' => $credit['pending_invoice_count'],
            'pending_invoice_total' => $credit['pending_invoice_total'],
            'pending_invoice_overdue_count' => $credit['pending_invoice_overdue_count'],
            'pending_invoice_overdue_total' => $credit['pending_invoice_overdue_total'],
            'pending_invoices' => $credit['pending_invoices'],
            'term_of_payment_days' => $credit['term_of_payment_days'],
            'ar_outstanding_source' => $credit['ar_outstanding_source'],
            'ar_outstanding_updated_at' => $credit['ar_outstanding_updated_at'],
            'pending_invoice_due_date_basis' => $credit['pending_invoice_due_date_basis'],
            'credit_notes' => $credit['credit_notes'],

            // Original payload structure
            'event' => 'finance_approval_needed',
            'timestamp' => now()->toIso8601String(),
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'finance_approved' => (int) $order->finance_approved,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_amount' => (float) $order->total_amount,
                'shipping_address' => $order->shipping_address,
                'notes' => $order->notes,
                'source_warehouse_id' => $order->source_warehouse_id,
                'source_warehouse_name' => $order->sourceWarehouse?->name,
                'created_at' => optional($order->created_at)->toIso8601String(),
            ],
            'customer' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
                'phone' => $user?->phone,
                'role' => $user?->role,
                'term_of_payment' => $user?->term_of_payment,
                'credit_limit' => $credit['credit_limit'],
                'ar_outstanding' => $credit['ar_outstanding'],
                'existing_top_transactions' => $credit['existing_top_transactions'],
                'accumulated_debt' => $credit['accumulated_debt'],
                'remaining_credit_limit' => $credit['remaining_credit_limit'],
                'pending_invoice_count' => $credit['pending_invoice_count'],
                'pending_invoice_total' => $credit['pending_invoice_total'],
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->display_name ?? $item->product?->name,
                    'product_code' => $item->product?->code,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->values()->all(),
            'actions' => [
                'approve_api' => url('/api/orders/finance-approval'),
                'admin_url' => url('/admin/orders/' . $order->id),
            ],
        ];
    }
}
