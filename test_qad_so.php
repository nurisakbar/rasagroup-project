<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payload = [
    'domainCode' => 'MCR',
    'salesOrderNumber' => 'WS260916003',
    'billToCustomerCode' => 'ZH53606',
    'soldToCustomerCode' => 'ZH53606',
    'shipToCustomerCode' => 'ZH53606',
    'orderDate' => '2026-09-16T00:00:00.000Z',
    'dueDate' => '2026-09-16T00:00:00.000Z',
    'requiredDate' => '2026-09-16T00:00:00.000Z',
    'shipDate' => '2026-09-16T00:00:00.000Z',
    'promiseDate' => '2026-09-16T00:00:00.000Z',
    'creditTermsCode' => 'CIA',
    'remarks' => 'TOT: pembayaran tempo 30',
    'purchaseOrderNumber' => '2609160803',
    'taxClass' => 'PPN',
    'isTaxable' => true,
    'salespersonCode_01' => 'SLS00001',
    'isSelfBillingEnabled' => true,
    'salesOrderLines' => [
        [
            'salesOrderNumber' => 'WS260916003',
            'salesOrderLine' => 1,
            'itemCode' => 'FMA010-CL03',
            'quantityOrdered' => 1,
            'unitOfMeasure' => 'BT',
            'listPrice' => 20000,
            'discountPercent' => 0,
            'netPrice' => 20000,
            'dueDate' => '2026-09-23T00:00:00.000Z',
            'isTaxable' => true,
            'salesAcct' => '41101',
            'salesCC' => '',
            'discountAcct' => '41101',
            'discountCC' => ''
        ]
    ]
];

$api = app(\App\Services\QidApiService::class);
echo "Sending payload...\n";
$res = $api->post('/api/transaction/sales-orders/create', $payload, true);
print_r($res);
