<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MigrateDistributorsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:distributors {file : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate distributor data from Excel file to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return Command::FAILURE;
        }

        $this->info("Membaca file Excel...");
        
        // Load all sheets as an array of arrays using anonymous class
        $sheets = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray, \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function array(array $array) {}
        }, $file);

        $masterDnSheet = null;
        $alamatDnSheet = null;
        $shipToSheet = null;

        foreach ($sheets as $index => $rows) {
            if (empty($rows)) continue;
            
            $firstRow = $rows[0];
            // Detect sheet by known columns
            if (array_key_exists('kode_dn', $firstRow) || array_key_exists('cust_code', $firstRow)) {
                $masterDnSheet = $rows;
            } elseif (array_key_exists('distributor', $firstRow) && array_key_exists('no_whatsapp', $firstRow)) {
                $alamatDnSheet = $rows;
            } elseif (array_key_exists('ship_to_address', $firstRow) || array_key_exists('full_address', $firstRow)) {
                $shipToSheet = $rows;
            }
        }

        if (!$masterDnSheet) {
            $this->error("Tidak dapat menemukan sheet 'master DN' yang berisi 'cust_code'.");
            return Command::FAILURE;
        }

        $this->info("Ditemukan " . count($masterDnSheet) . " baris di Master DN.");

        // Build a lookup for Alamat DN (for WA and Email)
        $alamatLookup = [];
        if ($alamatDnSheet) {
            foreach ($alamatDnSheet as $row) {
                if (!empty($row['distributor'])) {
                    $alamatLookup[trim(strtolower($row['distributor']))] = [
                        'phone' => $row['no_whatsapp'] ?? null,
                        'email' => $row['email'] ?? null,
                    ];
                }
            }
        }

        DB::beginTransaction();
        try {
            // FASE 1: Master DN -> Create/Update User & Warehouse
            $this->info("Memproses Fase 1: Master Distributor...");
            $processedCustCodes = [];
            foreach ($masterDnSheet as $row) {
                $custCode = $row['cust_code'] ?? null;
                if (!$custCode) continue;

                if (in_array($custCode, $processedCustCodes)) {
                    continue; // Process each custCode once for User creation
                }

                $name = $row['nama_distributor'] ?? $row['nama_qad'] ?? "Distributor {$custCode}";
                $email = $row['email_distributor'] ?? null;
                $phone = null;

                // Try lookup from Alamat DN sheet
                $lookupKey = trim(strtolower($name));
                if (isset($alamatLookup[$lookupKey])) {
                    if (empty($email) && !empty($alamatLookup[$lookupKey]['email'])) {
                        $email = $alamatLookup[$lookupKey]['email'];
                    }
                    $phone = $alamatLookup[$lookupKey]['phone'];
                }
                
                // fallback email
                if (empty($email)) {
                    $email = strtolower($custCode) . '@qad-dummy.com';
                }

                // Cleanup email if multiple (e.g., contains \n)
                if (str_contains($email, "\n")) {
                    $email = explode("\n", $email)[0];
                }

                // Search user
                $user = User::where('qad_customer_code', $custCode)->first();
                if (!$user) {
                    $user = User::where('email', $email)->first();
                    if ($user) {
                        $user->qad_customer_code = $custCode;
                        $user->save();
                    }
                }

                $addressRaw = $row['alamat'] ?? null;
                $cityRaw = $row['dn_kota'] ?? null;
                $provinceRaw = $row['province'] ?? null;

                // Region mapping using ekspedisi_dev
                $regencyId = null;
                $provinceId = null;
                $districtId = null;
                $villageId = null;
                
                if ($cityRaw) {
                    $regency = DB::table('ekspedisi_dev.view_wilayah_administratif_indonesia_cache')
                        ->where('regency_name', 'LIKE', '%' . strtoupper(trim($cityRaw)) . '%')
                        ->first();
                    if ($regency) {
                        $regencyId = $regency->regency_id;
                        $provinceId = $regency->province_id;
                        
                        // Extract district and village from address string
                        if ($addressRaw) {
                            $extracted = $this->extractDistrictAndVillage($regencyId, $addressRaw);
                            $districtId = $extracted['district_id'];
                            $villageId = $extracted['village_id'];
                        }
                    }
                }

                if ($user) {
                    $warehouse = $user->warehouse;
                    if (!$warehouse) {
                        $warehouse = Warehouse::create([
                            'id' => (string) Str::uuid(),
                            'name' => 'Gudang ' . $name,
                            'address' => $addressRaw,
                            'phone' => $phone,
                            'regency_id' => $regencyId,
                            'province_id' => $provinceId,
                            'district_id' => $districtId,
                            'village_id' => $villageId,
                            'is_active' => true,
                        ]);
                        $user->warehouse_id = $warehouse->id;
                    } else {
                        $warehouse->update([
                            'name' => 'Gudang ' . $name,
                            'address' => $addressRaw ?? $warehouse->address,
                            'phone' => $phone ?? $warehouse->phone,
                            'regency_id' => $regencyId ?? $warehouse->regency_id,
                            'province_id' => $provinceId ?? $warehouse->province_id,
                            'district_id' => $districtId ?? $warehouse->district_id,
                            'village_id' => $villageId ?? $warehouse->village_id,
                        ]);
                    }

                    $user->update([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone ?? $user->phone,
                        'role' => User::ROLE_DISTRIBUTOR,
                        'distributor_status' => 'approved',
                    ]);
                } else {
                    $warehouse = Warehouse::create([
                        'id' => (string) Str::uuid(),
                        'name' => 'Gudang ' . $name,
                        'address' => $addressRaw,
                        'phone' => $phone,
                        'regency_id' => $regencyId,
                        'province_id' => $provinceId,
                        'district_id' => $districtId,
                        'village_id' => $villageId,
                        'is_active' => true,
                    ]);

                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make('Rasagroup2025!'),
                        'role' => User::ROLE_DISTRIBUTOR,
                        'warehouse_id' => $warehouse->id,
                        'distributor_status' => 'approved',
                        'distributor_approved_at' => now(),
                        'qad_customer_code' => $custCode,
                    ]);
                }

                $processedCustCodes[] = $custCode;
            }
            $this->info("Fase 1 selesai. Total user diproses: " . count($processedCustCodes));

            // FASE 2: SHIP TO ADDRESS
            if ($shipToSheet) {
                $this->info("Memproses Fase 2: Alamat Ship To...");
                $addressCount = 0;
                foreach ($shipToSheet as $row) {
                    $custCode = $row['customer'] ?? null;
                    if (!$custCode) continue;

                    $user = User::where('qad_customer_code', $custCode)->first();
                    if (!$user) {
                        continue;
                    }

                    $shipToAddressCode = $row['ship_to_address'] ?? null;
                    $addressLabel = $row['name'] ?? null;
                    $fullAddress = $row['full_address'] ?? null;
                    $cityRaw = $row['city'] ?? null;
                    $postalCode = $row['postal_code'] ?? null;
                    
                    // Region mapping using ekspedisi_dev
                    $regencyId = null;
                    $provinceId = null;
                    $districtId = null;
                    $villageId = null;

                    if ($cityRaw) {
                        $regency = DB::table('ekspedisi_dev.view_wilayah_administratif_indonesia_cache')
                            ->where('regency_name', 'LIKE', '%' . strtoupper(trim($cityRaw)) . '%')
                            ->first();
                        if ($regency) {
                            $regencyId = $regency->regency_id;
                            $provinceId = $regency->province_id;
                            
                            if ($fullAddress) {
                                $extracted = $this->extractDistrictAndVillage($regencyId, $fullAddress);
                                $districtId = $extracted['district_id'];
                                $villageId = $extracted['village_id'];
                            }
                        }
                    }

                    // Check if address already exists by label (Ship To Address code)
                    $existingAddress = Address::where('user_id', $user->id)
                        ->where('label', $shipToAddressCode)
                        ->first();

                    if (!$existingAddress) {
                        Address::create([
                            'id' => (string) Str::uuid(),
                            'user_id' => $user->id,
                            'label' => $shipToAddressCode, // Store Ship To Code in label
                            'store_name' => $addressLabel,
                            'recipient_name' => $user->name,
                            'phone' => $user->phone ?: '-',
                            'address_detail' => $fullAddress,
                            'postal_code' => $postalCode,
                            'regency_id' => $regencyId,
                            'province_id' => $provinceId,
                            'district_id' => $districtId,
                            'village_id' => $villageId,
                            'is_default' => $addressCount == 0, // First address as default
                        ]);
                        $addressCount++;
                    } else {
                        $existingAddress->update([
                            'store_name' => $addressLabel,
                            'address_detail' => $fullAddress,
                            'postal_code' => $postalCode,
                            'regency_id' => $regencyId,
                            'province_id' => $provinceId,
                            'district_id' => $districtId,
                            'village_id' => $villageId,
                        ]);
                    }
                }
                $this->info("Fase 2 selesai. Total alamat diproses/ditambahkan: " . $addressCount);
            }

            DB::commit();
            $this->info("Migrasi data distributor berhasil!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage() . " di baris " . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Cari district dan village dari string alamat
     */
    private function extractDistrictAndVillage($regencyId, $addressText)
    {
        $result = [
            'district_id' => null,
            'village_id' => null
        ];
        
        $addressLower = strtolower($addressText);
        
        // Ambil semua district untuk regency ini
        $districts = DB::table('ekspedisi_dev.view_wilayah_administratif_indonesia_cache')
            ->where('regency_id', $regencyId)
            ->groupBy('district_id', 'district_name')
            ->select('district_id', 'district_name')
            ->get();
            
        foreach ($districts as $district) {
            $districtName = strtolower($district->district_name);
            if (strpos($addressLower, $districtName) !== false) {
                $result['district_id'] = $district->district_id;
                
                // Jika kecamatan ketemu, cari desanya di kecamatan tersebut
                $villages = DB::table('ekspedisi_dev.view_wilayah_administratif_indonesia_cache')
                    ->where('district_id', $district->district_id)
                    ->select('village_id', 'village_name')
                    ->get();
                    
                foreach ($villages as $village) {
                    $villageName = strtolower($village->village_name);
                    if (strpos($addressLower, $villageName) !== false) {
                        $result['village_id'] = $village->village_id;
                        break;
                    }
                }
                break; // Stop at first matched district
            }
        }
        
        return $result;
    }
}
