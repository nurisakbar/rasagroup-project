<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ekspedisi = app(\App\Services\EkspedisiKuService::class);
$result = $ekspedisi->calculateCost('3275030', '3275030', 501, 'jne');
print_r($result);
