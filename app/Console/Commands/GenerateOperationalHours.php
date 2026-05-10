<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateOperationalHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-operational-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate default operational hours for all warehouses and distributors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating operational hours for warehouses...');
        $warehouses = \App\Models\Warehouse::all();
        foreach ($warehouses as $warehouse) {
            if ($warehouse->operationalHours()->count() === 0) {
                $warehouse->generateDefaultOperationalHours();
                $this->line("Generated for warehouse: {$warehouse->name}");
            }
        }

        $this->info('Generating operational hours for distributors...');
        $distributors = \App\Models\User::where('role', \App\Models\User::ROLE_DISTRIBUTOR)->get();
        foreach ($distributors as $distributor) {
            if ($distributor->operationalHours()->count() === 0) {
                $distributor->generateDefaultOperationalHours();
                $this->line("Generated for distributor: {$distributor->name} ({$distributor->email})");
            }
        }

        $this->info('Done!');
    }
}
