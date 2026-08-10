<?php

if (php_sapi_name() !== 'cli') {
    die("Harus dijalankan dari CLI.\n");
}

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = $argv[1] ?? 'https://dev.rasaconnect.com/api/faspay/snap';
// Pastikan tidak ada trailing slash
$baseUrl = rtrim($baseUrl, '/');

echo "Memulai Faspay UAT Simulator ke: {$baseUrl}\n";
echo "Pastikan Anda menjalankan ini di server yang sudah di-whitelist Faspay (jika diperlukan).\n\n";

$testCases = [
    "11.1" => [
        "name" => "Access Token Invalid",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Authorization: Bearer INVALID_TOKEN", "Content-Type: application/json"],
        "payload" => []
    ],
    "11.2" => [
        "name" => "Unauthorized Signature",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["X-SIGNATURE: INVALID_SIGNATURE", "Content-Type: application/json"],
        "payload" => ["partnerServiceId" => "370201", "customerNo" => "123", "virtualAccountNo" => "370201123"]
    ],
    "11.3" => [
        "name" => "Missing Mandatory Field",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => ["partnerServiceId" => "370201"] // Missing virtualAccountNo dll
    ],
    "11.4" => [
        "name" => "Invalid Field Format",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => ["partnerServiceId" => "370201", "virtualAccountNo" => 370201123] // Int instead of string
    ],
    "11.5" => [
        "name" => "Cannot use the same X-EXTERNAL-ID",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["X-EXTERNAL-ID: SAME_ID_123", "Content-Type: application/json"],
        "payload" => ["partnerServiceId" => "370201", "virtualAccountNo" => "370201123"]
    ],
    "11.6" => [
        "name" => "Inquiry VA - Input no Virtual Account Valid",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345679",
            "virtualAccountNo" => "3685000212345679",
            "channelCode" => "6011",
            "inquiryRequestId" => "REQ-001"
        ]
    ],
    "11.7" => [
        "name" => "Inquiry VA - Input no Virtual Account Valid sudah lunas",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345678",
            "virtualAccountNo" => "3685000212345678",
            "inquiryRequestId" => "REQ-002"
        ]
    ],
    "11.8" => [
        "name" => "Inquiry VA - Input no Virtual Account Valid kadaluarsa",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345677", // Asumsi VA expired
            "virtualAccountNo" => "3685000212345677",
            "inquiryRequestId" => "REQ-003"
        ]
    ],
    "11.9" => [
        "name" => "Inquiry VA - Input no Virtual Account tidak terdaftar",
        "url" => $baseUrl . "/inquiry",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "9999999999",
            "virtualAccountNo" => "3685009999999999",
            "inquiryRequestId" => "REQ-004"
        ]
    ],
    "11.10" => [
        "name" => "Payment VA - Input no VA Valid (Closed Amount)",
        "url" => $baseUrl . "/payment",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345679",
            "virtualAccountNo" => "3685000212345679",
            "paidAmount" => ["value" => "40000.00", "currency" => "IDR"],
            "paymentRequestId" => "PAY-001",
            "latestTransactionStatus" => "00"
        ]
    ],
    "11.11" => [
        "name" => "Payment VA - Input no VA tidak terdaftar",
        "url" => $baseUrl . "/payment",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "9999999999",
            "virtualAccountNo" => "3685009999999999",
            "paidAmount" => ["value" => "40000.00", "currency" => "IDR"],
            "paymentRequestId" => "PAY-002"
        ]
    ],
    "11.12" => [
        "name" => "Payment VA - Invalid Amount",
        "url" => $baseUrl . "/payment",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345679",
            "virtualAccountNo" => "3685000212345679",
            "paidAmount" => ["value" => "1000.00", "currency" => "IDR"], // Wrong amount
            "paymentRequestId" => "PAY-003",
            "latestTransactionStatus" => "00"
        ]
    ],
    "11.16" => [
        "name" => "Payment VA - Open Amount",
        "url" => $baseUrl . "/payment",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345679",
            "virtualAccountNo" => "3685000212345679",
            "paidAmount" => ["value" => "150000.00", "currency" => "IDR"],
            "paymentRequestId" => "PAY-004",
            "latestTransactionStatus" => "00"
        ]
    ],
    "11.17" => [
        "name" => "Payment Notification - Merchant Success",
        "url" => $baseUrl . "/payment",
        "headers" => ["Content-Type: application/json"],
        "payload" => [
            "partnerServiceId" => "368500",
            "customerNo" => "0212345679",
            "virtualAccountNo" => "3685000212345679",
            "trx_id" => "TRX-UAT-999",
            "payment_status_code" => "2" // Success in legacy format
        ]
    ],
];

$results = [];

foreach ($testCases as $id => $tc) {
    echo "Running [{$id}] {$tc['name']}...\n";
    
    $ch = curl_init($tc['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tc['payload']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $tc['headers']);
    
    // Ignore SSL if hitting local/dev without valid certs
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $results[$id] = [
        "scenario" => $tc['name'],
        "request_url" => $tc['url'],
        "request_headers" => $tc['headers'],
        "request_payload" => $tc['payload'],
        "http_code" => $httpCode,
        "response_body" => json_decode($response, true) ?? $response,
        "curl_error" => $error
    ];
    
    // Log directly to Laravel's logger
    \Illuminate\Support\Facades\Log::channel('single')->info("Faspay UAT Simulation Result - Scenario [{$id}]", $results[$id]);
}

$outputFile = __DIR__ . '/faspay_uat_results.json';
file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\nSelesai! Hasil simulasi disimpan dalam bentuk JSON di: {$outputFile}\n";
echo "Dan semua hasil request & response juga telah dicatat ke dalam storage/logs/laravel.log!\n";
echo "Nanti Anda tinggal mendownload storage/logs/laravel.log tersebut dan memberikannya kepada saya.\n";
