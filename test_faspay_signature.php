<?php

use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Faspay E2E Signature Simulation...\n";

// 1. Generate RSA Key Pair
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];
$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privateKey);
$publicKeyDetails = openssl_pkey_get_details($res);
$publicKey = $publicKeyDetails["key"];

// 2. Backup original public key & replace with ours temporarily
$publicKeyPath = storage_path('app/faspay_public_key.pem');
$originalPublicKey = file_exists($publicKeyPath) ? file_get_contents($publicKeyPath) : null;
file_put_contents($publicKeyPath, $publicKey);
echo "Temporarily replaced faspay_public_key.pem with test key.\n";

try {
    // 3. Create a dummy order
    $user = User::first() ?? User::factory()->create();
    $orderNumber = 'TEST_FASPAY_' . time();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => $orderNumber,
        'total_amount' => 150000.00,
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    echo "Created dummy order: {$orderNumber}\n";

    // 4. Construct payload
    $payload = [
        'virtualAccountNo' => $orderNumber,
        'latestTransactionStatus' => '00',
        'paidAmount' => [
            'value' => '150000.00',
            'currency' => 'IDR'
        ],
        'type' => 'payment'
    ];
    $bodyStr = json_encode($payload);
    $bodyHash = strtolower(hash('sha256', $bodyStr));
    
    // 5. Create string to sign
    $method = 'POST';
    // Path inside the URL as seen by the router (standard SNAP BI path)
    $path = '/api/v1.0/transfer-va/payment';
    $timestamp = date('c'); // ISO 8601
    
    // SNAP BI Formula for Webhook
    $stringToSign = $method . ":" . $path . ":" . $bodyHash . ":" . $timestamp;
    echo "String to Sign:\n{$stringToSign}\n\n";

    // 6. Sign it
    openssl_sign($stringToSign, $signatureData, $privateKey, OPENSSL_ALGO_SHA256);
    $signature = base64_encode($signatureData);
    echo "Generated X-SIGNATURE: " . substr($signature, 0, 30) . "...\n";

    // 7. Send Request
    $url = 'http://127.0.0.1:8000' . $path;
    echo "Sending webhook request to: {$url}\n";
    $response = Http::withHeaders([
        'CHANNEL-ID' => '77001',
        'X-TIMESTAMP' => $timestamp,
        'X-SIGNATURE' => $signature,
        'Content-Type' => 'application/json'
    ])->send('POST', $url, [
        'body' => $bodyStr
    ]);

    echo "Response Status: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";

    // 8. Verify Order Status
    $order->refresh();
    echo "Order Payment Status in DB: " . $order->payment_status . "\n";
    if ($order->payment_status === 'paid' && $response->status() === 200) {
        echo "✅ Simulation PASS: Webhook processed and order paid successfully!\n";
    } else {
        echo "❌ Simulation FAIL: Order was not marked as paid or response was not 200.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    // 9. Restore original public key
    if ($originalPublicKey !== null) {
        file_put_contents($publicKeyPath, $originalPublicKey);
        echo "Restored original faspay_public_key.pem.\n";
    } else {
        unlink($publicKeyPath);
    }
}
