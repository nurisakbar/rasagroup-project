<?php
$userId = 'bot37020';
$password = 'p@ssw0rd';
$merchantId = '37020';
$signature = sha1(md5($userId . $password));

$data = [
    'request' => 'Inquiry Payment Channel',
    'merchant_id' => $merchantId,
    'merchant' => 'Rasa Group',
    'signature' => $signature
];

$ch = curl_init('https://debit-sandbox.faspay.co.id/cvr/100001/10');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

echo "Signature: $signature\n";
echo "Response: $response\n";
