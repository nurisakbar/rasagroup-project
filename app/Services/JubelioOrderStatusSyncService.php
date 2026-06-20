<?php

namespace App\Services;

use App\Models\Order;
use App\Support\JubelioOrderStatusMapper;
use Illuminate\Support\Facades\Log;

class JubelioOrderStatusSyncService
{
    public function __construct(
        private JubelioService $jubelio,
        private JubelioOrderStatusMapper $mapper
    ) {}

    /**
     * @return array{
     *     checked: int,
     *     updated: int,
     *     unchanged: int,
     *     failed: int,
     *     skipped: int
     * }
     */
    public function sync(?int $limit = null, ?string $orderId = null): array
    {
        if (! config('jubelio.status_poll.enabled', true)) {
            return [
                'checked' => 0,
                'updated' => 0,
                'unchanged' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];
        }

        $stats = [
            'checked' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        $query = Order::query()
            ->whereNotNull('jubelio_salesorder_id')
            ->where('order_type', Order::TYPE_REGULAR)
            ->where('payment_status', 'paid')
            ->whereNotIn('order_status', ['completed', 'cancelled'])
            ->orderBy('updated_at');

        if ($orderId) {
            $query->where('id', $orderId);
        } elseif ($limit !== null) {
            $query->limit(max(1, $limit));
        } else {
            $query->limit(max(1, (int) config('jubelio.status_poll.batch_size', 50)));
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            Log::channel('jubelio_sales_order')->debug('JubelioOrderStatusSync: no orders to poll');

            return $stats;
        }

        $token = $this->jubelio->token();

        foreach ($orders as $order) {
            $stats['checked']++;

            try {
                $result = $this->syncOrder($order, $token);
                if ($result === 'updated') {
                    $stats['updated']++;
                } elseif ($result === 'unchanged') {
                    $stats['unchanged']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                Log::channel('jubelio_sales_order')->error('JubelioOrderStatusSync: order failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'jubelio_salesorder_id' => $order->jubelio_salesorder_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('jubelio_sales_order')->info('JubelioOrderStatusSync: finished', $stats);

        return $stats;
    }

    /**
     * @return 'updated'|'unchanged'|'skipped'
     */
    public function syncOrder(Order $order, ?string $token = null): string
    {
        $salesOrderId = (int) $order->jubelio_salesorder_id;
        if ($salesOrderId <= 0) {
            return 'skipped';
        }

        $token = $token ?: $this->jubelio->token();
        $jubelioOrder = $this->jubelio->getSalesOrder($token, $salesOrderId);

        if (! is_array($jubelioOrder)) {
            throw new \RuntimeException("Gagal mengambil SO Jubelio #{$salesOrderId}");
        }

        $mapped = $this->mapper->map($jubelioOrder);
        $updates = [];
        $changed = false;

        if ($mapped['tracking_number'] && $mapped['tracking_number'] !== $order->tracking_number) {
            $updates['tracking_number'] = $mapped['tracking_number'];
            $changed = true;
        }

        if ($this->mapper->shouldApplyStatus($order->order_status, $mapped)) {
            $updates['order_status'] = $mapped['order_status'];
            $changed = true;

            if ($mapped['shipped_at'] && ! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
        } elseif (
            $mapped['tracking_number']
            && in_array($order->order_status, ['pending', 'processing'], true)
            && ! isset($updates['order_status'])
        ) {
            $updates['order_status'] = 'shipped';
            $updates['shipped_at'] = $order->shipped_at ?: now();
            $changed = true;
        }

        if (! $changed) {
            Log::channel('jubelio_sales_order')->debug('JubelioOrderStatusSync: unchanged', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'jubelio_salesorder_id' => $salesOrderId,
                'local_status' => $order->order_status,
                'jubelio_internal_status' => $mapped['jubelio_internal_status'],
                'jubelio_channel_status' => $mapped['jubelio_channel_status'],
            ]);

            return 'unchanged';
        }

        $previousStatus = $order->order_status;
        $order->update($updates);
        $order->refresh();

        if (($updates['order_status'] ?? null) === 'completed') {
            $order->creditPoints();
        }

        Log::channel('jubelio_sales_order')->info('JubelioOrderStatusSync: updated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'jubelio_salesorder_id' => $salesOrderId,
            'jubelio_salesorder_no' => $jubelioOrder['salesorder_no'] ?? $order->jubelio_salesorder_no,
            'previous_status' => $previousStatus,
            'new_status' => $order->order_status,
            'tracking_number' => $order->tracking_number,
            'jubelio_internal_status' => $mapped['jubelio_internal_status'],
            'jubelio_channel_status' => $mapped['jubelio_channel_status'],
            'updates' => $updates,
        ]);

        return 'updated';
    }
}
