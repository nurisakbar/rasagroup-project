<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RetryFailedWmsSalesOrdersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 120;

    public int $tries = 1;

    public int $uniqueFor = 280;

    public function uniqueId(): string
    {
        return 'retry-failed-wms-sales-orders';
    }

    public function handle(): void
    {
        if (empty(config('services.wms.api_url')) || empty(config('services.wms.api_key'))) {
            Log::info('RetryFailedWmsSalesOrdersJob: skipped (WMS API belum dikonfigurasi)');

            return;
        }

        $orders = Order::query()
            ->needingWmsSalesOrderRetry()
            ->orderBy('created_at')
            ->limit(30)
            ->get();

        if ($orders->isNotEmpty()) {
            Log::info('RetryFailedWmsSalesOrdersJob: dispatching retries', [
                'count' => $orders->count(),
                'order_numbers' => $orders->pluck('order_number')->all(),
            ]);

            foreach ($orders as $order) {
                SendSalesOrderToWmsJob::dispatch($order);
            }
        }

        // Cek pesanan yang masih berstatus QUEUED / UPDATE_QUEUED setelah > 2 menit
        $queuedOrders = Order::query()
            ->where('finance_approved', true)
            ->where('order_status', '!=', 'cancelled')
            ->whereIn('wms_so_status', ['QUEUED', 'UPDATE_QUEUED'])
            ->where('wms_so_synced_at', '<=', now()->subMinutes(2))
            ->orderBy('wms_so_synced_at')
            ->limit(20)
            ->get();

        if ($queuedOrders->isNotEmpty()) {
            Log::info('RetryFailedWmsSalesOrdersJob: dispatching status checks for queued orders', [
                'count' => $queuedOrders->count(),
                'order_numbers' => $queuedOrders->pluck('order_number')->all(),
            ]);

            foreach ($queuedOrders as $queuedOrder) {
                CheckWmsOrderStatusJob::dispatch($queuedOrder);
            }
        }
    }
}
