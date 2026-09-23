<?php

namespace App\Support;

use App\Models\Warehouse;
use App\Services\WmsService;

final class QadInventoryStock
{
    public static function locationCode(?Warehouse $warehouse): ?string
    {
        return WmsService::locationCode($warehouse);
    }

    /**
     * Qty batch per item_code dari WMS (lot dengan "-" dilewati; filter masa berlaku opsional).
     *
     * @return array<string, int>|null null jika lokasi tidak ada / fetch gagal
     */
    public static function qtyByItemCode(?Warehouse $warehouse, int $minMasaBerlakuBulan = 0): ?array
    {
        return app(WmsService::class)->qtyByItemCode($warehouse, $minMasaBerlakuBulan);
    }
}
