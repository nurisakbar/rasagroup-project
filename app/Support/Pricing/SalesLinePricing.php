<?php

namespace App\Support\Pricing;

use App\Models\OrderItem;
use App\Support\TaxAwarePrice;

/**
 * Harga per baris order untuk ERP (QAD & Zoho): harga list (DPP katalog), persen diskon, dan harga net.
 *
 * Diskon selalu dinyatakan per item. Diskon lama di level order (orders.discount_percent) ikut
 * dilebur ke persen diskon setiap baris agar total di ERP sama dengan total order.
 */
class SalesLinePricing
{
    /**
     * @return array{list_price: float, discount_percent: float, net_price: float}
     */
    public static function forItem(OrderItem $item, float $soldPrice): array
    {
        $soldPrice = round(max(0.0, $soldPrice), 2);
        $catalogPrice = round(TaxAwarePrice::excludingTax($item->catalogUnitPrice()), 2);

        $discountPercent = (float) $item->unitDiscountPercent();
        if ($discountPercent <= 0 && $catalogPrice > $soldPrice && $catalogPrice > 0) {
            $discountPercent = round((($catalogPrice - $soldPrice) / $catalogPrice) * 100, 2);
        }

        $hasItemDiscount = $discountPercent > 0 && $catalogPrice > $soldPrice;
        $listPrice = $hasItemDiscount ? $catalogPrice : $soldPrice;
        $percent = $hasItemDiscount ? $discountPercent : 0.0;

        $orderPercent = (float) ($item->order?->discount_percent ?? 0);
        if ($orderPercent > 0) {
            $percent = round((1 - (1 - $percent / 100) * (1 - $orderPercent / 100)) * 100, 2);
        }

        // netPrice = listPrice x (1 - diskon%), boleh desimal (mis. 58559 x 80% = 46847.2) agar konsisten dengan hitungan ERP.
        $netPrice = $percent > 0
            ? round($listPrice * (1 - ($percent / 100)), 2)
            : (float) $listPrice;

        return [
            'list_price' => (float) $listPrice,
            'discount_percent' => (float) $percent,
            'net_price' => (float) $netPrice,
        ];
    }
}
