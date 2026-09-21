<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\FaspaySnapService('rdi');
$order = \App\Models\Order::first();
$order->order_number = 'WS260905004-FRESH5';
$response = $service->generateQris($order, 10000);
echo "RESPONSE STATUS: " . ($response ? 'SUCCESS' : 'NULL') . "\n";
