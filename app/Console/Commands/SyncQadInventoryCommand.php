<?php

namespace App\Console\Commands;

use App\Jobs\SyncQadInventoryJob;
use Illuminate\Console\Command;

class SyncQadInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qad:sync-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job to synchronize inventory data from WMS to local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SyncQadInventoryJob::dispatch();

        $this->info('WMS inventory sync job dispatched to queue.');

        return Command::SUCCESS;
    }
}
