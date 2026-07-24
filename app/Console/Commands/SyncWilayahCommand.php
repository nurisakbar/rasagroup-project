<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncWilayahCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-wilayah';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync administrative regions from view_cache to master tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting wilayah synchronization...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $this->info('Truncating old tables...');
            DB::table('villages')->truncate();
            DB::table('districts')->truncate();
            DB::table('regencies')->truncate();
            DB::table('provinces')->truncate();

            // Insert Provinces
            $this->info('Syncing Provinces...');
            $provinces = DB::table('view_wilayah_administratif_indonesia_cache')
                ->select('province_id as id', 'province_name as name')
                ->distinct()
                ->whereNotNull('province_id')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })->toArray();
            
            // Chunk insert
            foreach (array_chunk($provinces, 1000) as $chunk) {
                DB::table('provinces')->insert($chunk);
            }

            // Insert Regencies
            $this->info('Syncing Regencies...');
            $regencies = DB::table('view_wilayah_administratif_indonesia_cache')
                ->select('regency_id as id', 'province_id', 'regency_name as name')
                ->distinct()
                ->whereNotNull('regency_id')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })->toArray();
            
            foreach (array_chunk($regencies, 1000) as $chunk) {
                DB::table('regencies')->insert($chunk);
            }

            // Insert Districts
            $this->info('Syncing Districts...');
            $districts = DB::table('view_wilayah_administratif_indonesia_cache')
                ->select('district_id as id', 'regency_id', 'district_name as name')
                ->distinct()
                ->whereNotNull('district_id')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })->toArray();
            
            foreach (array_chunk($districts, 1000) as $chunk) {
                DB::table('districts')->insert($chunk);
            }

            // Insert Villages
            $this->info('Syncing Villages...');
            DB::table('view_wilayah_administratif_indonesia_cache')
                ->select('village_id as id', 'district_id', 'village_name as name')
                ->distinct()
                ->whereNotNull('village_id')
                ->orderBy('village_id')
                ->chunk(2000, function ($villagesChunk) {
                    $insertData = $villagesChunk->map(function ($item) {
                        return (array) $item;
                    })->toArray();
                    DB::table('villages')->insert($insertData);
                });

            $this->info('Synchronization completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
