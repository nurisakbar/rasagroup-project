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
    protected $description = 'Sinkronisasi stok batch WMS/QAD ke database lokal';

    public function handle(): int
    {
        $this->info('Job sinkronisasi stok batch dimasukkan ke queue.');
        SyncQadInventoryJob::dispatch();

        return Command::SUCCESS;
    }
}
