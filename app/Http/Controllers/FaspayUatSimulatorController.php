<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaspayUatSimulatorController extends Controller
{
    public function simulate(Request $request)
    {
        $baseUrl = 'https://dev.rasaconnect.com/api/faspay/snap';
        
        // Cek apakah ada base URL spesifik yang dikirim lewat query (opsional)
        if ($request->has('base_url')) {
            $baseUrl = rtrim($request->input('base_url'), '/');
        }

        $baseHeaders = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c",
            "X-SIGNATURE" => "",
            "X-TIMESTAMP" => now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
            "X-PARTNER-ID" => "37020",
            "X-EXTERNAL-ID" => time() . rand(1000, 9999),
            "CHANNEL-ID" => "77001"
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
                "headers" => array_merge($baseHeaders, ["X-SIGNATURE" => "INVALID_SIGNATURE"]),
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
                "headers" => array_merge($baseHeaders, ["X-EXTERNAL-ID" => "1234567890"]),
                "payload" => ["partnerServiceId" => "370201", "virtualAccountNo" => "370201123"]
            ],
            "11.6" => [
                "name" => "Inquiry VA - Input no Virtual Account Valid",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "WS260807002",
                    "virtualAccountNo" => "370201WS260807002",
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
                    "customerNo" => "WS260810001",
                    "virtualAccountNo" => "370201WS260810001",
                    "inquiryRequestId" => "REQ-002"
                ]
            ],
            "11.8" => [
                "name" => "Inquiry VA - Input no Virtual Account Valid kadaluarsa",
                "url" => $baseUrl . "/inquiry",
                "headers" => $baseHeaders,
                "payload" => [
                    "partnerServiceId" => "370201",
                    "customerNo" => "WS260807003", 
                    "virtualAccountNo" => "370201WS260807003",
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
                    "customerNo" => "WS260807004",
                    "virtualAccountNo" => "370201WS260807004",
                    "paidAmount" => ["value" => "2281541.00", "currency" => "IDR"],
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
                    "customerNo" => "WS260807004",
                    "virtualAccountNo" => "370201WS260807004",
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
                    "customerNo" => "WS260807004",
                    "virtualAccountNo" => "370201WS260807004",
                    "paidAmount" => ["value" => "2291541.00", "currency" => "IDR"],
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
                    "customerNo" => "WS260807004",
                    "virtualAccountNo" => "370201WS260807004",
                    "paidAmount" => ["value" => "2281541.00", "currency" => "IDR"],
                    "paymentRequestId" => "PAY-005"
                ]
            ],
        ];

        $results = [];

        foreach ($testCases as $id => $tc) {
            try {
                $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
                $externalId = time() . rand(1000, 9999);
                
                // Make unique request identifiers
                if (isset($tc['payload']['inquiryRequestId'])) {
                    $tc['payload']['inquiryRequestId'] = "REQ-" . uniqid();
                }
                if (isset($tc['payload']['paymentRequestId'])) {
                    $tc['payload']['paymentRequestId'] = "PAY-" . uniqid();
                }
                if (str_contains($tc['url'], '/payment')) {
                    $tc['payload']['trxDateTime'] = $timestamp;
                    $tc['payload']['referenceNo'] = "REF-" . time() . rand(100, 999);
                }

                // Set fresh timestamp
                $tc['headers']['X-TIMESTAMP'] = $timestamp;
                
                // Set fresh external ID unless explicitly testing duplicate ID
                if (!isset($tc['headers']['X-EXTERNAL-ID']) || $tc['headers']['X-EXTERNAL-ID'] !== '1234567890') {
                    $tc['headers']['X-EXTERNAL-ID'] = $externalId;
                }
                
                // 1. Construct standard SNAP StringToSign
                $method = 'POST';
                $path = parse_url($tc['url'], PHP_URL_PATH);
                $bodyStr = json_encode($tc['payload']);
                $bodyHash = strtolower(hash('sha256', $bodyStr));
                $stringToSign = $method . ":" . $path . ":" . $bodyHash . ":" . $timestamp;

                // 2. Generate RSA Signature using Private Key
                $privateKeyPath = storage_path('app/faspay_private_key.pem');
                if (file_exists($privateKeyPath)) {
                    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
                    openssl_sign($stringToSign, $signatureBytes, $privateKey, OPENSSL_ALGO_SHA256);
                    $authenticRsaSignature = base64_encode($signatureBytes);
                } else {
                    // Fallback pseudo-random for environments without the private key
                    $hash1 = hash('sha512', $stringToSign . "1", true);
                    $hash2 = hash('sha512', $stringToSign . "2", true);
                    $hash3 = hash('sha512', $stringToSign . "3", true);
                    $hash4 = hash('sha512', $stringToSign . "4", true);
                    $authenticRsaSignature = base64_encode($hash1 . $hash2 . $hash3 . $hash4);
                }
                
                // Set RSA signature
                if ($id === "11.2") {
                    // Make it explicitly invalid by changing the first character to 'z'
                    $tc['headers']['X-SIGNATURE'] = 'z' . substr($authenticRsaSignature, 1);
                } else {
                    $tc['headers']['X-SIGNATURE'] = $authenticRsaSignature;
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
