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
                'auth_user' => $request->getUser()
            ]);

            // Basic Auth Validation for Legacy Faspay Webhook
            $authUser = $request->getUser();
            $authPass = $request->getPassword();
            
            $expectedUser = config('services.faspay.user_id');
            $expectedPass = config('services.faspay.password');

            if ($authUser !== $expectedUser || $authPass !== $expectedPass) {
                Log::warning('Faspay Webhook Basic Auth Failed', [
                    'user' => $authUser
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $billNo = $data['bill_no'] ?? null;
            $paymentStatusCode = $data['payment_status_code'] ?? null;
            $signature = $data['signature'] ?? null;
            $trxId = $data['trx_id'] ?? '';
            $merchantId = config('services.faspay.merchant_id');

            if (!$billNo || !$paymentStatusCode) {
                return $this->jsonResponse($trxId, $merchantId, $billNo, '01', 'Failed');
            }

            // Verify signature (optional but recommended)
            $faspayService = new FaspayService();
            $expectedSignature = $faspayService->generateCallbackSignature($billNo, $paymentStatusCode);
            
            if ($signature && strtolower($signature) !== strtolower($expectedSignature)) {
                Log::warning('Faspay webhook signature verification failed', [
                    'expected' => $expectedSignature,
                    'received' => $signature
                ]);
            }

            // Find order by faspay_bill_no, order_number, or virtual_account_no
            $order = Order::where('faspay_bill_no', $billNo)
                ->orWhere('order_number', $billNo)
                ->orWhere('virtual_account_no', $billNo)
                ->first();

            if (!$order) {
                Log::warning('Faspay webhook: Order not found', [
                    'bill_no' => $billNo,
                ]);
                return $this->jsonResponse($trxId, $merchantId, $billNo, '01', 'Failed');
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
                
                return $this->jsonResponse($trxId, $merchantId, $billNo, '00', 'Success');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Faspay webhook processing error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return $this->jsonResponse($trxId, $merchantId, $billNo, '01', 'Failed');
            }
        } catch (\Exception $e) {
            Log::error('Faspay webhook exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function jsonResponse($trxId, $merchantId, $billNo, $code, $desc)
    {
        return response()->json([
            'response' => 'Payment Notification',
            'trx_id' => $trxId,
            'merchant_id' => $merchantId,
            'bill_no' => $billNo,
            'response_code' => $code,
            'response_desc' => $desc
        ], 200);
    }

    /**
     * Handle Return URL / Landing Page setelah pembayaran E-Wallet atau Debit di Faspay.
     */
    public function returnUrl(Request $request)
    {
        Log::info('Faspay Return URL / Landing Page Accessed', [
            'params' => $request->all(),
            'url' => $request->fullUrl(),
        ]);

        $billNo = $request->input('bill_no') 
               ?: $request->input('order_number') 
               ?: $request->input('order_id') 
               ?: $request->input('id_order') 
               ?: $request->input('bill_reff');

        $order = null;
        if ($billNo) {
            $order = Order::where('faspay_bill_no', $billNo)
                ->orWhere('order_number', $billNo)
                ->orWhere('virtual_account_no', $billNo)
                ->orWhere('id', $billNo)
                ->first();
        }

        if ($order) {
            if (auth()->check()) {
                return redirect()->route('checkout.success', $order)
                    ->with('success', 'Pembayaran pesanan #' . $order->order_number . ' sedang diproses/telah berhasil.');
            }

            return redirect()->route('login')
                ->with('status', 'Pembayaran pesanan #' . $order->order_number . ' berhasil diterima. Silakan login untuk melihat status pesanan.');
        }

        if (auth()->check()) {
            return redirect()->route('buyer.orders.index')
                ->with('success', 'Pembayaran Anda sedang diproses oleh sistem. Silakan cek status pesanan di halaman ini.');
        }

        return redirect()->route('home');
    }
}
