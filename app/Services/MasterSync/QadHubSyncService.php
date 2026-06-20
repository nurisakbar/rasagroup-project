<?php

namespace App\Services\MasterSync;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\QadService;
use App\Support\ProductCodeMatcher;
use App\Support\QadIntegration;
use App\Support\QadResponseHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QadHubSyncService
{
    public function __construct(
        private QadService $qad
    ) {}

    /**
     * @return array{synced: int, skipped: int, stock_rows: int, configured: bool}
     */
    public function sync(bool $withStock = true): array
    {
        $stats = [
            'synced' => 0,
            'skipped' => 0,
            'stock_rows' => 0,
            'configured' => QadIntegration::isConfigured(),
        ];

        if (! $stats['configured']) {
            Log::channel('master_sync')->info('QadHubSync skipped: QAD not configured');

            return $stats;
        }

        $response = $this->qad->getInventoryLocation([]);
        $locations = QadResponseHelper::list($response);

        if ($locations === []) {
            Log::channel('master_sync')->warning('QadHubSync: no locations returned', [
                'response_error' => QadResponseHelper::isError($response),
            ]);
        }

        foreach ($locations as $index => $loc) {
            $kodeHub = $loc['location'] ?? $loc['locationID'] ?? $loc['locationid'] ?? null;
            $namaHub = $loc['description'] ?? $loc['locationName'] ?? $loc['locationname'] ?? null;

            if (! $kodeHub || ! $namaHub) {
                Log::channel('master_sync')->warning('QadHubSync: skip location', [
                    'index' => $index,
                    'location' => $loc,
                ]);
                $stats['skipped']++;

                continue;
            }

            $warehouse = Warehouse::firstOrNew(['kode_hub' => $kodeHub]);

            if (! $warehouse->exists) {
                $warehouse->fill([
                    'name' => $namaHub,
                    'slug' => Str::slug($namaHub) . '-' . strtolower($kodeHub),
                    'description' => 'Synced from QAD',
                    'is_active' => true,
                ]);
            } else {
                if (blank($warehouse->name)) {
                    $warehouse->name = $namaHub;
                }
                if (blank($warehouse->description)) {
                    $warehouse->description = 'Synced from QAD';
                }
            }

            $warehouse->markSyncSource('qad');
            $warehouse->save();
            $stats['synced']++;

            if ($withStock) {
                $stats['stock_rows'] += $this->refreshStock($warehouse);
            }
        }

        Log::channel('master_sync')->info('QadHubSync finished', $stats);

        return $stats;
    }

    /**
     * @return array{stock_rows: int, warehouses: int, configured: bool}
     */
    public function syncAllStocks(): array
    {
        $stats = [
            'stock_rows' => 0,
            'warehouses' => 0,
            'configured' => QadIntegration::isConfigured(),
        ];

        if (! $stats['configured']) {
            return $stats;
        }

        $warehouses = Warehouse::query()
            ->whereNotNull('kode_hub')
            ->where('kode_hub', '!=', '')
            ->get();

        foreach ($warehouses as $warehouse) {
            $rows = $this->refreshStock($warehouse);
            if ($rows > 0) {
                $stats['warehouses']++;
            }
            $stats['stock_rows'] += $rows;
        }

        Log::channel('master_sync')->info('QadHubSync all stocks finished', $stats);

        return $stats;
    }

    private function refreshStock(Warehouse $warehouse): int
    {
        $response = $this->qad->getAllInventory([
            'location' => $warehouse->kode_hub,
            'search' => '',
            'batch' => '',
            'length' => 1000,
        ]);

        $items = QadResponseHelper::list($response);
        $processed = 0;

        foreach ($items as $item) {
            $itemCode = $item['item_code'] ?? $item['itemCode'] ?? $item['itemID'] ?? $item['itemid'] ?? null;
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? $item['onHand'] ?? 0);

            if (! $itemCode) {
                continue;
            }

            $product = ProductCodeMatcher::findProduct($itemCode);
            if (! $product) {
                continue;
            }

            WarehouseStock::updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ],
                [
                    'stock' => max(0, $qty),
                ]
            );

            $processed++;
        }

        return $processed;
    }
}
