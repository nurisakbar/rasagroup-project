<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Address;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\WarehouseStock;

class ImportDistributorsMouCommand extends Command
{
    protected $signature = 'import:distributors-mou 
                            {--file= : Path to the CSV file (defaults to Rekap_Status_Draft_MOU_Distributor)} 
                            {--dry-run : Show mapping result without saving to database}';

    protected $description = 'Import 45 distributors from Draft MOU CSV to database (User, Warehouse, Address, PriceLevel)';

    public function handle()
    {
        $filePath = $this->option('file') ?: base_path('../Rekap_Status_Draft_MOU_Distributor(Rekap Draft MOU Distributor).csv');
        
        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info("=========================================");
            $this->info(" DRY RUN MODE (NO DATABASE CHANGES)      ");
            $this->info("=========================================");
        }

        $fp = fopen($filePath, 'r');
        $rows = [];
        while (($data = fgetcsv($fp)) !== false) {
            $rows[] = $data;
        }
        fclose($fp);

        // Load master data for smart location resolver
        $wilayahCache = DB::table('view_wilayah_administratif_indonesia_cache')
            ->select('province_id', 'province_name', 'regency_id', 'regency_name', 'district_id', 'district_name', 'village_id', 'village_name')
            ->get();
            
        $regencies = $wilayahCache->unique('regency_id')->values();

        $tableData = [];
        $totalImported = 0;
        $totalRegencyMatch = 0;
        $totalDistrictMatch = 0;
        $totalVillageMatch = 0;

        DB::beginTransaction();

