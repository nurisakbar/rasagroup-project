<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qadService = app(\App\Services\QadService::class);

echo "\nChecking customer ZH78584...\n";
$cust = $qadService->getCustomer('ZH78584', 'MCR-CUST');
echo json_encode($cust, JSON_PRETTY_PRINT) . "\n";
