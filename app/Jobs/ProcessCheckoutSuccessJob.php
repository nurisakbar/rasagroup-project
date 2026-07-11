<?php

namespace App\Jobs;

use App\Models\Order;
use App\Support\QadIntegration;
use App\Support\SalesOrderSyncDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Pekerjaan pasca halaman checkout/success: cek Xendit, antre sync sales order (Jubelio), notifikasi.
 * Menghindari pemanggilan API berat secara sinkron di request HTTP.
 */
class ProcessCheckoutSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function __construct(
        public string $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::query()
            ->with([
                'user',
                'address.district',
                'address.regency',
                'address.province',
                'address.district.city',
                'items.product',
            ])
            ->find($this->orderId);

        if (! $order) {
            Log::warning('ProcessCheckoutSuccessJob: order not found', ['order_id' => $this->orderId]);

            return;
        }

        try {
            if (QadIntegration::isConfigured() && $order->shouldSyncToQad()) {
                $user = $order->user;
                $address = $order->address;
                if ($user && empty($user->qad_customer_code) && $address) {
                    $snapshot = \App\Support\QadAddressSnapshot::fromBuyerAddress($address);
                    SyncCustomerToQad::dispatch($user, $snapshot);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessCheckoutSuccessJob: failed to dispatch SyncCustomerToQad', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Payment polling is handled entirely by Webhooks for Faspay

        $order->refresh();

        if ($order->payment_status === 'paid') {
            SalesOrderSyncDispatcher::dispatch($order);
        }

        $order->refresh();

        if ($order->payment_status === 'paid') {
            SendWhatsAppNotification::dispatch($order, 'thank_you');
        }
    }
}
