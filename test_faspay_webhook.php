<?php
$userId = 'bot37020';
$password = 'p@ssw0rd';
$merchantId = '37020';
$billNo = 'TEST-12345'; // Simulate an order number
$paymentStatusCode = '2'; // 2 = Success
$signature = sha1(md5($userId . $password . $billNo . $paymentStatusCode));

$data = [
    'request' => 'Payment Notification',
    'trx_id' => '3702070213755971',
    'merchant_id' => $merchantId,
    'merchant' => 'Rasa Group',
    'bill_no' => $billNo,
    'payment_reff' => 'REFF-123',
    'payment_date' => date('Y-m-d H:i:s'),
    'payment_status_code' => $paymentStatusCode,
    'payment_status_desc' => 'Payment Sukses',
    'bill_total' => '100000',
    'payment_total' => '100000',
    'payment_channel_uid' => '402',
    'payment_channel' => 'Permata',
    'signature' => $signature
];

$ch = curl_init('http://127.0.0.1:8000/webhooks/faspay');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
// Send as JSON since the docs say "sample-json-request"
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($userId . ':' . $password)
]);
$response = curl_exec($ch);
curl_close($ch);

echo "Sent Data: " . json_encode($data) . "\n";
echo "Response from our webhook: $response\n";
