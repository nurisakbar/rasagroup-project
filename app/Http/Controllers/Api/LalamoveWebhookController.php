<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LalamoveWebhookController extends Controller
{
    /**
     * Lalamove dashboard URL check (GET).
     */
    public function verify(): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    /**
     * Receive Lalamove order events and update the matching Rasa order.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Lalamove webhook received', [
            'event' => $request->input('eventType'),
            'payload' => $payload,
        ]);

        if ($request->has('data.url')) {
            return response()->json(['success' => true]);
        }

        $lalamoveOrderId = $request->input('data.order.orderId')
            ?? $request->input('data.orderId')
            ?? $request->input('orderId');

        $status = $request->input('data.order.status')
            ?? $request->input('data.status')
            ?? $request->input('eventType');

        if (! is_string($lalamoveOrderId) || $lalamoveOrderId === '') {
            Log::warning('Lalamove webhook missing order id', ['payload' => $payload]);

            return response()->json(['success' => true]);
        }

        $order = Order::query()
            ->where('tracking_number', $lalamoveOrderId)
            ->orWhere('ekspedisiku_shipment_id', $lalamoveOrderId)
            ->first();

        if (! $order) {
            Log::warning('Lalamove webhook order not found', [
                'lalamove_order_id' => $lalamoveOrderId,
            ]);

            return response()->json(['success' => true]);
        }

        $this->applyStatus($order, (string) $status, $lalamoveOrderId);

        return response()->json(['success' => true]);
    }

    protected function applyStatus(Order $order, string $status, string $lalamoveOrderId): void
    {
        $normalized = strtoupper(str_replace([' ', '-'], '_', trim($status)));
        $oldStatus = $order->order_status;
        $updates = [
            'ekspedisiku_booking_status' => strtolower($normalized) ?: $order->ekspedisiku_booking_status,
            'ekspedisiku_booking_last_error' => null,
        ];

        if ($order->tracking_number !== $lalamoveOrderId) {
            $updates['tracking_number'] = $lalamoveOrderId;
        }
        if ($order->ekspedisiku_shipment_id !== $lalamoveOrderId) {
            $updates['ekspedisiku_shipment_id'] = $lalamoveOrderId;
        }

        if (in_array($normalized, ['ASSIGNING_DRIVER', 'ON_GOING', 'PICKED_UP', 'DRIVER_ASSIGNED'], true)) {
            if (! in_array($oldStatus, ['shipped', 'delivered', 'completed', 'cancelled'], true)) {
                $updates['order_status'] = 'shipped';
            }
            if (! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
        }

        if (in_array($normalized, ['COMPLETED', 'DELIVERED'], true)) {
            if (! in_array($oldStatus, ['delivered', 'completed', 'cancelled'], true)) {
                $updates['order_status'] = 'delivered';
            }
            if (! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
            if (! $order->received_at) {
                $updates['received_at'] = now();
            }
        }

        if (in_array($normalized, ['CANCELED', 'CANCELLED', 'REJECTED', 'EXPIRED'], true)) {
            $updates['ekspedisiku_booking_status'] = 'failed';
            $updates['ekspedisiku_booking_last_error'] = 'Lalamove '.$normalized;
        }

        $order->update($updates);

        if (
            $order->user
            && $oldStatus !== 'shipped'
            && $order->order_status === 'shipped'
        ) {
            $order->user->notify(new OrderShippedNotification($order));
        }

        Log::info('Lalamove webhook processed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'lalamove_order_id' => $lalamoveOrderId,
            'lalamove_status' => $normalized,
            'order_status' => $order->order_status,
        ]);
    }
}
