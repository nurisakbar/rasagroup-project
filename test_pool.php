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

$types = ['MOTORCYCLE', 'MPV'];

$responses = Illuminate\Support\Facades\Http::pool(fn (\Illuminate\Http\Client\Pool $pool) => collect($types)->map(fn ($type) => 
    $pool->as($type)->withToken($token)->acceptJson()->post("{$baseUrl}/rates", [
        'pickup' => $pickup,
        'dropoff' => $dropoff,
        'weight' => 1000,
        'service_type' => $type
    ])
));

foreach ($responses as $key => $response) {
    echo $key . " -> " . $response->status() . "\n";
}

