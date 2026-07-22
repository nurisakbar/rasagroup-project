<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EkspedisiKuService;

$service = app(EkspedisiKuService::class);
$report = [];

function doTest($name, $closure) {
    global $report;
    try {
        $result = $closure();
        $report[$name] = [
            'status' => 'SUCCESS',
            'result' => $result
        ];
    } catch (\Exception $e) {
        $report[$name] = [
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
}

// 1. Test Couriers
doTest('GET_COURIERS', function() use ($service) {
    return $service->getCouriers();
});

// 2. Test Provinces
doTest('GET_PROVINCES', function() use ($service) {
    $res = $service->getProvinces();
    return [
        'count' => count($res['data'] ?? []),
        'sample' => array_slice($res['data'] ?? [], 0, 2)
    ];
});

// 3. Test Calculate Cost
doTest('CALCULATE_COST_REGULAR', function() use ($service) {
    // Origin and destination must be district IDs.
    // Use some arbitrary district ID from DB if possible, or 1 and 2.
    return $service->calculateCost(1, 2, 1000, 'lion_parcel');
});

// 4. Test Create Booking
doTest('CREATE_BOOKING', function() use ($service) {
    $payload = [
        'carrier' => 'lion_parcel',
        'shipment' => [
            'reference' => 'QA-TEST-' . time(),
            'package' => [
                'service_code' => 'REGPACK',
                'weight' => 1.5,
                'length' => 10,
                'width' => 10,
                'height' => 10,
                'item_value' => 50000,
                'description' => 'Test Item QA'
            ],
            'origin' => [
                'name' => 'Pengirim QA',
                'phone' => '081234567890',
                'address' => 'Jl. Asal No. 1',
                'district_id' => 1
            ],
            'destination' => [
                'name' => 'Penerima QA',
                'phone' => '089876543210',
                'address' => 'Jl. Tujuan No. 2',
                'district_id' => 2
            ]
        ]
    ];
    return [
        'payload' => $payload,
        'response' => $service->createBooking($payload)
    ];
});

// 5. Test Track
doTest('TRACK_SHIPMENT', function() use ($service) {
    return $service->track('RESI-QA-TEST-999', 'lion_parcel');
});

echo json_encode($report, JSON_PRETTY_PRINT);
