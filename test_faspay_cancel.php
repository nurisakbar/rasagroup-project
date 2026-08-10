<?php
$userId = 'bot37020';
$password = 'p@ssw0rd';
$merchantId = '37020';
$billNo = 'TEST-1785827135';
$trxId = '3702070213755971';
$signature = sha1(md5($userId . $password . $billNo));

$data = [
    'request' => 'Cancel Transaction',
    'trx_id' => $trxId,
    'merchant_id' => $merchantId,
    'merchant' => 'Rasa Group',
    'bill_no' => $billNo,
    'payment_cancel' => 'Testing cancellation for UAT',
    'signature' => $signature
];

$ch = curl_init('https://debit-sandbox.faspay.co.id/cvr/100005/10');
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
