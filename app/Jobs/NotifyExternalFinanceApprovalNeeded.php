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

    public function __construct(public Order $order)
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
            return;
        }

        $this->order->loadMissing(['user', 'sourceWarehouse', 'items.product']);

        if (!$this->order->isAwaitingFinanceApproval()) {
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

        $response = Http::timeout((int) config('services.finance_approval.timeout', 15))
            ->withHeaders($headers)
            ->post($url, $payload);

        if ($response->failed()) {
            Log::error('Finance approval webhook gagal', [
                'order_id' => $this->order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();
        }

        Log::info('Finance approval webhook sukses', [
            'order_id' => $this->order->id,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);
    }

    protected function buildPayload(): array
    {
        $order = $this->order;
        $user = $order->user;

        return [
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
                'credit_limit' => $user?->credit_limit !== null ? (float) $user->credit_limit : null,
                'ar_outstanding' => $user?->ar_outstanding !== null ? (float) $user->ar_outstanding : null,
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
