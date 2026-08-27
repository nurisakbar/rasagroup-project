<?php
$clientId = 'bot37020'; // Try user_id as Client Key
$privateKeyPath = 'storage/app/faspay_private_key_dev.pem';
if (!file_exists($privateKeyPath)) {
    die("Private key not found\n");
}
$privateKey = file_get_contents($privateKeyPath);
$timestamp = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d\TH:i:sP');
$stringToSign = $clientId . '|' . $timestamp;
$keyResource = openssl_pkey_get_private($privateKey);
openssl_sign($stringToSign, $signature, $keyResource, OPENSSL_ALGO_SHA256);
$signatureBase64 = base64_encode($signature);

$headers = [
    'X-TIMESTAMP: ' . $timestamp,
    'X-CLIENT-KEY: ' . $clientId,
    'X-SIGNATURE: ' . $signatureBase64,
    'Content-Type: application/json',
];

$payload = json_encode([
    'grantType' => 'client_credentials',
    'additionalInfo' => new stdClass(),
]);

$ch = curl_init('https://debit-sandbox.faspay.co.id/v1.0/access-token/b2b');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo "Result for $clientId:\n$result\n";
