<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$snapService = new \App\Services\FaspaySnapService();
$b2bData = $snapService->getB2bToken();
print_r($b2bData);
