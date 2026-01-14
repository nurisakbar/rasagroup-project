<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            IndonesiaRegionsSeeder::class, // For SQLite fallback / MySQL status check
            SuperAdminSeeder::class,
            UserSeeder::class,
            BrandCategorySeeder::class, // Must be before ProductSeeder
            WarehouseSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
