<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('x-callback-token');

            // Verify webhook signature (optional but recommended)
            $xenditService = new XenditService();
            // Uncomment if you have webhook token configured
            // if (!$xenditService->verifyWebhookSignature($payload, $signature)) {
            //     Log::warning('Xendit webhook signature verification failed');
            //     return response()->json(['error' => 'Invalid signature'], 401);
            // }

            $data = $request->json()->all();

            Log::info('Xendit Webhook Received', [
                'event' => $data['status'] ?? 'unknown',
                'invoice_id' => $data['id'] ?? null,
                'external_id' => $data['external_id'] ?? null,
            ]);

            // Find order by invoice ID or external_id (order_number)
            $order = null;
            if (isset($data['id'])) {
                $order = Order::where('xendit_invoice_id', $data['id'])->first();
            }
            
            if (!$order && isset($data['external_id'])) {
                $order = Order::where('order_number', $data['external_id'])->first();
            }

            if (!$order) {
                Log::warning('Xendit webhook: Order not found', [
                    'invoice_id' => $data['id'] ?? null,
                    'external_id' => $data['external_id'] ?? null,
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Handle different invoice statuses
            $status = $data['status'] ?? null;

            DB::beginTransaction();
            try {
                switch ($status) {
                    case 'PAID':
                        $order->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        // Auto update order status to processing if still pending
                        if ($order->order_status === 'pending') {
                            $order->update(['order_status' => 'processing']);
                        }

                        // Credit points for DRiiPPreneur if applicable
                        if ($order->points_earned > 0 && !$order->points_credited) {
                            $user = $order->user;
                            if ($user && $user->isDriippreneurApproved()) {
                                $user->increment('points', $order->points_earned);
                                $order->update(['points_credited' => true]);
                            }
                        }

                        Log::info('Xendit payment successful', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'amount' => $order->total_amount,
                        ]);
                        break;

                    case 'EXPIRED':
                        $order->update([
                            'payment_status' => 'failed',
                        ]);
                        Log::info('Xendit invoice expired', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ]);
                        break;

                    case 'FAILED':
                        $order->update([
                            'payment_status' => 'failed',
                        ]);
                        Log::info('Xendit payment failed', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ]);
                        break;

                    default:
                        Log::info('Xendit webhook: Unhandled status', [
                            'status' => $status,
                            'order_id' => $order->id,
                        ]);
                }

                DB::commit();
                return response()->json(['message' => 'Webhook processed successfully'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Xendit webhook processing error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json(['error' => 'Processing failed'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Xendit webhook exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}



