<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure RajaOngkir data for required provinces exists
        if (\Illuminate\Support\Facades\DB::table('raja_ongkir_provinces')->count() == 0) {
            $this->command->info('Fetching RajaOngkir provinces...');
            app(\App\Services\RajaOngkirService::class)->getProvinces();
        }
        
        $requiredProvinces = [10, 18, 5];
        foreach ($requiredProvinces as $provId) {
            if (\Illuminate\Support\Facades\DB::table('raja_ongkir_cities')->where('province_id', $provId)->count() == 0) {
                app(\App\Services\RajaOngkirService::class)->getCities($provId);
            }
        }

        $warehouses = [
            [
                'name' => 'Hub Jakarta Pusat',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'description' => 'Hub utama di Jakarta Pusat',
                'province_id' => 10, // DKI Jakarta (RajaOngkir)
                'regency_id' => 137, // Jakarta Pusat (RajaOngkir)
                'is_active' => true,
                'manager' => [
                    'name' => 'Manager Hub Jakarta',
                    'email' => 'hub.jakarta@example.com',
                    'phone' => '081234567896',
                ],
            ],
            [
                'name' => 'Hub Surabaya',
                'address' => 'Jl. Basuki Rahmat No. 45, Surabaya',
                'phone' => '031-87654321',
                'description' => 'Hub regional Jawa Timur',
                'province_id' => 18, // Jawa Timur (RajaOngkir)
                'regency_id' => 577, // Surabaya (RajaOngkir)
                'is_active' => true,
                'manager' => [
                    'name' => 'Manager Hub Surabaya',
                    'email' => 'hub.surabaya@example.com',
                    'phone' => '081234567897',
                ],
            ],
            [
                'name' => 'Hub Bandung',
                'address' => 'Jl. Asia Afrika No. 78, Bandung',
                'phone' => '022-11223344',
                'description' => 'Hub regional Jawa Barat',
                'province_id' => 5, // Jawa Barat (RajaOngkir)
                'regency_id' => 55, // Bandung (RajaOngkir)
                'is_active' => true,
                'manager' => [
                    'name' => 'Manager Hub Bandung',
                    'email' => 'hub.bandung@example.com',
                    'phone' => '081234567898',
                ],
            ],
        ];

        foreach ($warehouses as $data) {
            $existing = Warehouse::where('name', $data['name'])->first();
            if ($existing) {
                $this->command->info('Warehouse already exists: ' . $data['name']);
                continue;
            }

            // Create warehouse
            $warehouse = Warehouse::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'description' => $data['description'],
                'province_id' => $data['province_id'],
                'regency_id' => $data['regency_id'],
                'is_active' => $data['is_active'],
            ]);

            // Create manager user
            $manager = User::firstOrCreate(
                ['email' => $data['manager']['email']],
                [
                    'name' => $data['manager']['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['manager']['phone'],
                    'role' => User::ROLE_WAREHOUSE,
                    'warehouse_id' => $warehouse->id,
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info('Created: ' . $data['name'] . ' (ID: ' . $warehouse->id . ')');
            $this->command->info('  Manager: ' . $data['manager']['email'] . ' / password');
        }

        $this->command->info('');
        $this->command->info('Hub managers login URL: /warehouse/login');
    }
}
