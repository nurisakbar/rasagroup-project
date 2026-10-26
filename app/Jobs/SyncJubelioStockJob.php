<?php

namespace App\Jobs;

use App\Services\JubelioStockSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncJubelioStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function handle(JubelioStockSyncService $stockSync): void
    {
        if (! config('jubelio.stock_sync.enabled', true)) {
            Log::info('SyncJubelioStockJob: disabled in config, skip');

            return;
        }

        $lock = Cache::lock('jubelio-sync-stock-run', 600);

        if (! $lock->get()) {
            Log::info('SyncJubelioStockJob: another sync instance is currently running, skip');
            $this->queueNext();

            return;
        }

        try {
            Log::info('SyncJubelioStockJob: starting Jubelio and WMS stock sync');

            // 1. Sinkronisasi data batch fisik dari WMS untuk seluruh hub aktif
            try {
                $wms = app(\App\Services\WmsService::class);
                if ($wms->isConfigured()) {
                    $activeHubs = \App\Models\Warehouse::where('is_active', 1)->get();
                    $locCodes = $activeHubs->map(fn($w) => \App\Services\WmsService::locationCode($w))->filter()->unique();
                    foreach ($locCodes as $locCode) {
                        $wms->syncLocationBatches($locCode);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('SyncJubelioStockJob: WMS batch sync warning', ['error' => $e->getMessage()]);
            }

            // 2. Sinkronisasi data stok dari Jubelio yang dikombinasikan dengan WMS
            $stats = $stockSync->sync();
            Log::info('SyncJubelioStockJob: completed successfully', $stats);
        } catch (\Throwable $e) {
            Log::error('SyncJubelioStockJob: execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            optional($lock)->release();
            $this->queueNext();
        }
    }

    public function failed(?\Throwable $e): void
    {
        Log::error('SyncJubelioStockJob: job failed in queue', [
            'error' => $e?->getMessage(),
        ]);
        $this->queueNext();
    }

    /**
     * Pastikan rantai job 1 jam sudah ada di queue (fallback tanpa scheduler cron).
     */
    public static function ensureQueued(): void
    {
        if (app()->runningUnitTests() || app()->runningInConsole()) {
            return;
        }

        if (config('queue.default') === 'sync') {
            return;
        }

        if (! config('jubelio.stock_sync.enabled', true)) {
            return;
        }

        if (! Cache::add('jubelio-stock-chain', 1, now()->addMinutes(70))) {
            return;
        }

        static::dispatch();
    }

    private function queueNext(): void
    {
        if (config('queue.default') === 'sync') {
            return;
        }

        if (! config('jubelio.stock_sync.enabled', true)) {
            return;
        }

        Cache::put('jubelio-stock-chain', 1, now()->addMinutes(70));

        if (! Cache::add('jubelio-stock-next-queued', 1, now()->addMinutes(50))) {
            return;
        }

        static::dispatch()->delay(now()->addHour());
    }
}
