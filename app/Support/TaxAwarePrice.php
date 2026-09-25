<?php

namespace App\Support;

use App\Models\Setting;

class TaxAwarePrice
{
    /**
     * Harga sebelum PPN (DPP). Harga katalog dianggap sudah termasuk pajak.
     */
    public static function excludingTax(float $inclusivePrice, ?float $taxPercent = null): float
    {
        $taxPercent ??= Setting::taxPercent();
        if ($taxPercent <= 0 || $inclusivePrice <= 0) {
            return $inclusivePrice;
        }

        return $inclusivePrice / (1 + ($taxPercent / 100));
    }

    /**
     * Diskon dihitung dari harga setelah pajak dikeluarkan.
     * Contoh: 111.000, PPN 11% → DPP 100.000, diskon 20% → 80.000.
     */
    public static function applyDiscount(float $inclusivePrice, float $discountPercent, ?float $taxPercent = null): float
    {
        if ($discountPercent <= 0) {
            return $inclusivePrice;
        }

        $dpp = self::excludingTax($inclusivePrice, $taxPercent);

        return round($dpp * (1 - ($discountPercent / 100)), 2);
    }

    /**
     * Pecah nilai jual vs katalog menjadi DPP, PPN, dan harga termasuk pajak.
     * Harga diskon kategori/price level sudah DPP; harga katalog dianggap termasuk PPN.
     *
     * @return array{dpp: float, ppn: float, inclusive: float, tax_percent: float}
     */
    public static function breakdown(float $soldAmount, float $catalogAmount, ?float $taxPercent = null): array
    {
        $taxPercent ??= Setting::taxPercent();
        $soldAmount = max(0, $soldAmount);
        $catalogAmount = max(0, $catalogAmount);
        $hasDiscount = ($catalogAmount - $soldAmount) > 0.5;

        if ($hasDiscount) {
            $dpp = round($soldAmount, 2);
            $ppn = $taxPercent > 0 ? round($dpp * ($taxPercent / 100), 2) : 0.0;

            return [
                'dpp' => $dpp,
                'ppn' => $ppn,
                'inclusive' => round($dpp + $ppn, 2),
                'tax_percent' => $taxPercent,
            ];
        }

        $dpp = round(self::excludingTax($soldAmount, $taxPercent), 2);
        $ppn = round(max(0, $soldAmount - $dpp), 2);

        return [
            'dpp' => $dpp,
            'ppn' => $ppn,
            'inclusive' => round($soldAmount, 2),
            'tax_percent' => $taxPercent,
        ];
    }

    public static function percentIfEnabled(?bool $enabled): float
    {
        return $enabled === false ? 0.0 : Setting::taxPercent();
    }

    public static function ppnLabel(?float $taxPercent = null): string
    {
        $taxPercent ??= Setting::taxPercent();
        if ($taxPercent <= 0) {
            return 'PPN';
        }

        $formatted = rtrim(rtrim(number_format($taxPercent, 1, ',', '.'), '0'), ',');

        return 'PPN '.$formatted.'%';
    }
}
