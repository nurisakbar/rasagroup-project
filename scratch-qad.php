<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qadService = app(\App\Services\QadService::class);
$payload = [
  "domainCode" => "MCR",
  "salesOrderNumber" => "WS000001",
  "billToCustomerCode" => "ZH78584",
  "soldToCustomerCode" => "ZH78584",
  "shipToCustomerCode" => "ZH78584",
  "orderDate" => "2026-04-28T00:00:00.000Z",
  "dueDate" => "2026-04-28T00:00:00.000Z",
  "requiredDate" => "2026-04-28T00:00:00.000Z",
  "shipDate" => "2026-04-28T00:00:00.000Z",
  "promiseDate" => "2026-04-28T00:00:00.000Z",
  "creditTermsCode" => "CIA",
  "remarks" => "ORD-20260428-0002",
  "purchaseOrderNumber" => "2604280002",
  "taxClass" => "PPN",
  "isTaxable" => true,
  "salespersonCode_01" => "SLS00001",
  "isSelfBillingEnabled" => true,
  "salesOrderLines" => [
    [
      "salesOrderNumber" => "WS000001",
      "salesOrderLine" => 1,
      "itemCode" => "FMB010-MD03",
      "quantityOrdered" => 1,
      "unitOfMeasure" => "PK",
      "listPrice" => 111000,
      "discountPercent" => 0,
      "netPrice" => 111000,
      "dueDate" => "2026-05-05T00:00:00.000Z",
      "isTaxable" => true,
      "salesAcct" => "41101",
      "salesCC" => "",
      "discountAcct" => "41101",
      "discountCC" => ""
    ]
  ]
];

$result = $qadService->createSalesOrder($payload);
echo json_encode($result, JSON_PRETTY_PRINT);
