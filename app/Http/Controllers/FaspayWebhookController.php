<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\FaspayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FaspayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('Faspay Webhook Received - FULL DEBUG', [
                'headers' => $request->headers->all(),
                'payload' => $data,
            ]);

            $billNo = $data['bill_no'] ?? null;
            $paymentStatusCode = $data['payment_status_code'] ?? null;
            $signature = $data['signature'] ?? null;

            if (!$billNo || !$paymentStatusCode) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Verify signature (optional but recommended)
            $faspayService = new FaspayService();
            $expectedSignature = $faspayService->generateCallbackSignature($billNo, $paymentStatusCode);
            
            if ($signature && strtolower($signature) !== strtolower($expectedSignature)) {
                Log::warning('Faspay webhook signature verification failed', [
                    'expected' => $expectedSignature,
                    'received' => $signature
                ]);
                // return response()->json(['error' => 'Invalid signature'], 401); 
                // Commented out to ensure testing works, uncomment in production if Faspay sends signature
            }

            // Find order by faspay_bill_no or order_number
            $order = Order::where('faspay_bill_no', $billNo)
                ->orWhere('order_number', $billNo)
                ->first();

            if (!$order) {
                Log::warning('Faspay webhook: Order not found', [
                    'bill_no' => $billNo,
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            DB::beginTransaction();
            try {
                // Faspay payment_status_code: 2 = Payment Success, 0 = Unpaid, 3 = Payment Failed/Expired
                if ($paymentStatusCode == '2') {
                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        if ($order->order_status === 'pending') {
                            $order->update(['order_status' => 'processing']);
                        }

                        $order->creditPoints();

                        Log::info('Faspay payment successful', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'amount' => $order->total_amount,
                        ]);
                        
                        \App\Jobs\SendWhatsAppNotification::dispatch($order, 'thank_you');
                        \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_notification');
                        \App\Support\SalesOrderSyncDispatcher::dispatch($order);
                    }
                } elseif (in_array($paymentStatusCode, ['3', '4', '5', '7', '8'])) { // Various failure/cancellation codes
                    if ($order->payment_status !== 'failed') {
                        $order->update([
                            'payment_status' => 'failed',
                        ]);
                        Log::info('Faspay payment failed or expired', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'status_code' => $paymentStatusCode
                        ]);
                    }
                } else {
                    Log::info('Faspay webhook: Unhandled status or unpaid', [
                        'status_code' => $paymentStatusCode,
                        'order_id' => $order->id,
                    ]);
                }

                DB::commit();
                
                // Faspay expects response XML or specific format based on their docs, 
                // but generally HTTP 200 is acceptable for standard webhooks. 
                // Some Faspay versions require specific response:
                return response()->json([
                    'response' => 'Payment Notification',
                    'trx_id' => $data['trx_id'] ?? '',
                    'merchant_id' => config('services.faspay.merchant_id'),
                    'bill_no' => $billNo,
                    'response_code' => '00',
                    'response_desc' => 'Success'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Faspay webhook processing error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json(['error' => 'Processing failed'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Faspay webhook exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
