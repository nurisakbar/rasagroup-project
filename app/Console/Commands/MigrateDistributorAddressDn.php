<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Address;
use App\Models\WilayahAdministratif;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MigrateDistributorAddressDn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:distributor-address-dn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate distributor DN addresses from Excel file to database';

    protected $mapping = [
        'PT Otten Kopi Indonesia (JKT)' => 'OTTEN',
        'PT Otten Kopi Indonesia (MDN)' => 'OTTEN',
        'Nostalgie CV.' => 'NOSTALGIE',
        'CV BERKAT LESTARI ABADI' => 'BERKAT LESTARI',
        'CV. Polimer Jaya Mandiri' => 'POLIMER MAKASSAR',
        'PT Megah Mitra Abadi' => 'MEGAH MITRA ABADI',
        'PT Usaha Baru Lestari' => 'USAHA BARU LESTARI',
        'Masuya Graha Trikencana PT. (KDR)' => 'MASUYA KEDIRI',
        'Ronny Haryono/CV Agro Madura Sejahtera' => 'AGRO MADURA',
        'PT Distribusindo Bintang Mandiri' => 'PT Distribundo Bintang Mandiri',
        'PT Sila Bali Dwipa' => 'SILA BALI DWIPA',
        'PT Jerindo Jaya Abadi (MLG)' => 'JERINDO JATIM',
        'PT Jerindo Jaya Abadi (PBG)' => 'JERINDO JATIM',
        'PT Karya Unggul Abdi Swadesi' => 'KARYA UNGGUL',
        'PT Jerindo Jaya Abadi (SDJ)' => 'JERINDO JATIM',
        'PT Sarana Rasa Sejahtera' => 'PT Sarana Rasa Sejahtera',
        'Rumah Hook' => 'RUMAH HOOK',
        'CV Aman Jaya Anugrah' => 'AMAN JAYA',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('../temp/DN Address CIS Pak Taufik.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return;
        }

        $this->info("Reading excel file...");

        $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray, \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function array(array $array) {}
        }, $filePath);

        // Target specific sheet (Alamat Gudang DN) or assume it's the second sheet (index 1)
        // Check which sheet has the expected headers
        $sheetData = [];
        foreach ($data as $sheet) {
            if (!empty($sheet) && isset($sheet[0]['distributor'])) {
                $sheetData = $sheet;
                break;
            }
        }

        if (empty($sheetData)) {
            $this->error("Could not find a sheet with 'distributor' column.");
            return;
        }

        $this->info("Found " . count($sheetData) . " rows to process.");
        
        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;
        $missingDistributor = [];

        foreach ($sheetData as $row) {
            $excelName = trim($row['distributor'] ?? '');
            if (empty($excelName)) continue;

            $shipToCode = trim($row['ship_to_code'] ?? '');
            $dnCode = trim($row['dn_code'] ?? '');
            $alamat = trim($row['alamat'] ?? '');
            $kota = trim($row['kota'] ?? '');
            
            // Resolve User
            $mappedName = $this->mapping[$excelName] ?? $excelName;
            
            $user = User::where('role', 'distributor')
                ->where('name', 'like', "%{$mappedName}%")
                ->first();
                
            if (!$user) {
                // Try fuzzy matching
                $user = User::where('role', 'distributor')
                    ->whereRaw("LOWER(?) LIKE CONCAT('%', LOWER(name), '%')", [$excelName])
                    ->first();
            }

            if (!$user) {
                if (!in_array($excelName, $missingDistributor)) {
                    $missingDistributor[] = $excelName;
                }
                $failCount++;
                continue;
            }

            // Resolve Regency & Province
            $regencyId = null;
            $provinceId = null;

            if ($kota) {
                // Clean KOTA string
                $cleanKota = str_ireplace(['kota ', 'kabupaten ', 'kab. '], '', $kota);
                $cleanKota = trim($cleanKota);
                
                $wilayah = WilayahAdministratif::where('regency_name', 'like', "%{$cleanKota}%")->first();
                if ($wilayah) {
                    $regencyId = $wilayah->regency_id;
                    $provinceId = $wilayah->province_id;
                } else {
                    $this->warn("KOTA not found for: {$kota}");
                }
            }

            // Check if address already exists based on ship to code or dn code
            $existing = Address::where('user_id', $user->id)
                ->where('notes', 'like', "%SHIP_TO_CODE: {$shipToCode}%")
                ->first();

            if ($existing) {
                $skippedCount++;
                continue;
            }

            $label = "Gudang DN" . ($dnCode ? " - {$dnCode}" : "");

            Address::create([
                'user_id' => $user->id,
                'label' => $label,
                'store_name' => $excelName,
                'recipient_name' => $excelName,
                'address_detail' => $alamat,
                'regency_id' => $regencyId,
                'province_id' => $provinceId,
                'notes' => "SHIP_TO_CODE: {$shipToCode} | DN_CODE: {$dnCode}",
                'is_default' => false,
                'phone' => $user->phone ?? '-', // required field check
            ]);

            $successCount++;
        }

        $this->info("Migration completed.");
        $this->info("Success: {$successCount}");
        $this->info("Skipped (already exists): {$skippedCount}");
        $this->info("Failed (distributor not found): {$failCount}");
        
        if (!empty($missingDistributor)) {
            $this->warn("The following distributors from excel were not found in database:");
            foreach ($missingDistributor as $missing) {
                $this->line("- " . $missing);
            }
        }
    }
}
