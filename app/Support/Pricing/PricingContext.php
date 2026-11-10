<?php

namespace App\Support\Pricing;

use App\Models\WarehouseProductPrice;

/**
 * Warehouse yang harganya dipakai untuk Product::$price selama satu request.
 *
 * - Storefront (diaktifkan middleware): mengikuti hub terpilih di session.
 * - Keranjang/checkout: dipaksa ke warehouse keranjang / hub akhir order lewat useWarehouse().
 * - Admin, panel warehouse, job antrean: tidak aktif, jadi Product::$price = harga global.
 */
class PricingContext
{
    private bool $storefront = false;

    private bool $hasOverride = false;

    private ?string $override = null;

    /** @var array<string, array<string, string>> warehouse_id => [product_id => price] */
    private array $masterPrices = [];

    public static function current(): self
    {
        return app(self::class);
    }

    public function enableStorefront(): void
    {
        $this->storefront = true;
    }

    /**
     * Paksa warehouse harga (null = harga global) sampai request selesai.
     */
    public function useWarehouse(?string $warehouseId): void
    {
        $this->hasOverride = true;
        $this->override = $warehouseId ?: null;
    }

    /**
     * Jalankan $callback dengan warehouse harga tertentu, lalu kembalikan konteks sebelumnya.
     *
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    public function forWarehouse(?string $warehouseId, callable $callback): mixed
    {
        [$hadOverride, $previous] = [$this->hasOverride, $this->override];
        $this->useWarehouse($warehouseId);

        try {
            return $callback();
        } finally {
            [$this->hasOverride, $this->override] = [$hadOverride, $previous];
        }
    }

    public function warehouseId(): ?string
    {
        if ($this->hasOverride) {
            return $this->override;
        }

        if ($this->storefront && app()->bound('session')) {
            $hubId = session('selected_hub_id');

            return $hubId ? (string) $hubId : null;
        }

        return null;
    }

    /**
     * Harga master yang berlaku sekarang di warehouse konteks, atau null (pakai harga global).
     */
    public function masterPriceFor(?string $productId): ?string
    {
        $warehouseId = $this->warehouseId();
        if ($warehouseId === null || $productId === null) {
            return null;
        }

        if (! isset($this->masterPrices[$warehouseId])) {
            // Satu query per warehouse per request; baris pertama per produk = harga yang berlaku.
            $this->masterPrices[$warehouseId] = WarehouseProductPrice::where('warehouse_id', $warehouseId)
                ->effectiveAt(now())
                ->get(['product_id', 'price'])
                ->unique('product_id')
                ->mapWithKeys(fn ($row) => [$row->product_id => (string) $row->getRawOriginal('price')])
                ->all();
        }

        return $this->masterPrices[$warehouseId][$productId] ?? null;
    }

    /**
     * Lupakan cache harga (mis. setelah harga master diubah dalam request yang sama).
     */
    public function flush(): void
    {
        $this->masterPrices = [];
    }
}
