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
        
        if ($driver === 'mysql') {
            $this->command->info('MySQL detected - importing regions cache data...');
            
            // Drop old view if it exists
            DB::statement("DROP VIEW IF EXISTS view_wilayah_administratif_indonesia");
            DB::statement("DROP TABLE IF EXISTS view_wilayah_administratif_indonesia_cache");
            
            $sqlPath = database_path('seeders/view_wilayah_administratif_indonesia_cache.sql');
            if (file_exists($sqlPath)) {
                $this->command->info('Executing SQL dump for wilayah administratf cache...');
                DB::unprepared(file_get_contents($sqlPath));
                $this->command->info('Regions cache data imported successfully.');
            } else {
                $this->command->error('SQL file not found at: ' . $sqlPath);
            }
        } else {
            $this->command->warn('Seeding regions is only supported on MySQL driver.');
        }
    }
}
