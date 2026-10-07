<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the checkout getExpeditionServices endpoint logic roughly
$controller = app(\App\Http\Controllers\CheckoutController::class);
$expedition = \App\Models\Expedition::where('code', 'lalamove')->first();
$warehouse = \App\Models\Warehouse::find('019f94da-a6d0-712a-afc0-829d8fde49cd');
$address = \App\Models\Address::find('01a0f12b-d102-7358-ad2e-576a92696e42');

$method = new ReflectionMethod($controller, 'resolveShippingCost');
$method->setAccessible(true);
$result = $method->invoke($controller, $expedition, $warehouse, $address, 1000);

print_r($result);
