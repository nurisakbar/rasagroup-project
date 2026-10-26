<?php

namespace App\Services;

use App\Models\Product;
use App\Models\QadInventory;
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

        $products = Product::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->get(['id', 'code', 'alternate_codes']);

        $productsByCode = [];
        $productAllCodes = [];
        foreach ($products as $p) {
            $allCodes = $p->getAllCodes();
            $productAllCodes[$p->id] = $allCodes;
            foreach ($allCodes as $c) {
                $productsByCode[strtoupper(trim($c))] = $p->id;
            }
        }

        $stockItems = $this->jubelio->fetchStocksByItemIds($token, $itemIds);

        // Ambil stok fisik dari WMS/QAD untuk gudang-gudang terkait
        $warehouses = Warehouse::whereIn('id', array_values($locationToWarehouse))->get()->keyBy('id');
        $wmsStockByWarehouse = [];
        foreach ($warehouses as $wh) {
            if (!empty($wh->qad_location_code)) {
                $wmsStockByWarehouse[$wh->id] = QadInventory::where('qad_location_code', $wh->qad_location_code)
                    ->where('qty', '>', 0)
                    ->selectRaw('UPPER(TRIM(item_code)) as item_code, sum(qty) as total_qty')
                    ->groupBy(DB::raw('UPPER(TRIM(item_code))'))
                    ->pluck('total_qty', 'item_code')
                    ->map(fn($q) => (int) $q)
                    ->toArray();
            }
        }

        $stats = [
            'stock_rows' => 0,
            'products' => 0,
            'skipped_products' => 0,
            'skipped_locations' => 0,
        ];

        DB::transaction(function () use ($stockItems, $locationToWarehouse, $productsByCode, $wmsStockByWarehouse, &$stats) {
            $syncedProducts = [];

            foreach ($stockItems as $item) {
                $rawCode = $item['item_code'] ?? null;
                $itemCode = strtoupper(trim((string) $rawCode));
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

                    $jubelioQty = max(0, (int) ($locationStock['available'] ?? $locationStock['on_hand'] ?? 0));
                    $wmsQty = 0;
                    if (isset($productAllCodes[$productId])) {
                        foreach ($productAllCodes[$productId] as $c) {
                            $wmsQty += (int) ($wmsStockByWarehouse[$warehouseId][strtoupper(trim($c))] ?? 0);
                        }
                    } else {
                        $wmsQty = (int) ($wmsStockByWarehouse[$warehouseId][$itemCode] ?? 0);
                    }
                    $effectiveStock = max($jubelioQty, $wmsQty);

                    WarehouseStock::updateOrCreate(
                        [
                            'warehouse_id' => $warehouseId,
                            'product_id' => $productId,
                        ],
                        [
                            'stock' => $effectiveStock,
                        ]
                    );

                    $stats['stock_rows']++;
                }
            }

            // Pastikan item yang memiliki stok fisik di WMS tapi tidak terdaftar di Jubelio juga ter-update
            foreach ($wmsStockByWarehouse as $whId => $wmsItems) {
                foreach ($wmsItems as $wmsCode => $wmsQty) {
                    if ($wmsQty <= 0) continue;
                    $pId = $productsByCode[$wmsCode] ?? null;
                    if (!$pId) continue;

                    $totalWmsForProduct = 0;
                    if (isset($productAllCodes[$pId])) {
                        foreach ($productAllCodes[$pId] as $c) {
                            $totalWmsForProduct += (int) ($wmsStockByWarehouse[$whId][strtoupper(trim($c))] ?? 0);
                        }
                    } else {
                        $totalWmsForProduct = $wmsQty;
                    }

                    $existing = WarehouseStock::where('warehouse_id', $whId)->where('product_id', $pId)->first();
                    if (!$existing || $existing->stock < $totalWmsForProduct) {
                        WarehouseStock::updateOrCreate(
                            ['warehouse_id' => $whId, 'product_id' => $pId],
                            ['stock' => $totalWmsForProduct]
                        );
                        $syncedProducts[$pId] = true;
                        $stats['stock_rows']++;
                    }
                }
            }

            $stats['products'] = count($syncedProducts);
        });

        Log::info('Jubelio and WMS stock sync finished', $stats);

        return $stats;
    }

    /**
     * Sinkronkan stok satu gudang spesifik secara langsung (Jubelio + WMS).
     */
    public function syncWarehouse(Warehouse $warehouse): int
    {
        if (empty($warehouse->kode_hub)) {
            return 0;
        }

        $token = $this->jubelio->login();
        $locationId = $this->jubelio->findLocationIdByCode($token, $warehouse->kode_hub);
        if (!$locationId) {
            return 0;
        }

        $items = $this->jubelio->fetchItemsToSell($token, $locationId);
        $products = Product::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->get(['id', 'code', 'alternate_codes']);

        $productsByCode = [];
        $productAllCodes = [];
        foreach ($products as $p) {
            $allCodes = $p->getAllCodes();
            $productAllCodes[$p->id] = $allCodes;
            foreach ($allCodes as $c) {
                $productsByCode[strtoupper(trim($c))] = $p->id;
            }
        }

        $qadCode = $warehouse->qad_location_code;
        $wmsTotals = [];
        if (!empty($qadCode)) {
            $wmsTotals = QadInventory::where('qad_location_code', $qadCode)
                ->where('qty', '>', 0)
                ->selectRaw('UPPER(TRIM(item_code)) as item_code, sum(qty) as total_qty')
                ->groupBy(DB::raw('UPPER(TRIM(item_code))'))
                ->pluck('total_qty', 'item_code')
                ->map(fn($q) => (int) $q)
                ->toArray();
        }

        $updated = 0;
        DB::transaction(function () use ($items, $productsByCode, $wmsTotals, $warehouse, &$updated) {
            foreach ($items as $item) {
                $itemCode = strtoupper(trim((string) ($item['item_code'] ?? '')));
                $productId = $productsByCode[$itemCode] ?? null;
                if (!$productId) continue;

                $jubelioQty = max(0, (int) ($item['available_qty'] ?? 0));
                
                $wmsQty = 0;
                if (isset($productAllCodes[$productId])) {
                    foreach ($productAllCodes[$productId] as $c) {
                        $wmsQty += (int) ($wmsTotals[strtoupper(trim($c))] ?? 0);
                    }
                } else {
                    $wmsQty = (int) ($wmsTotals[$itemCode] ?? 0);
                }

                $finalStock = max($jubelioQty, $wmsQty);

                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'product_id' => $productId],
                    ['stock' => $finalStock]
                );
                $updated++;
            }

            foreach ($wmsTotals as $wmsCode => $wmsQty) {
                if ($wmsQty <= 0) continue;
                $productId = $productsByCode[$wmsCode] ?? null;
                if (!$productId) continue;

                $totalWmsForProduct = 0;
                if (isset($productAllCodes[$productId])) {
                    foreach ($productAllCodes[$productId] as $c) {
                        $totalWmsForProduct += (int) ($wmsTotals[strtoupper(trim($c))] ?? 0);
                    }
                } else {
                    $totalWmsForProduct = $wmsQty;
                }

                $existing = WarehouseStock::where('warehouse_id', $warehouse->id)->where('product_id', $productId)->first();
                if (!$existing || $existing->stock < $totalWmsForProduct) {
                    WarehouseStock::updateOrCreate(
                        ['warehouse_id' => $warehouse->id, 'product_id' => $productId],
                        ['stock' => $totalWmsForProduct]
                    );
                    $updated++;
                }
            }
        });

        return $updated;
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
