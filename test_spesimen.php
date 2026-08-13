<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$specimen = \App\Models\SpesimenLaboratorium::withTrashed()->where('nomor_spesimen', 'SP2026080984')->first();
if ($specimen) {
    echo "Found Specimen:\n";
    echo "ID: " . $specimen->id . "\n";
    echo "Nomor Antrian ID: " . $specimen->nomor_antrian_id . "\n";
    echo "Nomor Spesimen: " . $specimen->nomor_spesimen . "\n";
    echo "Trashed: " . ($specimen->trashed() ? "Yes" : "No") . "\n";
} else {
    echo "Specimen not found.\n";
}
