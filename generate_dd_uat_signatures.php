<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\FaspaySnapService();
$privateKeyPath = storage_path('app/faspay_private_key_dev.pem');
$privateKey = file_get_contents($privateKeyPath);

$paymentUrl = 'https://debit-sandbox.faspay.co.id/v1.0/debit/payment-host-to-host';
$statusUrl = 'https://debit-sandbox.faspay.co.id/v1.0/debit/status';
$paymentEndpoint = '/v1.0/debit/payment-host-to-host';
$statusEndpoint = '/v1.0/debit/status';

$basePayload = [
    'partnerReferenceNo' => 'WS260903001',
    'merchantId' => '37020',
    'amount' => ['value' => '60000.00', 'currency' => 'IDR'],
    'customerEmail' => 'nuris.akbar@gmail.com',
    'customerPhone' => '08969935552',
    'validUpTo' => '2026-09-04T15:02:30+07:00',
    'additionalInfo' => [
        'billDate' => '2026-09-03T15:02:30+07:00',
        'channelCode' => '812',
        'paymentChannelUid' => '812',
        'customerName' => 'Nuris Akbar',
        'billDescription' => 'Payment #WS260903001',
    ],
];

$scenarios = [];

// 19.2 - Invalid Signature
$ts = '2026-09-03T15:02:01+07:00';
$validSig = $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $basePayload, $ts, $privateKey);
$scenarios['19.2'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802010001',
    'signature' => 'INVALID_' . substr($validSig, 7),
    'payload' => $basePayload,
    'url' => $paymentUrl,
];

// 19.3 - Missing merchantId
$ts = '2026-09-03T15:02:02+07:00';
$p193 = $basePayload;
unset($p193['merchantId']);
$scenarios['19.3'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802020002',
    'signature' => $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $p193, $ts, $privateKey),
    'payload' => $p193,
    'url' => $paymentUrl,
];

// 19.4 - Invalid amount format
$ts = '2026-09-03T15:02:03+07:00';
$p194 = json_decode(json_encode($basePayload), true);
$p194['amount']['value'] = 'abc';
$scenarios['19.4'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802030003',
    'signature' => $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $p194, $ts, $privateKey),
    'payload' => $p194,
    'url' => $paymentUrl,
];

// 19.5 - Duplicate X-EXTERNAL-ID (two attempts, same external id, different timestamps)
$ts1 = '2026-09-03T15:02:04+07:00';
$ts2 = '2026-09-03T15:02:05+07:00';
$extId = '202609030802040004';
$scenarios['19.5'] = [
    'attempt1' => [
        'timestamp' => $ts1,
        'external_id' => $extId,
        'signature' => $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $basePayload, $ts1, $privateKey),
        'payload' => $basePayload,
        'url' => $paymentUrl,
    ],
    'attempt2' => [
        'timestamp' => $ts2,
        'external_id' => $extId,
        'signature' => $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $basePayload, $ts2, $privateKey),
        'payload' => $basePayload,
        'url' => $paymentUrl,
    ],
];

// 19.6 - Success (from laravel(8).log)
$scenarios['19.6'] = [
    'timestamp' => '2026-09-03T15:02:30+07:00',
    'external_id' => '202609030802305541',
    'signature' => 'Kvg42huui5HFi/x2Udu/OLMt7390RhHTpMVazohEOwZzk51E5J2WnqCA3H9FFRUi1bXlcJblxVgW7umpRwjhV6mDYV8GQlUAHgXBnM+lelTk234JobTUbxJhtW/WwjIkykQfz3zOh5U2H3V/NjDmYIF1jy3ljpP6+UtZYaC7srPiBkLRxXj+v/bc79lOD4BzPmZtTPKaeBCco2ZR6CsqYlhHssMfP2DV4xBaEDCmCGHKOr9Kh4DWWX+N/ULQQq9S6ESX9VHiWrpHMqTd6xmXITdJ7wDMU05cLWHYTenxnOTl6wrJXUYJ8okCyElhkLZOWI8BxZ3fG18fgFvXAul4dw==',
    'payload' => [
        'partnerReferenceNo' => 'WS260903001',
        'merchantId' => '37020',
        'amount' => ['value' => '60000.00', 'currency' => 'IDR'],
        'customerEmail' => 'nuris.akbar@gmail.com',
        'customerPhone' => '08969935552',
        'validUpTo' => '2026-09-04T15:02:30+07:00',
        'additionalInfo' => [
            'billDate' => '2026-09-03T15:02:30+07:00',
            'channelCode' => '812',
            'paymentChannelUid' => '812',
            'customerName' => 'Nuris Akbar',
            'billDescription' => 'Payment #WS260903001',
        ],
    ],
    'url' => $paymentUrl,
    'response' => [
        'responseCode' => '2005400',
        'responseMessage' => 'Request has been processed successfully',
        'referenceNo' => '3702081255404571',
        'partnerReferenceNo' => 'WS260903001',
        'webRedirectUrl' => 'https://debit-sandbox.faspay.co.id/pws/100003/0830000010100000/838e5b7744a80ba06a38e69b4c738d052169aa21?trx_id=3702081255404571&merchant_id=37020&bill_no=WS260903001',
        'additionalInfo' => [
            'merchantId' => '37020',
            'amount' => ['value' => '60000.00', 'currency' => 'IDR'],
        ],
    ],
];

// 19.7 - Invalid Merchant
$ts = '2026-09-03T15:02:06+07:00';
$p197 = $basePayload;
$p197['merchantId'] = '99999';
$scenarios['19.7'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802060006',
    'signature' => $service->generateTransactionAsymmetricSignature('POST', $paymentEndpoint, $p197, $ts, $privateKey),
    'payload' => $p197,
    'url' => $paymentUrl,
];

// 19.13 - Payment Status
$ts = '2026-09-03T15:02:07+07:00';
$p1913 = [
    'originalReferenceNo' => '3702081255404571',
    'originalPartnerReferenceNo' => 'WS260903001',
    'merchantId' => '37020',
    'serviceCode' => '55',
];
$scenarios['19.13'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802070007',
    'signature' => $service->generateTransactionAsymmetricSignature('POST', $statusEndpoint, $p1913, $ts, $privateKey),
    'payload' => $p1913,
    'url' => $statusUrl,
];

// 19.14 - Transaction Not Found
$ts = '2026-09-03T15:02:08+07:00';
$p1914 = [
    'originalPartnerReferenceNo' => 'WS260903999',
    'merchantId' => '37020',
    'serviceCode' => '55',
];
$scenarios['19.14'] = [
    'timestamp' => $ts,
    'external_id' => '202609030802080008',
    'signature' => $service->generateTransactionAsymmetricSignature('POST', $statusEndpoint, $p1914, $ts, $privateKey),
    'payload' => $p1914,
    'url' => $statusUrl,
];

file_put_contents(__DIR__ . '/dd_uat_signatures.json', json_encode($scenarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Signatures generated and saved to dd_uat_signatures.json\n";
