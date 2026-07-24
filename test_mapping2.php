<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WilayahAdministratif;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = '/Applications/MAMP/htdocs/rasagroup/distributor_prod.xlsx';
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
array_shift($rows);

$regencies = WilayahAdministratif::select('regency_id', 'regency_name', 'province_id', 'province_name')
    ->groupBy('regency_id', 'regency_name', 'province_id', 'province_name')
    ->get();

$regencyLookup = [];
foreach ($regencies as $regency) {
    $name = strtoupper(trim($regency->regency_name));
    $name = str_replace(['KOTA ADM. ', 'KAB. ADM. ', 'KOTA ', 'KAB. ', 'KABUPATEN '], '', $name);
    $regencyLookup[$name] = $regency;
}
uksort($regencyLookup, function($a, $b) {
    return strlen($b) - strlen($a);
});

$stats = [
    'total' => 0,
    'full_match' => 0,
    'regency_district_match' => 0,
    'regency_only' => 0,
    'failed' => 0,
];

foreach ($rows as $row) {
    if (empty(trim($row[0]))) continue;
    
    $addressRaw = $row[1] ?? '';
    $addressLower = strtolower($addressRaw);
    $stats['total']++;
    
    $matchedRegency = null;
    foreach ($regencyLookup as $cleanName => $regency) {
        if (strpos($addressLower, strtolower($cleanName)) !== false) {
            $matchedRegency = $regency;
            break;
        }
    }
    
    if (!$matchedRegency) {
        $stats['failed']++;
        continue;
    }
    
    $districts = WilayahAdministratif::where('regency_id', $matchedRegency->regency_id)
        ->groupBy('district_id', 'district_name')
        ->select('district_id', 'district_name')
        ->get();
        
    $matchedDistrict = null;
    foreach ($districts as $district) {
        if (strpos($addressLower, strtolower(trim($district->district_name))) !== false) {
            $matchedDistrict = $district;
            break;
        }
    }
    
    if (!$matchedDistrict) {
        $stats['regency_only']++;
        continue;
    }
    
    $villages = WilayahAdministratif::where('district_id', $matchedDistrict->district_id)
        ->groupBy('village_id', 'village_name')
        ->select('village_id', 'village_name')
        ->get();
        
    $matchedVillage = null;
    foreach ($villages as $village) {
        if (strpos($addressLower, strtolower(trim($village->village_name))) !== false) {
            $matchedVillage = $village;
            break;
        }
    }
    
    if ($matchedVillage) {
        $stats['full_match']++;
    } else {
        $stats['regency_district_match']++;
    }
}

echo "=== IMPROVED MAPPING REPORT ===\n";
echo "Total Data: {$stats['total']}\n";
echo "Full Match (Prov+Kota+Kec+Kel): {$stats['full_match']}\n";
echo "Regency+District Match (Prov+Kota+Kec): {$stats['regency_district_match']}\n";
echo "Regency Only Match (Prov+Kota): {$stats['regency_only']}\n";
echo "FAILED (No Match): {$stats['failed']}\n";
