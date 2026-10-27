<?php

namespace App\Support;

use App\Jobs\SyncOrderToJubelio;
use App\Jobs\SyncOrderToQad;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SalesOrderSyncDispatcher
{
    public const TARGET_JUBELIO = 'jubelio';

    public const TARGET_QAD = 'qad';

    public static function dispatch(Order $order): void
    {
        $order->refresh();

        $dispatchedAny = false;

        // 1. Sinkronisasi QAD jika memenuhi syarat
        if ($order->shouldSyncToQad() && self::isQadEnabled()) {
            if (! empty($order->qad_so_number)) {
                Log::info('SalesOrderSyncDispatcher: QAD SO already exists, skip', [
                    'order_id' => $order->id,
                    'order_type' => $order->order_type,
                    'qad_so_number' => $order->qad_so_number,
                ]);
            } else {
                SyncOrderToQad::dispatch($order);
                Log::info('SalesOrderSyncDispatcher: SyncOrderToQad dispatched', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                ]);
            }
            $dispatchedAny = true;
        }

        // 2. Sinkronisasi Jubelio jika memenuhi syarat
        if ($order->shouldSyncToJubelio() && self::isJubelioEnabled()) {
            if (! empty($order->jubelio_salesorder_id)) {
                Log::info('SalesOrderSyncDispatcher: Jubelio SO already exists, skip', [
                    'order_id' => $order->id,
                    'order_type' => $order->order_type,
                    'jubelio_salesorder_id' => $order->jubelio_salesorder_id,
                ]);
            } else {
                SyncOrderToJubelio::dispatch($order);
                Log::info('SalesOrderSyncDispatcher: SyncOrderToJubelio dispatched', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                ]);
            }
            $dispatchedAny = true;
        }

        if (! $dispatchedAny) {
            Log::info('SalesOrderSyncDispatcher: no sales order target for order', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'jubelio_enabled' => self::isJubelioEnabled(),
                'qad_enabled' => self::isQadEnabled(),
            ]);
        }
    }

    public static function resolveTarget(Order $order): ?string
    {
        if ($order->shouldSyncToQad() && self::isQadEnabled()) {
            return self::TARGET_QAD;
        }

        if ($order->shouldSyncToJubelio() && self::isJubelioEnabled()) {
            return self::TARGET_JUBELIO;
        }

        return null;
    }

    public static function isJubelioEnabled(): bool
    {
        return (bool) config('jubelio.sales_order.enabled', false);
    }

    public static function isQadEnabled(): bool
    {
        return QadIntegration::isConfigured();
    }
}
