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

$failedExamples = [];
$successExamples = [];

foreach ($rows as $row) {
    if (empty(trim($row[0]))) continue;
    
    $distributor = $row[0];
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
        if (count($failedExamples) < 10) $failedExamples[] = "* **$distributor**: $addressRaw";
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
        if (count($successExamples) < 5) $successExamples[] = "* **$distributor**: $addressRaw\n  *Terdeteksi:* {$matchedRegency->province_name} -> {$matchedRegency->regency_name} -> {$matchedDistrict->district_name} -> {$matchedVillage->village_name}";
    } else {
        $stats['regency_district_match']++;
    }
}

$md = "# Laporan Hasil Mapping Alamat (Preview)\n\n";
$md .= "Dari algoritma matching terbaru (dengan pembersihan nama KOTA/KABUPATEN), berikut adalah tingkat keberhasilannya:\n\n";
$md .= "- **Total Data**: {$stats['total']}\n";
$md .= "- **Berhasil Penuh (Prov+Kota+Kec+Kel)**: {$stats['full_match']}\n";
$md .= "- **Berhasil Sebagian (Prov+Kota+Kec)**: {$stats['regency_district_match']}\n";
$md .= "- **Berhasil Sebagian (Prov+Kota)**: {$stats['regency_only']}\n";
$md .= "- **Gagal Mapping Sama Sekali**: {$stats['failed']}\n\n";

$successRate = round((($stats['total'] - $stats['failed']) / $stats['total']) * 100, 1);
$md .= "> [!TIP]\n> **Tingkat Keberhasilan Deteksi (Minimal Kota): $successRate%**\n\n";

$md .= "## Contoh Alamat yang Gagal Dideteksi\n";
$md .= "Alamat-alamat ini tidak mengandung nama Kota/Kabupaten yang persis sama dengan database Kemendagri.\n\n";
foreach ($failedExamples as $ex) {
    $md .= "$ex\n";
}

$md .= "\n## Contoh Alamat yang Berhasil Penuh\n\n";
foreach ($successExamples as $ex) {
    $md .= "$ex\n";
}

file_put_contents('/tmp/mapping_report.md', $md);