        try {
            // Data starts at row 5 (index 4)
            for ($i = 4; $i < count($rows); $i++) {
                $r = $rows[$i];
                if (empty($r[1]) || str_contains($r[1], 'Total Distributor') || str_contains($r[1], 'Draft') || str_contains($r[1], 'RINGKASAN')) continue;
                
                $dnName = trim($r[1]);
                $slug = Str::slug($dnName);
                $rawAddress = trim(str_replace(["\r", "\n"], ' ', $r[2] ?? ''));
                $topStr = trim($r[5] ?? '');
                $discStr = trim($r[6] ?? '');

                // Clean address
                $cleanAddress = $rawAddress;
                if ($cleanAddress === '#N/A' || empty($cleanAddress)) {
                    $cleanAddress = 'Alamat belum tersedia (dari Draft MOU)';
                }

                // Parse TOP
                $top = 0;
                if (is_numeric($topStr)) {
                    $top = (int)$topStr;
                }

                // Parse Disc
                $disc = 0.00;
                if (!empty($discStr)) {
                    $disc = (float)str_replace(['%', ','], ['', '.'], $discStr);
                }

                // Location Resolution
                $provinceId = null;
                $regencyId = null;
                $districtId = null;
                $villageId = null;
                $matchedLevel = 'NONE';

                if ($rawAddress !== '#N/A' && !empty($rawAddress)) {
                    // 1. Find Regency
                    $foundReg = null;
                    foreach ($regencies as $reg) {
                        $cleanReg = trim(str_ireplace(['KABUPATEN ', 'KAB. ', 'KAB ', 'KOTA ADM. ', 'KOTA ', 'ADM. '], '', $reg->regency_name));
                        if (strlen($cleanReg) >= 3 && (stripos($rawAddress, $cleanReg) !== false || stripos($dnName, $cleanReg) !== false)) {
                            $foundReg = $reg;
                            break;
                        }
                    }
                    if (!$foundReg) {
                        if (stripos($rawAddress, 'Ujung Pandang') !== false || stripos($dnName, 'MAKASSAR') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'MAKASSAR') !== false);
                        } elseif (stripos($dnName, 'SBY') !== false || stripos($rawAddress, 'Tandes') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'SURABAYA') !== false);
                        } elseif (stripos($rawAddress, 'Ketapang Sampit') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'KOTAWARINGIN TIMUR') !== false);
                        } elseif (stripos($rawAddress, 'Tegalluar') !== false || stripos($dnName, 'BANDUNG') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'BANDUNG') !== false);
                        } elseif (stripos($rawAddress, 'Tanjung Pandan') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'BELITUNG') !== false);
                        } elseif (stripos($rawAddress, 'Pamekasan') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'PAMEKASAN') !== false);
                        } elseif (stripos($rawAddress, 'Kedoya') !== false || stripos($rawAddress, 'Cengkareng') !== false || stripos($rawAddress, 'Taman Palem') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'JAKARTA BARAT') !== false);
                        } elseif (stripos($rawAddress, 'Golf Island') !== false || stripos($rawAddress, 'PIK') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'JAKARTA UTARA') !== false);
                        } elseif (stripos($rawAddress, 'Cipinang') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'JAKARTA TIMUR') !== false);
                        } elseif (stripos($rawAddress, 'Bojong Koneng') !== false || stripos($rawAddress, 'Babakan Madang') !== false) {
                            $foundReg = $regencies->first(fn($r) => stripos($r->regency_name, 'BOGOR') !== false);
                        }
                    }

                    if ($foundReg) {
                        $provinceId = $foundReg->province_id;
                        $regencyId = $foundReg->regency_id;
                        $matchedLevel = 'Regency';
                        $totalRegencyMatch++;

                        // 2. Find District
                        $districts = $wilayahCache->where('regency_id', $regencyId)->unique('district_id');
                        $foundDist = null;
                        foreach ($districts as $dist) {
                            if (strlen($dist->district_name) >= 3 && stripos($rawAddress, $dist->district_name) !== false) {
                                $foundDist = $dist;
                                break;
                            }
                        }

                        if ($foundDist) {
                            $districtId = $foundDist->district_id;
                            $matchedLevel = 'District';
                            $totalDistrictMatch++;

                            // 3. Find Village (hanya di district tsb agar tidak ambigu)
                            $villages = $wilayahCache->where('district_id', $districtId)->unique('village_id');
                            $foundVil = null;
                            foreach ($villages as $vil) {
                                if (strlen($vil->village_name) >= 3 && stripos($rawAddress, $vil->village_name) !== false) {
                                    $foundVil = $vil;
                                    break;
                                }
                            }
                            if ($foundVil) {
                                $villageId = $foundVil->village_id;
                                $matchedLevel = 'Village';
                                $totalVillageMatch++;
                            }
                        }
                    }
                }

                if (!$isDryRun) {
                    // Create PriceLevel if doesn't exist
                    $priceLevelId = null;
                    if ($disc > 0) {
                        $priceLevel = PriceLevel::firstOrCreate(
                            ['discount_percentage' => $disc],
                            [
                                'id' => (string) Str::uuid(),
                                'name' => "Diskon Distributor " . rtrim(rtrim(number_format($disc, 2, ',', ''), '0'), ',') . "%",
                                'is_active' => true,
                            ]
                        );
                        $priceLevelId = $priceLevel->id;
                    }

                    // Create Warehouse
                    $warehouse = Warehouse::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'id' => (string) Str::uuid(),
                            'name' => $dnName,
                            'address' => $cleanAddress,
                            'province_id' => $provinceId,
                            'regency_id' => $regencyId,
                            'district_id' => $districtId,
                            'village_id' => $villageId,
                            'is_active' => true,
                        ]
                    );

                    // Sync Products
                    $this->syncProductsToWarehouse($warehouse);

                    // Create User
                    $email = $slug . '@distributor.rasagroup.id';
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'id' => (string) Str::uuid(),
                            'name' => $dnName,
                            'password' => Hash::make('RasaDistributor2026!'),
                            'role' => User::ROLE_DISTRIBUTOR,
                            'warehouse_id' => $warehouse->id,
                            'price_level_id' => $priceLevelId,
                            'term_of_payment' => $top,
                            'distributor_status' => 'approved',
                            'distributor_approved_at' => now(),
                            'distributor_province_id' => $provinceId,
                            'distributor_regency_id' => $regencyId,
                            'distributor_address' => $cleanAddress,
                            'wa_verified_at' => now(),
                        ]
                    );

                    // Create Address
                    if ($user->wasRecentlyCreated) {
                        Address::create([
                            'id' => (string) Str::uuid(),
                            'user_id' => $user->id,
                            'label' => 'Utama',
                            'recipient_name' => $dnName,
                            'phone' => '-',
                            'address_detail' => $cleanAddress,
                            'province_id' => $provinceId,
                            'regency_id' => $regencyId,
                            'district_id' => $districtId,
                            'village_id' => $villageId,
                            'is_default' => true,
                        ]);
                    }
                }

                $tableData[] = [
                    'Name' => Str::limit($dnName, 20),
                    'Email' => $slug . '@distrib...',
                    'Matched' => $matchedLevel,
                    'TOP' => $top,
                    'Disc' => $disc . '%',
                    'Address' => Str::limit($cleanAddress, 25),
                ];
                $totalImported++;
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info("Import completed successfully! ({$totalImported} distributors inserted/updated)");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }

        $this->table(
            ['Name', 'Email', 'Location Match Level', 'TOP (Days)', 'Disc', 'Address Preview'],
            $tableData
        );
        
        $this->info("\nStats:");
        $this->line("- Total Processed: " . $totalImported);
        $this->line("- Matched Regency: {$totalRegencyMatch} (" . round($totalRegencyMatch/$totalImported*100, 1) . "%)");
        $this->line("- Matched District: {$totalDistrictMatch} (" . round($totalDistrictMatch/$totalImported*100, 1) . "%)");
        $this->line("- Matched Village: {$totalVillageMatch} (" . round($totalVillageMatch/$totalImported*100, 1) . "%)");

        return 0;
    }

    private function syncProductsToWarehouse(Warehouse $warehouse)
    {
        $products = Product::where('status', 'active')->get();
        $existingProductIds = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->pluck('product_id')
            ->toArray();
        
        foreach ($products as $product) {
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }
            WarehouseStock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'stock' => 0,
            ]);
        }
    }
}
