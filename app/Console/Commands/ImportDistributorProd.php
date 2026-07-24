<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WilayahAdministratif;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDistributorProd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:distributor-prod {file=distributor_prod.xlsx : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import distributor data from distributor_prod.xlsx and map addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        // If file doesn't exist, try looking in base_path
        if (!file_exists($file)) {
            $baseFile = base_path('../' . $file);
            if (file_exists($baseFile)) {
                $file = $baseFile;
            } else {
                $this->error("File tidak ditemukan: {$file}");
                return Command::FAILURE;
            }
        }

        $this->info("Membaca file Excel: {$file}");
        
        try {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            $this->error("Gagal membaca Excel: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Remove header
        array_shift($rows);

        $this->info("Menyiapkan lookup wilayah...");
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
            'success' => 0,
            'failed_map' => 0,
            'errors' => 0,
        ];

        DB::beginTransaction();
        try {
            $unmappedLog = [];

            foreach ($rows as $index => $row) {
                if (empty(trim($row[0]))) continue;
                
                $stats['total']++;
                
                $distributorName = trim($row[0]);
                $addressRaw = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $email = trim($row[3] ?? '');
                
                if (empty($email)) {
                    $email = strtolower(Str::slug($distributorName)) . '@dummy.com';
                }

                $addressLower = strtolower($addressRaw);
                
                $provinceId = null;
                $regencyId = null;
                $districtId = null;
                $villageId = null;

                // 1. Match Regency
                $matchedRegency = null;
                foreach ($regencyLookup as $cleanName => $regency) {
                    if (strpos($addressLower, strtolower($cleanName)) !== false) {
                        $matchedRegency = $regency;
                        break;
                    }
                }
                
                if ($matchedRegency) {
                    $provinceId = $matchedRegency->province_id;
                    $regencyId = $matchedRegency->regency_id;
                    
                    // 2. Match District
                    $districts = WilayahAdministratif::where('regency_id', $regencyId)
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
                    
                    if ($matchedDistrict) {
                        $districtId = $matchedDistrict->district_id;
                        
                        // 3. Match Village
                        $villages = WilayahAdministratif::where('district_id', $districtId)
                            ->groupBy('village_id', 'village_name')
                            ->select('village_id', 'village_name')
                            ->get();
                            
                        foreach ($villages as $village) {
                            if (strpos($addressLower, strtolower(trim($village->village_name))) !== false) {
                                $villageId = $village->village_id;
                                break;
                            }
                        }
                    }
                } else {
                    $stats['failed_map']++;
                    $unmappedLog[] = "Baris " . ($index + 2) . " | $distributorName | $addressRaw";
                }

                // Create or Update User
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::where('name', $distributorName)->first();
                }

                if ($user) {
                    $user->update([
                        'name' => $distributorName,
                        'phone' => $phone ?: $user->phone,
                        'role' => User::ROLE_DISTRIBUTOR,
                        'distributor_status' => 'approved',
                    ]);
                } else {
                    $user = User::create([
                        'name' => $distributorName,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make('Rasagroup2025!'),
                        'role' => User::ROLE_DISTRIBUTOR,
                        'distributor_status' => 'approved',
                        'distributor_approved_at' => now(),
                    ]);
                }

                // Create or Update Warehouse
                $warehouseName = 'Gudang ' . $distributorName;
                $warehouse = $user->warehouse;
                
                if ($warehouse) {
                    $warehouse->update([
                        'name' => $warehouseName,
                        'address' => $addressRaw,
                        'phone' => $phone ?: $warehouse->phone,
                        'province_id' => $provinceId,
                        'regency_id' => $regencyId,
                        'district_id' => $districtId,
                        'village_id' => $villageId,
                    ]);
                } else {
                    $warehouse = Warehouse::create([
                        'id' => (string) Str::uuid(),
                        'name' => $warehouseName,
                        'address' => $addressRaw,
                        'phone' => $phone,
                        'province_id' => $provinceId,
                        'regency_id' => $regencyId,
                        'district_id' => $districtId,
                        'village_id' => $villageId,
                        'is_active' => true,
                    ]);
                    
                    $user->warehouse_id = $warehouse->id;
                    $user->save();
                }
                
                $stats['success']++;
            }

            DB::commit();
            $this->info("Import selesai!");
            
            if (count($unmappedLog) > 0) {
                $logPath = storage_path('logs/unmapped_addresses.log');
                file_put_contents($logPath, implode("\n", $unmappedLog));
                $this->warn("Terdapat {$stats['failed_map']} alamat yang gagal dipetakan (ID Wilayah = null). Log tersimpan di: $logPath");
            }
            
            $this->table(['Statistik', 'Jumlah'], [
                ['Total Data', $stats['total']],
                ['Berhasil Import User/Warehouse', $stats['success']],
                ['Alamat Gagal Dipetakan', $stats['failed_map']],
                ['Error/Gagal Import', $stats['errors']],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan sistem: " . $e->getMessage() . " di baris " . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
