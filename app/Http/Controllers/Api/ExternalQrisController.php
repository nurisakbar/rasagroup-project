<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateExternalQrisRequest;
use App\Models\ExternalQrisTransaction;
use App\Services\ExternalQrisSnapService;
use App\Services\ExternalQrisWebhookDispatcher;
use App\Services\FaspaySnapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ExternalQrisController extends Controller
{
    /**
     * Dedicated endpoint to generate QRIS for PT Rasa Distribusi Indonesia (RDI).
     */
    public function generateRdi(GenerateExternalQrisRequest $request): Response
    {
        $request->merge(['company' => 'rdi']);
        return $this->generate($request);
    }

    /**
     * Generate QRIS Image / Data for external applications.
     */
    public function generate(GenerateExternalQrisRequest $request): Response
    {
        $validated = $request->validated();
        
        $referenceNo = $validated['reference_no'];
        $amount = (float) $validated['amount'];
        $company = strtolower($validated['company'] ?? 'rdi');
        $channelCode = (string) ($validated['channel_code'] ?? '836'); // 836 = Paydia QRIS (direct PNG), 715 = Nobu QRIS
        $validityMinutes = (int) ($validated['validity_minutes'] ?? 60);
        $format = strtolower($validated['format'] ?? 'json');
        $callbackUrl = $validated['callback_url'] ?? null;
        $callbackToken = $validated['callback_token'] ?? null;

        try {
            $snapService = new FaspaySnapService($company);
            
            $options = [
                'channel_code' => $channelCode,
                'validity_minutes' => $validityMinutes,
                'customer_name' => $validated['customer_name'] ?? 'Customer',
                'customer_phone' => $validated['customer_phone'] ?? '081234567890',
                'description' => $validated['description'] ?? ('Payment #' . $referenceNo),
            ];

            $result = $snapService->generateStandaloneQris($referenceNo, $amount, $options);

            if (!$result || ($result['responseCode'] ?? '') !== '2004700') {
                $errorMessage = $result['responseMessage'] ?? 'Failed to generate QRIS with payment gateway.';
                Log::error('External QRIS generation failed', [
                    'reference_no' => $referenceNo,
                    'result' => $result
                ]);

                return response()->json([
                    'status' => 'error',
                    'responseCode' => $result['responseCode'] ?? '5004700',
                    'responseMessage' => $errorMessage,
                    'details' => $result
                ], Response::HTTP_BAD_GATEWAY);
            }

            // Extract image URL: prioritize direct PNG qrUrl, then qrImageUrl
            $qrImageUrl = $result['qrUrl'] ?? ($result['additionalInfo']['qrImageUrl'] ?? null);
            $faspayRefNo = $result['referenceNo'] ?? null;
            $partnerRefNo = $result['partnerReferenceNo'] ?? $referenceNo;
            $expiresAt = now()->addMinutes($validityMinutes)->timezone('Asia/Jakarta');

            // Record transaction to database for webhook relay and status tracking
            $transaction = ExternalQrisTransaction::updateOrCreate(
                ['reference_no' => $partnerRefNo],
                [
                    'faspay_reference_no' => $faspayRefNo,
                    'company' => $company,
                    'channel_code' => $channelCode,
                    'amount' => $amount,
                    'status' => 'pending',
                    'qr_image_url' => $qrImageUrl,
                    'qr_url' => $result['qrUrl'] ?? null,
                    'callback_url' => $callbackUrl,
                    'callback_token' => $callbackToken,
                    'callback_status' => !empty($callbackUrl) ? 'pending' : 'none',
                    'customer_name' => $validated['customer_name'] ?? 'Customer',
                    'customer_phone' => $validated['customer_phone'] ?? '081234567890',
                    'description' => $validated['description'] ?? ('Payment #' . $referenceNo),
                    'expires_at' => $expiresAt,
                    'raw_generate_response' => $result,
                ]
            );

            // Format: redirect directly to QR image
            if ($format === 'redirect' && !empty($qrImageUrl)) {
                return redirect()->away($qrImageUrl);
            }

            // Format: stream binary image directly
            if ($format === 'image' && !empty($qrImageUrl)) {
                $imageContent = Http::withoutVerifying()->get($qrImageUrl);
                if ($imageContent->successful()) {
                    return response($imageContent->body(), Response::HTTP_OK)
                        ->header('Content-Type', $imageContent->header('Content-Type') ?: 'image/png')
                        ->header('X-Reference-No', (string) $faspayRefNo)
                        ->header('X-Partner-Reference-No', (string) $partnerRefNo);
                }
            }

            // Default Format: JSON
            return response()->json([
                'status' => 'success',
                'responseCode' => '2004700',
                'responseMessage' => 'QRIS generated successfully',
                'data' => [
                    'id' => $transaction->id,
                    'partner_reference_no' => $partnerRefNo,
                    'reference_no' => $faspayRefNo,
                    'amount' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR',
                    'qr_image_url' => $qrImageUrl,
                    'qr_url' => $result['qrUrl'] ?? null,
                    'channel_code' => $channelCode,
                    'company' => $company,
                    'expires_at' => $expiresAt->toIso8601String(),
                    'callback_url' => $callbackUrl,
                    'callback_status' => $transaction->callback_status,
                    'raw' => $result
                ]
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Exception in ExternalQrisController@generate', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'responseCode' => '5000000',
                'responseMessage' => 'Internal server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check QRIS payment status for external applications.
     */
    public function status(Request $request, string $referenceNo, ExternalQrisWebhookDispatcher $dispatcher): JsonResponse
    {
        $transaction = ExternalQrisTransaction::where('reference_no', $referenceNo)
            ->orWhere('faspay_reference_no', $referenceNo)
            ->first();

        $company = strtolower($request->query('company', $transaction->company ?? 'rdi'));
        $channelCode = (string) $request->query('channel_code', $transaction->channel_code ?? '836');
        $faspayRefNo = $request->query('reference_no', $transaction->faspay_reference_no ?? null);

        // If already verified as paid in DB, return current status
        if ($transaction && $transaction->isPaid()) {
            return response()->json([
                'status' => 'success',
                'responseCode' => '2005100',
                'responseMessage' => 'Payment has been completed',
                'data' => [
                    'reference_no' => $transaction->reference_no,
                    'faspay_reference_no' => $transaction->faspay_reference_no,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'paid_at' => $transaction->paid_at ? $transaction->paid_at->toIso8601String() : null,
                    'callback_url' => $transaction->callback_url,
                    'callback_status' => $transaction->callback_status,
                    'callback_sent_at' => $transaction->callback_sent_at ? $transaction->callback_sent_at->toIso8601String() : null,
                ]
            ], Response::HTTP_OK);
        }

        try {
            $snapService = new FaspaySnapService($company);
            $result = $snapService->queryQris($referenceNo, $faspayRefNo, $channelCode);

            if (!$result) {
                return response()->json([
                    'status' => 'error',
                    'responseCode' => '4045101',
                    'responseMessage' => 'Transaction status inquiry failed or not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if Faspay indicates payment completed
            $latestStatus = $result['latestTransactionStatus'] ?? null;
            if ($latestStatus === '00' && $transaction && !$transaction->isPaid()) {
                $transaction->markAsPaid($result);
                $dispatcher->dispatch($transaction, $result);
            }

            return response()->json([
                'status' => 'success',
                'responseCode' => $result['responseCode'] ?? '2005100',
                'responseMessage' => $result['responseMessage'] ?? 'Query successful',
                'data' => array_merge($result, [
                    'database_status' => $transaction->status ?? 'pending',
                    'callback_status' => $transaction->callback_status ?? 'none',
                ])
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            Log::error('Exception in ExternalQrisController@status', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'responseCode' => '5000000',
                'responseMessage' => 'Internal server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Manually resend/test webhook notification to 3rd party callback URL.
     */
    public function resendWebhook(Request $request, string $referenceNo, ExternalQrisWebhookDispatcher $dispatcher): JsonResponse
    {
        $transaction = ExternalQrisTransaction::where('reference_no', $referenceNo)
            ->orWhere('faspay_reference_no', $referenceNo)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '4040001',
                'responseMessage' => 'External QRIS transaction not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Allow overriding callback URL for testing
        if ($request->has('callback_url')) {
            $transaction->callback_url = $request->input('callback_url');
        }

        $dispatchResult = $dispatcher->dispatch($transaction);

        return response()->json([
            'status' => $dispatchResult['delivered'] ? 'success' : 'failed',
            'responseCode' => $dispatchResult['delivered'] ? '2000000' : '5000001',
            'responseMessage' => $dispatchResult['delivered'] ? 'Webhook delivered successfully' : 'Webhook delivery failed',
            'data' => [
                'transaction' => [
                    'reference_no' => $transaction->reference_no,
                    'status' => $transaction->status,
                    'callback_url' => $transaction->callback_url,
                    'callback_status' => $transaction->callback_status,
                    'callback_http_status' => $transaction->callback_http_status,
                    'callback_attempts' => $transaction->callback_attempts,
                    'callback_sent_at' => $transaction->callback_sent_at ? $transaction->callback_sent_at->toIso8601String() : null,
                ],
                'dispatch_result' => $dispatchResult,
            ]
        ], $dispatchResult['delivered'] ? Response::HTTP_OK : Response::HTTP_BAD_GATEWAY);
    }
}
