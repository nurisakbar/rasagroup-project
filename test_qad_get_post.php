<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qad = app(App\Services\QadService::class);
$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'Bearer ' . $qad->getToken(),
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
])->post(config('qidapi.base_url') . '/api/transaction/sales-orders/get', ['SalesOrderCode' => 'W2609003']);
echo $response->status() . "\n" . $response->body() . "\n";
