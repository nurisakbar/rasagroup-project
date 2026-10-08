<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LionParcelWebhookController extends Controller
{
    /**
     * Receive Lion Parcel order events and update the matching Rasa order.
     */
    public function handle(Request $request): JsonResponse
    {
        $expectedSecret = env('LION_PARCEL_WEBHOOK_SECRET');
        $providedSecret = $request->header('x-token') 
                          ?? $request->bearerToken() 
                          ?? $request->input('token');

        if ($expectedSecret && $providedSecret !== $expectedSecret) {
            Log::warning('Lion Parcel Webhook Unauthorized Request', [
                'ip' => $request->ip(),
                'provided_token' => $providedSecret
            ]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        Log::info('Lion Parcel webhook received', [
            'payload' => $payload,
        ]);

        $sttNo = $request->input('stt_no') 
                 ?? $request->input('data.stt_no') 
                 ?? $request->input('stt_number');
                 
        $statusCode = $request->input('status_code') 
                      ?? $request->input('data.status_code');
                 
        $status = $request->input('status') 
                  ?? $request->input('data.status')
                  ?? $request->input('status_name');

        if (! is_string($sttNo) || $sttNo === '') {
            Log::warning('Lion Parcel webhook missing STT number', ['payload' => $payload]);
            return response()->json(['success' => true]);
        }

        $order = Order::query()
            ->where('tracking_number', $sttNo)
            ->first();

        if (! $order) {
            Log::warning('Lion Parcel webhook order not found', [
                'stt_no' => $sttNo,
            ]);

            return response()->json(['success' => true]);
        }

        $this->applyStatus($order, (string) $status, (string) $statusCode, $sttNo);

        return response()->json(['success' => true]);
    }

    protected function applyStatus(Order $order, string $status, string $statusCode, string $sttNo): void
    {
        $normalized = strtoupper(str_replace([' ', '-'], '_', trim($status)));
        $oldStatus = $order->order_status;
        $updates = [
            'ekspedisiku_booking_status' => strtolower($normalized) ?: $order->ekspedisiku_booking_status,
            'ekspedisiku_booking_last_error' => null,
        ];

        if ($order->tracking_number !== $sttNo) {
            $updates['tracking_number'] = $sttNo;
        }

        // Shipped mapping
        if (in_array($normalized, ['ON_PROCESS', 'RECEIVED_AT_ORIGIN', 'TRANSIT', 'OUT_FOR_DELIVERY', 'PICKED_UP'], true)) {
            if (! in_array($oldStatus, ['shipped', 'delivered', 'completed', 'cancelled'], true)) {
                $updates['order_status'] = 'shipped';
            }
            if (! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
        }

        // Delivered mapping
        if (in_array($normalized, ['DELIVERED', 'COMPLETED', 'RECEIVED_BY_CONSIGNEE'], true) || str_contains($normalized, 'DELIVERED')) {
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

        // Failed/Cancelled mapping
        if (in_array($normalized, ['CANCELED', 'CANCELLED', 'RETURNED', 'REJECTED'], true)) {
            $updates['ekspedisiku_booking_status'] = 'failed';
            $updates['ekspedisiku_booking_last_error'] = 'Lion Parcel '.$normalized;
        }

        $order->update($updates);

        if (
            $order->user
            && $oldStatus !== 'shipped'
            && $order->order_status === 'shipped'
        ) {
            $order->user->notify(new OrderShippedNotification($order));
        }

        Log::info('Lion Parcel webhook processed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'stt_no' => $sttNo,
            'lion_status' => $normalized,
            'order_status' => $order->order_status,
        ]);
    }
}
