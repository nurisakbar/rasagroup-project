<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$api = app(\App\Services\QidApiService::class);

// Fetch locations
$locationsRes = $api->get('/api/master/inventory/location');
if (($locationsRes['error']['isError'] ?? false) || !isset($locationsRes['data'])) {
    echo "Failed to fetch locations\n";
    print_r($locationsRes);
    exit;
}

$allInventories = [];
$locations = $locationsRes['data'];
echo "Found " . count($locations) . " locations.\n";

foreach ($locations as $loc) {
    $locCode = $loc['location'] ?? $loc['locationCode'] ?? null;
    if (!$locCode) continue;
    
    echo "Fetching inventory for location: {$locCode}\n";
    $invRes = $api->post('/api/master/inventory/all', ['Location' => $locCode], true);
    
    if (($invRes['error']['isError'] ?? false)) {
        echo "Error for {$locCode}: " . implode(', ', $invRes['error']['errorMessages'] ?? []) . "\n";
    } else {
        $invData = $invRes['data'] ?? [];
        echo " - Found " . count($invData) . " items.\n";
        foreach ($invData as $item) {
            $item['_locationCode'] = $locCode; // Inject location code for reference
            $allInventories[] = $item;
        }
    }
}

file_put_contents('storage/app/public/qad_inventory_all.json', json_encode($allInventories, JSON_PRETTY_PRINT));
echo "Total inventory items saved: " . count($allInventories) . " to storage/app/public/qad_inventory_all.json\n";
