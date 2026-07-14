<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qadService = app(\App\Services\QadService::class);
$response = $qadService->getInventoryLocation([]);
echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
