<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndonesiaRegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data is imported via migration for MySQL.
     * This seeder only creates the view.
     */
    public function run(): void
    {
        $driver = DB::connection()->getDriverName();
        
        // For MySQL, data is already imported via migration
        // Just create the view
        if ($driver === 'mysql') {
            $this->command->info('MySQL detected - regions data imported via migration.');
            $this->command->info('Provinces: ' . DB::table('provinces')->count());
            $this->command->info('Regencies: ' . DB::table('regencies')->count());
            $this->command->info('Districts: ' . DB::table('districts')->count());
            $this->command->info('Villages: ' . DB::table('villages')->count());
        }
        
        // Create view
        DB::statement("DROP VIEW IF EXISTS view_wilayah_administratif_indonesia");
        DB::statement("CREATE VIEW view_wilayah_administratif_indonesia AS 
            SELECT 
                villages.id as village_id,
                villages.name as village_name,
                districts.id as district_id,
                districts.name as district_name,
                regencies.id as regency_id,
                regencies.name as regency_name,
                provinces.id as province_id,
                provinces.name as province_name
            FROM villages
            LEFT JOIN districts ON districts.id = villages.district_id
            LEFT JOIN regencies ON regencies.id = districts.regency_id
            LEFT JOIN provinces ON provinces.id = regencies.province_id");
            
        $this->command->info('View view_wilayah_administratif_indonesia created successfully.');
    }
}
