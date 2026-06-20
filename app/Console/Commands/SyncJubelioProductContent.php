<?php

namespace App\Console\Commands;

use App\Jobs\SyncJubelioProductContent as SyncJubelioProductContentJob;
use App\Services\JubelioProductContentSyncService;
use Illuminate\Console\Command;

class SyncJubelioProductContent extends Command
{
    protected $signature = 'jubelio:sync-product-content
                            {--code= : Sinkronkan satu produk berdasarkan item_code}
                            {--sync : Jalankan langsung tanpa queue}';

    protected $description = 'Sinkronkan deskripsi dan foto produk dari Jubelio';

    public function handle(JubelioProductContentSyncService $syncService): int
    {
        if (! config('jubelio.product_content.enabled', true)) {
            $this->warn('Sinkronisasi konten produk Jubelio dinonaktifkan.');

            return self::SUCCESS;
        }

        $code = $this->option('code') ?: null;

        if ($this->option('sync')) {
            $this->info('Menjalankan sinkronisasi konten produk Jubelio...');
            $stats = $syncService->sync($code);
            $this->table(['Metric', 'Count'], collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all());

            return ($stats['failed'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
        }

        SyncJubelioProductContentJob::dispatch($code);
        $this->info('Job sinkronisasi deskripsi & foto Jubelio telah di-dispatch ke queue.');

        return self::SUCCESS;
    }
}
