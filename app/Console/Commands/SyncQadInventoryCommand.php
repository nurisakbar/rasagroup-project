<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\QadInventory;
use App\Services\QadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Synchronize inventory data from QAD to local database';

    /**
     * Execute the console command.
     */
    public function handle(QadService $qad)
    {
        $this->info('Starting QAD Inventory Synchronization...');

        if (!$qad->isConfigured()) {
            $this->error('QAD API is not configured. Aborting.');
            return Command::FAILURE;
        }

        // Get unique QAD location codes from warehouses
        $warehouses = Warehouse::where('is_active', 1)->get();
        
        $locationCodes = $warehouses->map(function ($warehouse) {
            return $warehouse->qad_location_code ?: $warehouse->kode_hub;
        })->filter()->unique()->values()->all();

        if (empty($locationCodes)) {
            $this->info('No QAD location codes found in active warehouses.');
            return Command::SUCCESS;
        }

        $now = Carbon::now();
        $totalSynced = 0;

        foreach ($locationCodes as $locationCode) {
            $this->info("Fetching inventory for location: {$locationCode}");
            
            try {
                // Pre-update: set all qtys to 0 for this location so that items no longer returned 
                // by the API will have 0 stock.
                QadInventory::where('qad_location_code', $locationCode)->update(['qty' => 0]);

                $response = $qad->getAllInventory([
                    'location' => $locationCode,
                    'search' => '',
                    'batch' => '',
                    'length' => 10000, // Large number to get all items
                ]);

                $items = class_exists(\App\Support\QadResponseHelper::class) 
                    ? \App\Support\QadResponseHelper::list($response) 
                    : ($response['data'] ?? []);

                if (empty($items)) {
                    $this->warn("No inventory items returned for location: {$locationCode}");
                    continue;
                }

                $upsertData = [];
                foreach ($items as $item) {
                    $itemCode = $item['item_code'] ?? $item['itemCode'] ?? $item['itemID'] ?? $item['itemid'] ?? null;
                    $qty = (float) ($item['qty'] ?? $item['quantity'] ?? $item['onHand'] ?? 0);
                    $lotSerial = $item['lot_serial'] ?? $item['lotSerial'] ?? $item['batch'] ?? $item['lot'] ?? null;
                    
                    // Expired date parsing
                    $expiredStr = $item['expired_short'] ?? $item['expired'] ?? null;
                    $expiredDate = null;
                    if ($expiredStr) {
                        try {
                            $expiredDate = Carbon::parse($expiredStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $expiredDate = null;
                        }
                    }

                    if (!$itemCode) {
                        continue;
                    }

                    $upsertData[] = [
                        'item_code' => $itemCode,
                        'qad_location_code' => $locationCode,
                        'lot_serial' => $lotSerial ?: '', // Can't have null in unique key combination in some DBs, safe fallback
                        'qty' => $qty,
                        'expired_date' => $expiredDate,
                        'last_sync_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($upsertData)) {
                    // Chunk the upsert to avoid large queries
                    foreach (array_chunk($upsertData, 500) as $chunk) {
                        QadInventory::upsert(
                            $chunk,
                            ['item_code', 'qad_location_code', 'lot_serial'], // Unique columns
                            ['qty', 'expired_date', 'last_sync_at', 'updated_at'] // Update columns
                        );
                    }
                    $totalSynced += count($upsertData);
                    $this->info("Successfully synced " . count($upsertData) . " items for location: {$locationCode}");
                }

            } catch (\Exception $e) {
                $this->error("Error syncing location {$locationCode}: " . $e->getMessage());
                Log::error("QAD Sync Error for {$locationCode}", ['exception' => $e]);
            }
        }

        $this->info("Synchronization completed! Total records synced: {$totalSynced}");
        return Command::SUCCESS;
    }
}
