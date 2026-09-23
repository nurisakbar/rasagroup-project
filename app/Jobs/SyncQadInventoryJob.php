<?php

namespace App\Jobs;

use App\Models\QadInventory;
use App\Models\Warehouse;
use App\Services\WmsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncQadInventoryJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return 'wms-sync-inventory';
    }

    public function handle(WmsService $wms): void
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

        $now = Carbon::now();
        $totalSynced = 0;

        foreach ($locationCodes as $locationCode) {
            try {
                $items = $wms->flatBatches($locationCode);

                if (empty($items)) {
                    Log::info('SyncQadInventoryJob: no inventory items returned', [
                        'location' => $locationCode,
                    ]);

                    continue;
                }

                $upsertData = [];
                foreach ($items as $item) {
                    $itemCode = $item['item_code'] ?? null;
                    $lotSerial = $item['lot_serial'] ?? '';
                    if (! $itemCode) {
                        continue;
                    }

                    $expiredDate = null;
                    if (! empty($item['expired'])) {
                        try {
                            $expiredDate = Carbon::parse($item['expired'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $expiredDate = null;
                        }
                    }

                    $upsertData[] = [
                        'item_code' => $itemCode,
                        'qad_location_code' => $locationCode,
                        'lot_serial' => $lotSerial,
                        'qty' => (float) ($item['qty'] ?? 0),
                        'expired_date' => $expiredDate,
                        'last_sync_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($upsertData)) {
                    foreach (array_chunk($upsertData, 500) as $chunk) {
                        QadInventory::upsert(
                            $chunk,
                            ['item_code', 'qad_location_code', 'lot_serial'],
                            ['qty', 'expired_date', 'last_sync_at', 'updated_at']
                        );
                    }
                    $totalSynced += count($upsertData);
                    QadInventory::where('qad_location_code', $locationCode)
                        ->where(function ($query) use ($now) {
                            $query->whereNull('last_sync_at')
                                ->orWhere('last_sync_at', '<', $now);
                        })
                        ->update(['qty' => 0]);
                    Log::info('SyncQadInventoryJob: synced location from WMS', [
                        'location' => $locationCode,
                        'items' => count($upsertData),
                    ]);
                }
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
