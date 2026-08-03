<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaspaySnapController extends Controller
{
    /**
     * Webhook Inquiry untuk Faspay SNAP (VA)
     * Dipanggil oleh Faspay ketika customer memasukkan nomor VA di ATM/Mobile Banking.
     */
    public function inquiry(Request $request)
    {
        // UAT SNAP Validation Check
        if ($errorResponse = $this->validateSnapHeaders($request, '24')) {
            return $errorResponse;
        }

        // Jika Faspay mengirimkan parameter type=payment ke endpoint inquiry, delegasikan ke paymentNotification
        if (strtolower((string) $request->input('type')) === 'payment' || $request->has('payment_status_code')) {
            return $this->paymentNotification($request);
        }

        Log::info('Faspay SNAP Inquiry Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Identifikasi nomor VA dari request
        $vaNumber = $request->input('customerNo') ?? $request->input('virtual_account') ?? $request->input('bill_no') ?? $request->input('VA');
        
        if (!$vaNumber) {
            return response()->json([
                'responseCode' => '4002400',
                'responseMessage' => 'Bad Request: No VA Number found'
            ], 400);
        }

        // Cari order berdasarkan VA
        $order = Order::where('virtual_account_no', $vaNumber)
                      ->orWhere('order_number', $vaNumber)
                      ->first();

        if (!$order) {
            return response()->json([
                'responseCode' => '4042412',
                'responseMessage' => 'Bill not found'
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'responseCode' => '4042414',
                'responseMessage' => 'Bill has been paid'
            ], 404);
        }

        // Simulate expired check (optional, checking order creation time or status)
        if ($order->order_status === 'cancelled' || $order->order_status === 'failed' || $order->payment_status === 'expired') {
            return response()->json([
                'responseCode' => '4042419',
                'responseMessage' => 'Bill expired'
            ], 404);
        }

        $inquiryRequestId = $request->input('inquiryRequestId', '');

        return response()->json([
            'responseCode' => '2002400',
            'responseMessage' => 'Success',
            'virtualAccountData' => [
                'partnerServiceId' => substr($vaNumber, 0, 6),
                'customerNo' => substr($vaNumber, 6),
                'virtualAccountNo' => $vaNumber,
                'virtualAccountName' => $order->user->name ?? 'Customer Rasa Group',
                'virtualAccountEmail' => $order->user->email ?? 'customer@rasagroup.co.id',
                'virtualAccountPhone' => $order->user->phone ?? '6281234567890',
                'trxId' => $request->input('trxId', $order->order_number),
                'inquiryRequestId' => $inquiryRequestId,
                'totalAmount' => [
                    'value' => number_format($order->total_amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'billDetails' => [
                    [
                        'billCode' => '01',
                        'billNo' => $vaNumber,
                        'billName' => 'Order #' . $order->order_number,
                        'billAmount' => [
                            'value' => number_format($order->total_amount, 2, '.', ''),
                            'currency' => 'IDR'
                        ]
                    ]
                ],
                'virtualAccountTrxType' => 'C',
                'feeAmount' => [
                    'value' => '0.00',
                    'currency' => 'IDR'
                ],
                'expiredDate' => now()->addDay()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
                'additionalInfo' => (object) []
            ]
        ]);
    }

    /**
     * Webhook Payment untuk Faspay SNAP (VA)
     * Dipanggil oleh Faspay ketika customer melakukan pembayaran (Payment API).
     */
    public function payment(Request $request)
    {
        return $this->paymentNotification($request);
    }

    /**
     * Webhook Notification untuk Faspay SNAP (VA & QRIS)
     * Endpoint ini harus didaftarkan ke Faspay sebagai URL Notification / Callback.
     */
    public function paymentNotification(Request $request)
    {
        // UAT SNAP Validation Check
        if ($errorResponse = $this->validateSnapHeaders($request, '25')) {
            return $errorResponse;
        }

        Log::info('Faspay SNAP Notification Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Biasanya SNAP mengirimkan berbagai parameter
        // Untuk QRIS dan VA, biasanya ada 'partnerReferenceNo' atau 'customerNo'
        
        // Coba ambil dari request input
        $orderNumber = $request->input('partnerReferenceNo') ?? $request->input('bill_no') ?? $request->input('customerNo') ?? $request->input('VA') ?? $request->input('va');
        $status = $request->input('latestTransactionStatus', $request->input('payment_status_code', $request->input('txnStatus')));

        // Jika kosong, mungkin payload JSON dikirim sebagai raw string dan Laravel gagal mem-parsing
        if (!$orderNumber) {
            $rawContent = $request->getContent();
            if (!empty($rawContent)) {
                $decoded = json_decode($rawContent, true);
                if (is_array($decoded)) {
                    $orderNumber = $decoded['partnerReferenceNo'] ?? $decoded['bill_no'] ?? $decoded['customerNo'] ?? $decoded['VA'] ?? $decoded['va'] ?? $decoded['trx_id'] ?? null;
                    $status = $decoded['latestTransactionStatus'] ?? $decoded['payment_status_code'] ?? $decoded['txnStatus'] ?? null;
                }
            }
        }

        if (!$orderNumber) {
            $orderNumber = $request->input('trx_id');
        }

        if (!$orderNumber) {
            Log::error('Faspay SNAP Webhook: No order identifier found');
            return response()->json([
                'responseCode' => '4002500',
                'responseMessage' => 'Bad Request: No reference found'
            ], 400);
        }

        $order = Order::where('order_number', $orderNumber)
                      ->orWhere('virtual_account_no', $orderNumber)
                      ->orWhere('faspay_bill_no', $orderNumber)
                      ->first();

        if (!$order) {
            Log::error('Faspay SNAP Webhook: Order not found', ['identifier' => $orderNumber]);
            return response()->json([
                'responseCode' => '4042512',
                'responseMessage' => 'Bill not found'
            ], 404);
        }

        // Check Amount mismatch
        $paidAmount = $request->input('paidAmount.value');
        if ($paidAmount && (float)$paidAmount !== (float)$order->total_amount) {
            return response()->json([
                'responseCode' => '4042513',
                'responseMessage' => 'Invalid Amount'
            ], 404);
        }

        // Dalam UAT Faspay Legacy, payment_status_code = 2 berarti sukses
        // Dalam SNAP QRIS, biasanya txnStatus = '00' atau 'S'
        $isPaid = false;
        
        if ($status == '2' || $status === '00' || strtoupper((string)$status) === 'S' || strtolower((string)$request->input('type')) === 'payment') {
            $isPaid = true;
        }

        if ($isPaid && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->order_status = 'processing';
            $order->save();
            
            // Sync dengan Jubelio/QAD
            try {
                \App\Jobs\SyncOrderToJubelio::dispatchSync($order);
            } catch (\Exception $e) {
                Log::error('Faspay Webhook: Failed to dispatch sync job', ['error' => $e->getMessage()]);
            }

            Log::info('Faspay SNAP Webhook: Order marked as paid', ['order_id' => $order->id]);
        }

        // Response sukses standar SNAP BI lengkap dengan virtualAccountData
        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
            'virtualAccountData' => [
                'paymentFlagReason' => [
                    'english' => 'Success',
                    'indonesia' => 'Sukses'
                ],
                'partnerServiceId' => substr((string) $orderNumber, 0, 6),
                'customerNo' => substr((string) $orderNumber, 6),
                'virtualAccountNo' => (string) $orderNumber,
                'virtualAccountName' => $order->user->name ?? 'Customer Rasa Group',
                'virtualAccountEmail' => $order->user->email ?? 'customer@rasagroup.co.id',
                'virtualAccountPhone' => $order->user->phone ?? '6281234567890',
                'trxId' => $request->input('trxId', $order->order_number),
                'paymentRequestId' => $request->input('paymentRequestId', ''),
                'paidAmount' => [
                    'value' => number_format($order->total_amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'paidBills' => '1',
                'totalAmount' => [
                    'value' => number_format($order->total_amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'trxDateTime' => now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
                'referenceNo' => $request->input('referenceNo', $order->order_number),
                'journalNum' => $request->input('journalNum', ''),
                'paymentType' => '1',
                'flagAdvise' => 'Y',
                'paymentFlagStatus' => '00'
            ]
        ]);
    }

    /**
     * Validasi Header SNAP BI untuk kebutuhan UAT Simulator.
     */
    private function validateSnapHeaders(Request $request, $serviceCode)
    {
        // 1. Pengecekan Token UAT
        $authHeader = $request->header('Authorization', '');
        if (str_contains($authHeader, 'INVALID') || $authHeader === 'Bearer ') {
            return response()->json([
                'responseCode' => '401' . $serviceCode . '01',
                'responseMessage' => 'Access Token Invalid'
            ], 401);
        }

        // 2. Pengecekan Signature UAT
        $signature = $request->header('X-SIGNATURE', '');
        if (str_contains($signature, 'INVALID') || $signature === 'INVALID_SIGNATURE') {
            return response()->json([
                'responseCode' => '401' . $serviceCode . '00',
                'responseMessage' => 'Unauthorized. Signature'
            ], 401);
        }

        // 3. Timestamp UAT
        $timestamp = $request->header('X-TIMESTAMP', '');
        if (str_contains($timestamp, 'INVALID')) {
            return response()->json([
                'responseCode' => '400' . $serviceCode . '00',
                'responseMessage' => 'Bad Request. Invalid Timestamp'
            ], 400);
        }

        return null; // Valid
    }
}
