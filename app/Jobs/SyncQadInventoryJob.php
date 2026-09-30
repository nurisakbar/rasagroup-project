<?php

namespace App\Jobs;

use App\Models\Warehouse;
use App\Services\WmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncQadInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public function handle(WmsService $wms): void
    {
        $lock = Cache::lock('wms-sync-inventory-run', 600);

        if (! $lock->get()) {
            $this->queueNext();

            return;
        }

        try {
            $this->syncAllHubs($wms);
        } finally {
            optional($lock)->release();
            $this->queueNext();
        }
    }

    public function failed(?\Throwable $e): void
    {
        $this->queueNext();
    }

    /**
     * Pastikan rantai job 5 menit sudah ada di queue (tanpa scheduler).
     */
    public static function ensureQueued(): void
    {
        if (app()->runningUnitTests() || app()->runningInConsole()) {
            return;
        }

        if (config('queue.default') === 'sync') {
            return;
        }

        if (! Cache::add('wms-inventory-chain', 1, now()->addMinutes(6))) {
            return;
        }

        static::dispatch();
    }

    private function queueNext(): void
    {
        if (config('queue.default') === 'sync') {
            return;
        }

        Cache::put('wms-inventory-chain', 1, now()->addMinutes(6));

        if (! Cache::add('wms-inventory-next-queued', 1, now()->addMinutes(4))) {
            return;
        }

        static::dispatch()->delay(now()->addMinutes(5));
    }

    private function syncAllHubs(WmsService $wms): void
    {
        Log::info('SyncQadInventoryJob: starting (WMS)');

        if (! $wms->isConfigured()) {
            Log::warning('SyncQadInventoryJob: skipped (WMS API belum dikonfigurasi)');

            return;
        }

        $warehouses = Warehouse::where('is_active', 1)->get();

        $locationCodes = $warehouses->map(function ($warehouse) {
            return WmsService::locationCode($warehouse);
        })->filter()->unique()->values()->all();

        if (empty($locationCodes)) {
            Log::info('SyncQadInventoryJob: no WMS location codes found in active warehouses');

            return;
        }

        $totalSynced = 0;

        foreach ($locationCodes as $locationCode) {
            try {
                $synced = $wms->syncLocationBatches($locationCode);

                if ($synced === 0) {
                    Log::info('SyncQadInventoryJob: no inventory items returned', [
                        'location' => $locationCode,
                    ]);

                    continue;
                }

                $totalSynced += $synced;
                Log::info('SyncQadInventoryJob: synced location from WMS', [
                    'location' => $locationCode,
                    'items' => $synced,
                ]);
            } catch (\Exception $e) {
                Log::error('SyncQadInventoryJob: error syncing location from WMS', [
                    'location' => $locationCode,
                    'exception' => $e,
                ]);
            }
        }

        Log::info('SyncQadInventoryJob: completed (WMS)', [
            'total_synced' => $totalSynced,
        ]);
    }
}
