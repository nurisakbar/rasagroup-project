<?php

namespace App\Support\Pricing;

use App\Models\Product;
use App\Models\WarehouseProductPrice;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Sumber tunggal harga katalog per warehouse: master harga yang berlaku, atau harga global produk
 * bila warehouse belum punya harga master untuk produk tersebut.
 */
class WarehousePriceResolver
{
    /**
     * @return array{price: float, price_master_id: ?string}
     */
    public function catalogPrice(Product $product, ?string $warehouseId, ?CarbonInterface $at = null): array
    {
        $master = $warehouseId ? $this->masterPrice($product->id, $warehouseId, $at) : null;

        return [
            'price' => $master ? (float) $master->price : (float) $product->price,
            'price_master_id' => $master?->id,
        ];
    }

    public function masterPrice(string $productId, string $warehouseId, ?CarbonInterface $at = null): ?WarehouseProductPrice
    {
        return WarehouseProductPrice::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->effectiveAt($at ?? now())
            ->first();
    }

    /**
     * Harga master yang berlaku untuk banyak produk sekaligus, dikunci per product_id.
     *
     * @param  array<int, string>  $productIds
     * @return Collection<string, WarehouseProductPrice>
     */
    public function masterPricesFor(string $warehouseId, array $productIds, ?CarbonInterface $at = null): Collection
    {
        return WarehouseProductPrice::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->effectiveAt($at ?? now())
            ->get()
            ->unique('product_id')
            ->keyBy('product_id');
    }
}
