<?php

namespace App\Support;

use App\Models\Address;
use App\Models\Warehouse;

final class ShopFulfillment
{
    public static function assumeStockReady(): bool
    {
        return (bool) config('shop.assume_stock_ready', true);
    }

    public static function autoHubByAddress(): bool
    {
        return (bool) config('shop.auto_hub_by_address', true);
    }

    public static function showStockOnStorefront(): bool
    {
        return (bool) config('shop.show_stock_on_storefront', false);
    }

    public static function resolveNearestHub(Address $address, ?string $excludeWarehouseId = null): ?Warehouse
    {
        return Warehouse::findBestHubForAddress($address, $excludeWarehouseId);
    }
}
