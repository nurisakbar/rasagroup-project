<?php

namespace App\Jobs;

use App\Models\Order;
use App\Support\QadIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RetryFailedQadSalesOrdersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 120;

    public int $tries = 1;

    public int $uniqueFor = 280;

    public function uniqueId(): string
    {
        return 'retry-failed-qad-sales-orders';
    }

    public function handle(): void
    {
        if (! QadIntegration::isConfigured()) {
            Log::info('RetryFailedQadSalesOrdersJob: skipped (QID API belum dikonfigurasi)');

            return;
        }

        $orders = Order::query()
            ->needingQadSalesOrderRetry()
            ->orderBy('created_at')
            ->limit(30)
            ->get();

        if ($orders->isEmpty()) {
            Log::info('RetryFailedQadSalesOrdersJob: no failed QAD sales orders');

            return;
        }

        Log::info('RetryFailedQadSalesOrdersJob: dispatching retries', [
            'count' => $orders->count(),
            'order_numbers' => $orders->pluck('order_number')->all(),
        ]);

        foreach ($orders as $order) {
            SyncOrderToQad::dispatch($order);
        }
    }
}
