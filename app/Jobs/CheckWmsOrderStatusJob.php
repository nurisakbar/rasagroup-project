<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckWmsOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    public function __construct(public Order $order)
    {
    }

    public function handle(WmsService $wms): void
    {
        $order = $this->order->fresh();
        if (! $order || empty($order->order_number)) {
            return;
        }

        $this->order = $order;

        if ($order->order_status === 'cancelled') {
            Log::info('CheckWmsOrderStatusJob: Order cancelled, skipping check', [
                'order_number' => $order->order_number,
            ]);

            return;
        }

        $response = $wms->checkSalesOrderStatus($order->order_number);
        if (! $response || ! isset($response['data'])) {
            Log::warning('CheckWmsOrderStatusJob: No valid response from WMS', [
                'order_number' => $order->order_number,
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release(30);
            }

            return;
        }

        $data = $response['data'];
        $wmsStatus = strtoupper(trim((string) ($data['status'] ?? '')));
        $failureReason = $data['failure_reason'] ?? null;

        if (! empty($wmsStatus)) {
            $order->wms_so_status = $wmsStatus;
            $order->wms_so_failure_reason = $failureReason;
            $order->wms_so_synced_at = now();
            $order->save();

            Log::info('CheckWmsOrderStatusJob: Updated order WMS status', [
                'order_number' => $order->order_number,
                'status' => $wmsStatus,
                'failure_reason' => $failureReason,
                'attempt' => $this->attempts(),
            ]);

            // Jika status masih dalam proses (QUEUED / UPDATE_QUEUED / PENDING), cek kembali 30 detik kemudian
            if (in_array($wmsStatus, ['QUEUED', 'UPDATE_QUEUED', 'PENDING'], true)) {
                if ($this->attempts() < $this->tries) {
                    $this->release(30);

                    return;
                }
            }

            // Jika pengiriman ke WMS sukses, trigger Jubelio jika belum terkirim
            if ($wmsStatus === 'SUCCESS' && $order->shouldSyncToJubelio() && \App\Support\SalesOrderSyncDispatcher::isJubelioEnabled() && empty($order->jubelio_salesorder_id)) {
                SyncOrderToJubelio::dispatch($order);
                Log::info('CheckWmsOrderStatusJob: Dispatched SyncOrderToJubelio after WMS status SUCCESS.', [
                    'order_number' => $order->order_number,
                ]);
            }
        }
    }
}
