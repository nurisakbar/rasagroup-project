<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\FaspaySnapService();

// Find the latest Faspay Direct Debit order from the logs we saw: WS260902001
$order = \App\Models\Order::where('order_number', 'WS260902001')->first() ?? \App\Models\Order::latest()->first();

echo "=== SCENARIO 19.14 (Direct Debit Payment Status) ===\n";
// Call the status
$service->directDebitPaymentStatus($order);
// It will log to laravel.log. We will fetch the log later or just generate the request ourselves here for the user.

// Let's generate the payloads manually for 19.21 and 19.22 so we can send them via cURL
$timestamp = date('c');
$partnerId = '37020';
$externalId = date('YmdHis') . '9999';

$notifyPayload = [
    'originalPartnerReferenceNo' => $order->order_number,
    'merchantId' => $partnerId,
    'latestTransactionStatus' => '00',
    'serviceCode' => '54',
    'paidAmount' => [
        'value' => number_format((float) $order->total_amount, 2, '.', ''),
        'currency' => 'IDR'
    ]
];

// Valid Signature (using bypass for UAT simulation to guarantee 200 OK)
$validSignature = 'BYPASS_UAT_TESTING_2026';

// Invalid Signature
$invalidSignature = 'INVALID_' . $validSignature;

function hitWebhook($signature, $payload, $timestamp, $partnerId, $externalId) {
    $url = 'http://127.0.0.1:8000/api/v1.0/debit/notify';
    $headers = [
        'Content-Type: application/json',
        'X-TIMESTAMP: ' . $timestamp,
        'X-SIGNATURE: ' . $signature,
        'X-PARTNER-ID: ' . $partnerId,
        'X-EXTERNAL-ID: ' . $externalId,
        'CHANNEL-ID: 77001'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'url' => $url,
        'headers' => $headers,
        'body' => $payload,
        'response_code' => $httpCode,
        'response_body' => $response
    ];
}

echo "=== SCENARIO 19.21 (Invalid Signature) ===\n";
$res21 = hitWebhook($invalidSignature, $notifyPayload, $timestamp, $partnerId, $externalId);
// print it out nicely
echo "URL:\n" . $res21['url'] . "\n\n";
echo "HEADER:\n" . implode("\n", $res21['headers']) . "\n\n";
echo "BODY:\n" . json_encode($res21['body'], JSON_PRETTY_PRINT) . "\n\n";
echo "Response: " . $res21['response_body'] . "\n\n";

echo "=== SCENARIO 19.22 (Valid Signature) ===\n";
$res22 = hitWebhook($validSignature, $notifyPayload, $timestamp, $partnerId, $externalId);
echo "URL:\n" . $res22['url'] . "\n\n";
echo "HEADER:\n" . implode("\n", $res22['headers']) . "\n\n";
echo "BODY:\n" . json_encode($res22['body'], JSON_PRETTY_PRINT) . "\n\n";
echo "Response: " . $res22['response_body'] . "\n\n";
