<?php

namespace App\Jobs;

use App\Models\QadInventory;
use App\Models\Warehouse;
use App\Services\QadService;
use App\Support\QadIntegration;
use App\Support\QadResponseHelper;
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
        return 'qad-sync-inventory';
    }

    public function handle(QadService $qad): void
    {
        Log::info('SyncQadInventoryJob: starting');

        if (! QadIntegration::isConfigured()) {
            Log::warning('SyncQadInventoryJob: skipped (QAD API belum dikonfigurasi)');

            return;
        }

        $warehouses = Warehouse::where('is_active', 1)->get();

        $locationCodes = $warehouses->map(function ($warehouse) {
            return $warehouse->qad_location_code ?: $warehouse->kode_hub;
        })->filter()->unique()->values()->all();

        if (empty($locationCodes)) {
            Log::info('SyncQadInventoryJob: no QAD location codes found in active warehouses');

            return;
        }

        $now = Carbon::now();
        $totalSynced = 0;

        foreach ($locationCodes as $locationCode) {
            try {
                QadInventory::where('qad_location_code', $locationCode)->update(['qty' => 0]);

                $response = $qad->getAllInventory([
                    'location' => $locationCode,
                    'search' => '',
                    'batch' => '',
                    'length' => 10000,
                ]);

                $items = class_exists(QadResponseHelper::class)
                    ? QadResponseHelper::list($response)
                    : ($response['data'] ?? []);

                if (empty($items)) {
                    Log::info('SyncQadInventoryJob: no inventory items returned', [
                        'location' => $locationCode,
                    ]);

                    continue;
                }

                $upsertData = [];
                foreach ($items as $item) {
                    $itemCode = $item['item_code'] ?? $item['itemCode'] ?? $item['itemID'] ?? $item['itemid'] ?? null;
                    $qty = (float) ($item['qty'] ?? $item['quantity'] ?? $item['onHand'] ?? 0);
                    $lotSerial = $item['lot_serial'] ?? $item['lotSerial'] ?? $item['batch'] ?? $item['lot'] ?? null;

                    $expiredStr = $item['expired_short'] ?? $item['expired'] ?? null;
                    $expiredDate = null;
                    if ($expiredStr) {
                        try {
                            $expiredDate = Carbon::parse($expiredStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $expiredDate = null;
                        }
                    }

                    if (! $itemCode) {
                        continue;
                    }

                    $upsertData[] = [
                        'item_code' => $itemCode,
                        'qad_location_code' => $locationCode,
                        'lot_serial' => $lotSerial ?: '',
                        'qty' => $qty,
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
                    Log::info('SyncQadInventoryJob: synced location', [
                        'location' => $locationCode,
                        'items' => count($upsertData),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('SyncQadInventoryJob: error syncing location', [
                    'location' => $locationCode,
                    'exception' => $e,
                ]);
            }
        }

        Log::info('SyncQadInventoryJob: completed', [
            'total_synced' => $totalSynced,
        ]);
    }
}
