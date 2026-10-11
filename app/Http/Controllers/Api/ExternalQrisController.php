<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateExternalQrisRequest;
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
        $channelCode = (string) ($validated['channel_code'] ?? '715'); // 715 = Nobu QRIS, 836 = Paydia QRIS
        $validityMinutes = (int) ($validated['validity_minutes'] ?? 60);
        $format = strtolower($validated['format'] ?? 'json');

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

            // Extract image URL from response
            $qrImageUrl = $result['additionalInfo']['qrImageUrl'] ?? ($result['qrUrl'] ?? null);
            $faspayRefNo = $result['referenceNo'] ?? null;
            $partnerRefNo = $result['partnerReferenceNo'] ?? $referenceNo;
            $expiresAt = now()->addMinutes($validityMinutes)->timezone('Asia/Jakarta')->toIso8601String();

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
                    'partner_reference_no' => $partnerRefNo,
                    'reference_no' => $faspayRefNo,
                    'amount' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR',
                    'qr_image_url' => $qrImageUrl,
                    'qr_url' => $result['qrUrl'] ?? null,
                    'channel_code' => $channelCode,
                    'company' => $company,
                    'expires_at' => $expiresAt,
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
    public function status(Request $request, string $referenceNo): JsonResponse
    {
        $company = strtolower($request->query('company', 'rdi'));
        $channelCode = (string) $request->query('channel_code', '715');
        $faspayRefNo = $request->query('reference_no');

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

            return response()->json([
                'status' => 'success',
                'responseCode' => $result['responseCode'] ?? '2005100',
                'responseMessage' => $result['responseMessage'] ?? 'Query successful',
                'data' => $result
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
}
