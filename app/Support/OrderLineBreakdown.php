<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;

/**
 * Rincian harga per baris pesanan untuk tampilan (halaman pesanan, email, invoice, admin).
 *
 * Aturan: harga katalog sudah termasuk PPN. Diskon dihitung dari harga sebelum PPN, lalu PPN dihitung
 * dari harga setelah diskon. Diskon dan PPN ditampilkan per baris, sehingga ringkasan cukup berisi
 * subtotal produk (jumlah kolom total), ongkos kirim, biaya layanan, dan total bayar.
 *
 * Jumlah total baris + ongkir + biaya layanan selalu sama dengan total pesanan: selisih pembulatan
 * (beberapa rupiah) ditaruh di PPN baris terakhir.
 */
class OrderLineBreakdown
{
    /** Batas selisih pembulatan yang dikoreksi ke baris terakhir. */
    private const ROUNDING_TOLERANCE = 5.0;

    /**
     * @return array{
     *     reliable: bool,
     *     uses_ppn: bool,
     *     tax_percent: float,
     *     tax_label: string,
     *     lines: list<array{
     *         item: OrderItem,
     *         no: int,
     *         name: string,
     *         code: ?string,
     *         quantity_label: string,
     *         quantity: int,
     *         unit_price_incl: float,
     *         unit_price_dpp: float,
     *         line_dpp: float,
     *         discount_percent: float,
     *         discount_amount: float,
     *         line_dpp_after_discount: float,
     *         ppn: float,
     *         total: float
     *     }>,
     *     products_total: float,
     *     order_discount: float,
     *     order_discount_label: ?string,
     *     shipping: float,
     *     service_fee: float,
     *     grand_total: float,
     *     note: string
     * }
     */
    public static function fromOrder(Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);

        $usesPpn = $order->usesPpn();
        $taxPercent = $usesPpn ? (float) Setting::taxPercent() : 0.0;
        $taxRate = $taxPercent / 100;

        $lines = [];
        foreach ($order->items->values() as $index => $item) {
            $qty = max(1, (int) $item->displayQuantity());
            $unitDpp = (float) $item->unitPriceBeforeDiscount();
            $unitAfter = (float) $item->unitPriceAfterDiscount();

            $lineDpp = round($unitDpp * $qty);
            $lineAfter = round(min($unitDpp, $unitAfter) * $qty);
            $discount = max(0.0, $lineDpp - $lineAfter);
            $ppn = round($lineAfter * $taxRate);

            $lines[] = [
                'item' => $item,
                'no' => $index + 1,
                'name' => (string) ($item->product?->display_name ?: $item->product?->name ?: 'Produk'),
                'code' => $item->product?->code,
                'quantity_label' => $item->orderedQuantityDescription(),
                'quantity' => $qty,
                'unit_price_incl' => round($unitDpp * (1 + $taxRate)),
                'unit_price_dpp' => round($unitDpp),
                'line_dpp' => $lineDpp,
                'discount_percent' => $discount > 0 ? (float) $item->unitDiscountPercent() : 0.0,
                'discount_amount' => $discount,
                'line_dpp_after_discount' => $lineAfter,
                'ppn' => $ppn,
                'total' => $lineAfter + $ppn,
            ];
        }

        $shipping = (float) ($order->shipping_cost ?? 0);
        $serviceFee = (float) ($order->payment_fee ?? 0);
        $grandTotal = round((float) $order->total_amount);

        // Diskon lama di level pesanan (order lama sebelum diskon per baris).
        $orderDiscount = round((float) ($order->discount_amount ?? 0) * (1 + $taxRate));

        $productsTotal = array_sum(array_column($lines, 'total'));
        $difference = $grandTotal - ($productsTotal - $orderDiscount + $shipping + $serviceFee);
        if ($lines !== [] && $difference != 0.0 && abs($difference) <= self::ROUNDING_TOLERANCE) {
            $last = array_key_last($lines);
            if ($taxRate > 0) {
                $lines[$last]['ppn'] += $difference;
            } else {
                $lines[$last]['line_dpp_after_discount'] += $difference;
            }
            $lines[$last]['total'] += $difference;
            $productsTotal += $difference;
            $difference = 0.0;
        }

        // Order lama dengan data harga tidak konsisten (mis. harga item tersimpan termasuk PPN): rincian
        // per baris tidak bisa direkonstruksi, tampilan memakai format sederhana.
        $reliable = $lines !== [] && abs($difference) < 0.5;

        $taxLabel = $usesPpn ? TaxAwarePrice::ppnLabel($taxPercent) : 'Tanpa PPN';

        return [
            'reliable' => $reliable,
            'uses_ppn' => $usesPpn && $taxPercent > 0,
            'tax_percent' => $taxPercent,
            'tax_label' => $taxLabel,
            'lines' => $lines,
            'products_total' => $productsTotal,
            'order_discount' => $orderDiscount,
            'order_discount_label' => $orderDiscount > 0 ? 'Diskon pesanan' : null,
            'shipping' => $shipping,
            'service_fee' => $serviceFee,
            'grand_total' => $grandTotal,
            'note' => $usesPpn && $taxPercent > 0
                ? 'Harga sudah termasuk ' . $taxLabel . '. Diskon dihitung dari harga sebelum PPN, lalu PPN dihitung dari harga setelah diskon.'
                : 'Harga tanpa PPN. Diskon dihitung dari harga per baris.',
        ];
    }

    public static function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
