<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resolver = app(\App\Services\ShippingLocationResolver::class);
$warehouse = \App\Models\Warehouse::find('019f94da-a6d0-712a-afc0-829d8fde49cd');
$address = \App\Models\Address::find('01a0f12b-d102-7358-ad2e-576a92696e42');
$pickup = $resolver->resolvePickup($warehouse);
$dropoff = $resolver->resolveDropoff($address);

$baseUrl = config('services.ekspedisiku.base_url');
$token = config('services.ekspedisiku.token');

foreach (['CAR', 'MPV', 'VAN'] as $type) {
    $response = Illuminate\Support\Facades\Http::withToken($token)
        ->acceptJson()
        ->post("{$baseUrl}/rates", [
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'weight' => 1000,
            'service_type' => $type
        ]);
    
    $res = $response->json();
    $carrier = collect($res['carriers'] ?? [])->firstWhere('id', 'lalamove');
    echo $type . ': ' . ($carrier['status'] ?? 'null') . "\n";
    if (isset($carrier['services'])) {
        print_r($carrier['services']);
    }
}
