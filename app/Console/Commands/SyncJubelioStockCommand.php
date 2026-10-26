<?php

namespace App\Console\Commands;

use App\Jobs\SyncJubelioStockJob;
use App\Services\JubelioStockSyncService;
use Illuminate\Console\Command;

class SyncJubelioStockCommand extends Command
{
    protected $signature = 'jubelio:sync-stock
                            {--sync : Jalankan langsung secara synchronous tanpa queue}';

    protected $description = 'Sinkronkan stok produk seluruh hub/gudang dari Jubelio';

    public function handle(JubelioStockSyncService $stockSync): int
    {
        if ($this->option('sync')) {
            $this->info('Menjalankan sinkronisasi stok Jubelio & WMS langsung...');

            try {
                // 1. Sinkronisasi batch fisik WMS
                $wms = app(\App\Services\WmsService::class);
                if ($wms->isConfigured()) {
                    $this->line('Mengambil data batch fisik dari server WMS...');
                    $activeHubs = \App\Models\Warehouse::where('is_active', 1)->get();
                    $locCodes = $activeHubs->map(fn($w) => \App\Services\WmsService::locationCode($w))->filter()->unique();
                    $wmsSyncedCount = 0;
                    foreach ($locCodes as $locCode) {
                        $wmsSyncedCount += $wms->syncLocationBatches($locCode);
                    }
                    $this->info("WMS batch sync selesai: {$wmsSyncedCount} baris batch tersinkron.");
                }

                // 2. Sinkronisasi stok Jubelio yang dikombinasikan dengan WMS
                $this->line('Mengambil dan merekonsiliasi stok dari server Jubelio...');
                $stats = $stockSync->sync();
                $this->info('Sinkronisasi stok Jubelio & WMS berhasil!');
                $this->table(
                    ['Metric', 'Count'],
                    collect($stats)->map(fn ($val, $key) => [$key, $val])->values()->all()
                );

                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Gagal sinkronisasi stok: ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        SyncJubelioStockJob::dispatch();
        $this->info('Job SyncJubelioStockJob telah dikirim ke queue.');

        return self::SUCCESS;
    }
}
