<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cities = ['Tangerang', 'Jakarta Barat', 'Palembang', 'Semarang', 'Kota Semarang', 'Medan', 'Jakarta Selatan', 'Bekasi', 'Kabupaten Bekasi'];

foreach($cities as $c) {
    // try to find by exact name or like
    $q = \App\Models\RajaOngkirCity::where('name', 'LIKE', '%' . strtoupper($c) . '%')->first();
    echo $c . " => " . ($q ? $q->name . " (type: " . $q->type . ")" : 'NOT FOUND') . "\n";
}
