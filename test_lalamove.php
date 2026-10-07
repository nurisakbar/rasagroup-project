<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\EkspedisiKuService::class);
$warehouse = \App\Models\Warehouse::find('019f94da-a645-7327-ad30-cc91723b1fb7');
$address = \App\Models\Address::find('019f952b-6b80-7365-8356-b85496bbcc92');

$result = $service->calculateCost($warehouse->district_id, $address->district_id, 1000, 'lalamove', [
    'warehouse' => $warehouse,
    'address' => $address,
]);

print_r($result);
