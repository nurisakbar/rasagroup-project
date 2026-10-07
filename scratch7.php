<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jubelio = app(App\Services\JubelioService::class);
$token = $jubelio->token();

$response = \Illuminate\Support\Facades\Http::withToken($token)
    ->withHeaders(['Content-Type' => 'application/json'])
    ->post('https://api.jubelio.com/shipment/get-rates', [
    "origin" => [
        "area_id" => "3275061002",
        "zipcode" => "17132"
    ],
    "destination" => [
        "area_id" => "1708042010",
        "zipcode" => "39372"
    ],
    "weight" => 250,
    "service_category_id" => 1,
    "total_value" => 120000,
    "items" => []
]);
echo "api.jubelio.com: " . $response->status() . "\n";
