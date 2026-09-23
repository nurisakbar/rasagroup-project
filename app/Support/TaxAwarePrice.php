<?php

namespace App\Support;

use App\Models\Setting;

class TaxAwarePrice
{
    /**
     * Harga sebelum PPN (DPP). Harga katalog dianggap sudah termasuk pajak.
     */
    public static function excludingTax(float $inclusivePrice): float
    {
        $taxPercent = Setting::taxPercent();
        if ($taxPercent <= 0 || $inclusivePrice <= 0) {
            return $inclusivePrice;
        }

        return $inclusivePrice / (1 + ($taxPercent / 100));
    }

    /**
     * Diskon dihitung dari harga setelah pajak dikeluarkan.
     * Contoh: 111.000, PPN 11% → DPP 100.000, diskon 20% → 80.000.
     */
    public static function applyDiscount(float $inclusivePrice, float $discountPercent): float
    {
        if ($discountPercent <= 0) {
            return $inclusivePrice;
        }

        $dpp = self::excludingTax($inclusivePrice);

        return round($dpp * (1 - ($discountPercent / 100)), 2);
    }
}
