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
            IndonesiaRegionsSeeder::class,
            SuperAdminSeeder::class,
            UserSeeder::class,
        ]);

        if (app()->isProduction()) {
            $this->command->info('Environment production terdeteksi. Hanya seeder penting (Region & Super Admin) yang dijalankan. Data dummy dilewati.');
            return;
        }

        $this->call([
            BrandCategorySeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,
            MenuDummySeeder::class,
            PromoDummySeeder::class,
            InformationChannelDummySeeder::class,
        ]);
    }
}
