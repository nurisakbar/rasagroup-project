<?php
$userId = 'bot37020';
$password = 'p@ssw0rd';
$merchantId = '37020';
$billNo = 'TEST-' . time();
$signature = sha1(md5($userId . $password . $billNo));

$data = [
    'request' => 'Transmisi Info Detil Pembelian',
    'merchant_id' => $merchantId,
    'merchant' => 'Rasa Group',
    'bill_no' => $billNo,
    'bill_reff' => '12345',
    'bill_date' => date('Y-m-d H:i:s'),
    'bill_expired' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'bill_desc' => 'Test Pembayaran Rasa Group',
    'bill_currency' => 'IDR',
    'bill_gross' => '100000',
    'bill_miscfee' => '0',
    'bill_total' => '100000',
    'cust_no' => 'CUST001',
    'cust_name' => 'John',
    'cust_lastname' => 'Doe',
    'payment_channel' => '702', // Permata VA
    'pay_type' => '1',
    'bank_userid' => '',
    'msisdn' => '081234567890',
    'email' => 'john.doe@example.com',
    'terminal' => '10',
    'billing_name' => 'John Doe',
    'billing_lastname' => 'Doe',
    'billing_address' => 'Jl. Kebon Kacang',
    'billing_address_city' => 'Jakarta',
    'billing_address_region' => 'DKI Jakarta',
    'billing_address_state' => 'Indonesia',
    'billing_address_poscode' => '10240',
    'billing_msisdn' => '081234567890',
    'billing_address_country_code' => 'ID',
    'item' => [
        [
            'product' => 'Paket Rasa Group',
            'qty' => '1',
            'amount' => '100000',
            'payment_plan' => '01',
            'merchant_id' => $merchantId,
            'tenor' => '00'
        ]
    ],
    'reserve1' => '',
    'reserve2' => '',
    'signature' => $signature
];

$ch = curl_init('https://debit-sandbox.faspay.co.id/cvr/300011/10');
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
