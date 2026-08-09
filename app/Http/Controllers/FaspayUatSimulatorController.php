<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaspayUatSimulatorController extends Controller
{
    public function simulate(Request $request)
    {
        $baseUrl = url('/api/faspay/snap');
        
        // Cek apakah ada base URL spesifik yang dikirim lewat query (opsional)
        if ($request->has('base_url')) {
            $baseUrl = rtrim($request->input('base_url'), '/');
        }

        $baseHeaders = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...", // Mock valid token
            "X-SIGNATURE" => "VALID_SIGNATURE_123",
            "X-TIMESTAMP" => now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
            "X-PARTNER-ID" => "37020",
            "X-EXTERNAL-ID" => "EXT-" . time() . rand(100, 999),
            "CHANNEL-ID" => "6011"
        ];
        
        $testCases = [
            "11.1" => [
                "name" => "Access Token Invalid",
                "url" => $baseUrl . "/inquiry",
                "headers" => array_merge($baseHeaders, ["Authorization" => "Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.invalid_signature_mockup_123456"]),
                "payload" => []
            ],
            "11.2" => [
                "name" => "Unauthorized Signature",
                "url" => $baseUrl . "/inquiry",
                "headers" => array_merge($baseHeaders, ["X-SIGNATURE" => base64_encode("wrong_signature_content_that_looks_real_12345")]),
                "payload" => ["partnerServiceId" => "370201", "customerNo" => "123", "virtualAccountNo" => "370201123"]
            ],
            "11.3" => [
                "name" => "Missing Mandatory Field",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => ["partnerServiceId" => "370201"] // Missing virtualAccountNo dll
            ],
            "11.4" => [
                "name" => "Invalid Field Format",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => ["partnerServiceId" => "370201", "virtualAccountNo" => 370201123] // Int instead of string
            ],
            "11.5" => [
                "name" => "Cannot use the same X-EXTERNAL-ID",
                "url" => $baseUrl . "/inquiry",
                "headers" => array_merge($baseHeaders, ["X-EXTERNAL-ID" => "SAME_ID_123"]),
                "payload" => ["partnerServiceId" => "370201", "virtualAccountNo" => "370201123"]
            ],
            "11.6" => [
                "name" => "Inquiry VA - Input no Virtual Account Valid",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345679",
                    "virtualAccountNo" => "3702010212345679",
                    "channelCode" => "6011",
                    "inquiryRequestId" => "REQ-001"
                ]
            ],
            "11.7" => [
                "name" => "Inquiry VA - Input no Virtual Account Valid sudah lunas",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345678",
                    "virtualAccountNo" => "3702010212345678",
                    "inquiryRequestId" => "REQ-002"
                ]
            ],
            "11.8" => [
                "name" => "Inquiry VA - Input no Virtual Account Valid kadaluarsa",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345677", 
                    "virtualAccountNo" => "3702010212345677",
                    "inquiryRequestId" => "REQ-003"
                ]
            ],
            "11.9" => [
                "name" => "Inquiry VA - Input no Virtual Account tidak terdaftar",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "9999999999",
                    "virtualAccountNo" => "3702019999999999",
                    "inquiryRequestId" => "REQ-004"
                ]
            ],
            "11.10" => [
                "name" => "Payment VA - Input no VA Valid (Closed Amount)",
                "url" => $baseUrl . "/payment",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345679",
                    "virtualAccountNo" => "3702010212345679",
                    "paidAmount" => ["value" => "40000.00", "currency" => "IDR"],
                    "paymentRequestId" => "PAY-001",
                    "latestTransactionStatus" => "00"
                ]
            ],
            "11.11" => [
                "name" => "Payment VA - Input no VA tidak terdaftar",
                "url" => $baseUrl . "/payment",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "9999999999",
                    "virtualAccountNo" => "3702019999999999",
                    "paidAmount" => ["value" => "40000.00", "currency" => "IDR"],
                    "paymentRequestId" => "PAY-002"
                ]
            ],
            "11.12" => [
                "name" => "Payment VA - Invalid Amount",
                "url" => $baseUrl . "/payment",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345679",
                    "virtualAccountNo" => "3702010212345679",
                    "paidAmount" => ["value" => "1000.00", "currency" => "IDR"], 
                    "paymentRequestId" => "PAY-003",
                    "latestTransactionStatus" => "00"
                ]
            ],
            "11.16" => [
                "name" => "Payment VA - Open Amount",
                "url" => $baseUrl . "/payment",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345679",
                    "virtualAccountNo" => "3702010212345679",
                    "paidAmount" => ["value" => "150000.00", "currency" => "IDR"],
                    "paymentRequestId" => "PAY-004",
                    "latestTransactionStatus" => "00"
                ]
            ],
            "11.17" => [
                "name" => "Payment Notification - Merchant Success",
                "url" => $baseUrl . "/payment",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "0212345679",
                    "virtualAccountNo" => "3702010212345679",
                    "trx_id" => "TRX-UAT-999",
                    "payment_status_code" => "2"
                ]
            ],
        ];

        $results = [];

        foreach ($testCases as $id => $tc) {
            try {
                $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
                $externalId = "EXT-" . time() . "-" . rand(1000, 9999);
                
                // Make unique request identifiers
                if (isset($tc['payload']['inquiryRequestId'])) {
                    $tc['payload']['inquiryRequestId'] = "REQ-" . uniqid();
                }
                if (isset($tc['payload']['paymentRequestId'])) {
                    $tc['payload']['paymentRequestId'] = "PAY-" . uniqid();
                }
                if (isset($tc['payload']['trx_id']) && $tc['payload']['trx_id'] === 'TRX-UAT-999') {
                    $tc['payload']['trx_id'] = "TRX-UAT-" . uniqid();
                }

                // Set fresh timestamp
                $tc['headers']['X-TIMESTAMP'] = $timestamp;
                
                // Set fresh external ID unless explicitly testing duplicate ID
                if (!isset($tc['headers']['X-EXTERNAL-ID']) || $tc['headers']['X-EXTERNAL-ID'] !== 'SAME_ID_123') {
                    $tc['headers']['X-EXTERNAL-ID'] = $externalId;
                }
                
                // Generate realistic signature unless explicitly testing invalid signature
                if (!isset($tc['headers']['X-SIGNATURE']) || $tc['headers']['X-SIGNATURE'] !== 'INVALID_SIGNATURE') {
                    $payloadStr = json_encode($tc['payload']);
                    $path = parse_url($tc['url'], PHP_URL_PATH);
                    $stringToSign = "POST:" . $path . ":" . hash('sha256', $payloadStr) . ":" . $timestamp;
                    $tc['headers']['X-SIGNATURE'] = base64_encode(hash_hmac('sha512', $stringToSign, config('services.faspay.snap_client_secret', 'dummy_secret'), true));
                }

                // Gunakan internal request dispatch untuk menghindari deadlock di `php artisan serve`
                $internalRequest = Request::create($tc['url'], 'POST', [], [], [], [], json_encode($tc['payload']));
                
                foreach ($tc['headers'] as $k => $v) {
                    $internalRequest->headers->set($k, $v);
                }
                $internalRequest->headers->set('Accept', 'application/json');

                $internalResponse = app()->handle($internalRequest);
                
                $responseBody = json_decode($internalResponse->getContent(), true) ?? $internalResponse->getContent();
                $statusCode = $internalResponse->getStatusCode();

                $resultData = [
                    "scenario" => $tc['name'],
                    "request_url" => $tc['url'],
                    "request_headers" => $tc['headers'],
                    "request_payload" => $tc['payload'],
                    "http_code" => $statusCode,
                    "response_body" => $responseBody,
                    "curl_error" => null
                ];

            } catch (\Exception $e) {
                $resultData = [
                    "scenario" => $tc['name'],
                    "request_url" => $tc['url'],
                    "request_headers" => $tc['headers'],
                    "request_payload" => $tc['payload'],
                    "http_code" => 500,
                    "response_body" => null,
                    "curl_error" => $e->getMessage()
                ];
            }

            $results[$id] = $resultData;
            
            // Log ke laravel.log
            Log::info("Faspay UAT Simulation Result - Scenario [{$id}]", $resultData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulasi berhasil dijalankan dan dicatat ke storage/logs/laravel.log',
            'simulated_base_url' => $baseUrl,
            'results' => $results
        ]);
    }
}
