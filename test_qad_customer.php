<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qadService = app(\App\Services\QadService::class);

// Use exactly 7-8 characters for QAD Customer Code limits
$customerCode = "ZH99999";
$name = "Test Cust 99";

$brPayload = [
    'businessRelationCode' => $customerCode,
    'businessRelationName1' => $name,
    'businessRelationName2' => '',
    'businessRelationName3' => '',
    'businessRelationSearchName' => substr($name, 0, 20),
    'corporateGroupCode' => '',
    'headOfficeAddressName' => $name,
    'headOfficeAddressSearchName' => substr($name, 0, 20),
    'headOfficeAddressTypeCode' => 'HEADOFFICE',
    'headOfficeBusinessRelationCode' => $customerCode,
    'headOfficeCity' => 'Jakarta',
    'headOfficeLanguageCode' => 'us',
    'headOfficeLatitude' => 0,
    'headOfficeLongitude' => 0,
    'headOfficeStreet1' => 'Jl Test 123',
    'headOfficeStreet2' => '-',
    'headOfficeTaxClass' => 'PPN',
    'headOfficeTaxZone' => 'IDN',
    'headOfficeTelephone' => '6281234567890',
    'headOfficeWebSite' => '',
    'headOfficeZipCode' => '10110',
    'isActive' => true,
];

echo "1. Creating Business Relation...\n";
$brResult = $qadService->post('/api/master/business-relation/create', $brPayload, true);
echo json_encode($brResult, JSON_PRETTY_PRINT) . "\n\n";

$customerPayload = [
  "addressName" => $name,
  "addressSearchName" => substr($name, 0, 20),
  "businessRelationCode" => $customerCode,
  "city" => "Jakarta",
  "countryCode" => "ID",
  "languageCode" => "us",
  "street1" => "Jl Test 123",
  "street2" => "-",
  "isTaxInCity" => true,
  "taxZone" => "IDN",
  "taxClass" => "PPN",
  "reminderCountryCode" => "ID",
  "reminderLanguageCode" => "us",
  "reminderTaxZone" => "IDN",
  "customerCode" => $customerCode,
  "isActive" => true,
  "isBusinessRelationActive" => true,
  "businessRelationName" => $name,
  "invoiceControlGLProfileCode" => "12101",
  "creditNoteControlGLProfileCode" => "12101",
  "prePaymentControlGLProfileCode" => "12101",
  "salesAccountGLProfileCode" => "41101",
  "currencyCode" => "IDR",
  "customerTypeCode" => "LOC",
  "creditTermsCode" => "CIA",
  "creditTermsType" => "NORMAL",
  "invoiceStatusCode" => "APPROVED-AR",
  "isTaxable" => true,
  "sharedSetCode" => "MCR-CUST",
  "vatDeliveryType" => "SERVICE",
  "vatPercentageLevel" => "NONE",
  "addressTypeCode" => "HEADOFFICE",
  "isBusinessRelationFieldsEnabled" => true,
  "customerCurrencyCode" => "IDR",
  "isOverruleAllowedSOCreditLimit" => true
];

echo "2. Creating Customer...\n";
$customerResult = $qadService->post('/api/master/customer/create', $customerPayload, true);
echo json_encode($customerResult, JSON_PRETTY_PRINT) . "\n\n";

echo "3. Ensuring Customer Data...\n";
$dataResult = $qadService->post('/api/master/customer/create-data', ['customerCode' => $customerCode], true);
echo json_encode($dataResult, JSON_PRETTY_PRINT) . "\n\n";
