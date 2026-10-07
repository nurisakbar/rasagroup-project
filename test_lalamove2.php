<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\EkspedisiKuService::class);
$warehouse = \App\Models\Warehouse::find('019f94da-a6d0-712a-afc0-829d8fde49cd');
$address = \App\Models\Address::find('01a0f12b-d102-7358-ad2e-576a92696e42');

// We will test if we can modify EkspedisiKuService payload to not send service_type
// But first, let's just make the raw HTTP request to EkspedisiKu
$resolver = app(\App\Services\ShippingLocationResolver::class);
$pickup = $resolver->resolvePickup($warehouse);
$dropoff = $resolver->resolveDropoff($address);

$payload = [
    'pickup' => $pickup,
    'dropoff' => $dropoff,
    'weight' => 1000,
];
$baseUrl = config('services.ekspedisiku.base_url');
$token = config('services.ekspedisiku.token');

$response = Illuminate\Support\Facades\Http::withToken($token)
    ->acceptJson()
    ->post("{$baseUrl}/rates", $payload);

print_r($response->json());
