<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JubelioStockSyncService
{
    public function __construct(
        private JubelioService $jubelio
    ) {}

    /**
     * @return array{stock_rows: int, products: int, skipped_products: int, skipped_locations: int}
     */
    public function sync(): array
    {
        $token = $this->jubelio->login();
        $locations = $this->jubelio->fetchAllLocations($token);
        $itemIds = $this->jubelio->fetchAllItemIds($token);

        if (empty($itemIds)) {
            throw new \RuntimeException('Tidak ada produk ditemukan di Jubelio.');
        }

        $locationToWarehouse = $this->buildLocationWarehouseMap($locations);
        if (empty($locationToWarehouse)) {
            throw new \RuntimeException('Tidak ada hub lokal yang cocok dengan lokasi Jubelio. Sinkronkan hub terlebih dahulu.');
        }

        $productsByCode = Product::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->pluck('id', 'code');

        $stockItems = $this->jubelio->fetchStocksByItemIds($token, $itemIds);

        $stats = [
            'stock_rows' => 0,
            'products' => 0,
            'skipped_products' => 0,
            'skipped_locations' => 0,
        ];

        DB::transaction(function () use ($stockItems, $locationToWarehouse, $productsByCode, &$stats) {
            $syncedProducts = [];

            foreach ($stockItems as $item) {
                $itemCode = $item['item_code'] ?? null;
                if (!$itemCode || !isset($productsByCode[$itemCode])) {
                    $stats['skipped_products']++;
                    continue;
                }

                $productId = $productsByCode[$itemCode];
                $syncedProducts[$productId] = true;

                foreach ($item['location_stocks'] ?? [] as $locationStock) {
                    $locationId = (int) ($locationStock['location_id'] ?? 0);
                    $warehouseId = $locationToWarehouse[$locationId] ?? null;

                    if (!$warehouseId) {
                        $stats['skipped_locations']++;
                        continue;
                    }

                    WarehouseStock::updateOrCreate(
                        [
                            'warehouse_id' => $warehouseId,
                            'product_id' => $productId,
                        ],
                        [
                            'stock' => max(0, (int) ($locationStock['available'] ?? $locationStock['on_hand'] ?? 0)),
                        ]
                    );

                    $stats['stock_rows']++;
                }
            }

            $stats['products'] = count($syncedProducts);
        });

        Log::info('Jubelio stock sync finished', $stats);

        return $stats;
    }

    /**
     * Map Jubelio location_id → warehouse UUID via location_code = kode_hub.
     *
     * @param  array<int, array<string, mixed>>  $locations
     * @return array<int, string>
     */
    private function buildLocationWarehouseMap(array $locations): array
    {
        $warehousesByKode = Warehouse::query()
            ->whereNotNull('kode_hub')
            ->where('kode_hub', '!=', '')
            ->pluck('id', 'kode_hub');

        $map = [];

        foreach ($locations as $location) {
            $locationId = $location['location_id'] ?? null;
            $locationCode = $location['location_code'] ?? null;

            if ($locationId === null || !$locationCode) {
                continue;
            }

            if (!($location['is_warehouse'] ?? true)) {
                continue;
            }

            $warehouseId = $warehousesByKode[$locationCode] ?? null;
            if ($warehouseId) {
                $map[(int) $locationId] = $warehouseId;
            }
        }

        return $map;
    }
}
