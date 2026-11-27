<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\DiscountService;

/**
 * Rincian harga per baris pesanan untuk tampilan (halaman pesanan, email, invoice, admin).
 *
 * Aturan: harga katalog sudah termasuk PPN. Diskon dihitung dari harga sebelum PPN (DPP), lalu PPN
 * dihitung dari DPP setelah diskon. Diskon tidak mengurangi PPN untuk kedua kalinya.
 *
 * Per baris:  DPP = harga sebelum PPN x jumlah;  DPP setelah diskon = DPP - diskon;
 *             PPN = DPP setelah diskon x tarif;  total baris = DPP setelah diskon + PPN. Diskon dan PPN ditampilkan per baris, sehingga ringkasan cukup berisi
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
     *     adjustment: float,
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

            $calc = self::calculateLine($unitDpp, $unitAfter, $qty, $taxRate);
            $lineDpp = $calc['line_dpp'];
            $lineAfter = $calc['line_dpp_after_discount'];
            $discount = $calc['discount_amount'];
            $ppn = $calc['ppn'];

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
                'total' => $calc['total'],
            ];
        }

        $shipping = (float) ($order->shipping_cost ?? 0);
        $serviceFee = (float) ($order->payment_fee ?? 0);
        $grandTotal = round((float) $order->total_amount);

        // Diskon lama di level pesanan (order lama sebelum diskon per baris).
        $orderDiscount = round((float) ($order->discount_amount ?? 0) * (1 + $taxRate));

        $productsTotal = array_sum(array_column($lines, 'total'));
        $difference = self::difference($grandTotal, $productsTotal, $orderDiscount, $shipping, $serviceFee);

        // Order sebelum diskon MOQ dicatat per item: total sudah dipotong tetapi harga item belum.
        if ($difference < -self::ROUNDING_TOLERANCE && $taxRate > 0) {
            $rebuilt = self::withReconstructedMoqDiscount($order, $lines, $taxRate);
            if ($rebuilt !== null) {
                $rebuiltTotal = array_sum(array_column($rebuilt, 'total'));
                $rebuiltDifference = self::difference($grandTotal, $rebuiltTotal, $orderDiscount, $shipping, $serviceFee);
                // Dipakai hanya bila hasilnya cocok dengan total tersimpan (selisih pembulatan saja).
                if (abs($rebuiltDifference) <= self::ROUNDING_TOLERANCE) {
                    $lines = $rebuilt;
                    $productsTotal = $rebuiltTotal;
                    $difference = $rebuiltDifference;
                }
            }
        }
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
            // Selisih total tersimpan terhadap jumlah baris (0 bila konsisten); ditampilkan sebagai baris penyesuaian.
            'adjustment' => round($difference, 2),
            'note' => $usesPpn && $taxPercent > 0
                ? 'Harga sudah termasuk ' . $taxLabel . '. Diskon dihitung dari harga sebelum PPN, lalu PPN dihitung dari harga setelah diskon.'
                : 'Harga tanpa PPN. Diskon dihitung dari harga per baris.',
        ];
    }

    /**
     * Rumus satu baris (murni, tanpa database). Semua nilai dibulatkan ke rupiah penuh dengan round().
     *
     * @param  float  $unitDppBefore  harga satuan sebelum PPN dan sebelum diskon
     * @param  float  $unitDppAfter   harga satuan sebelum PPN setelah diskon (tidak boleh melebihi harga sebelum diskon)
     * @param  float  $taxRate        tarif PPN sebagai pecahan (0.11); 0 bila tanpa PPN
     * @return array{line_dpp: float, line_dpp_after_discount: float, discount_amount: float, ppn: float, total: float}
     */
    public static function calculateLine(float $unitDppBefore, float $unitDppAfter, int $qty, float $taxRate): array
    {
        $qty = max(1, $qty);
        $lineDpp = round($unitDppBefore * $qty);
        $lineAfter = round(min($unitDppBefore, $unitDppAfter) * $qty);
        $ppn = round($lineAfter * $taxRate);

        return [
            'line_dpp' => $lineDpp,
            'line_dpp_after_discount' => $lineAfter,
            'discount_amount' => max(0.0, $lineDpp - $lineAfter),
            'ppn' => $ppn,
            'total' => $lineAfter + $ppn,
        ];
    }

    /**
     * Selisih total pesanan tersimpan terhadap jumlah baris + ongkir + biaya layanan - diskon pesanan.
     * Diskon sudah ada di total tiap baris, jadi tidak dikurangkan lagi pada subtotal.
     */
    public static function difference(float $grandTotal, float $productsTotal, float $orderDiscount, float $shipping, float $serviceFee): float
    {
        return $grandTotal - ($productsTotal - $orderDiscount + $shipping + $serviceFee);
    }

    /**
     * Rekonstruksi diskon MOQ (kode sales) per baris untuk order lama yang diskonnya hanya mengurangi total.
     * Aturan sama dengan DiscountService (tier per grup kategori, karton dibulatkan ke bawah).
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>|null  null bila tidak berlaku
     */
    private static function withReconstructedMoqDiscount(Order $order, array $lines, float $taxRate): ?array
    {
        $user = $order->user;
        if (! $user || trim((string) $order->sales_code) === '') {
            return null;
        }

        foreach ($lines as $line) {
            if ($line['discount_amount'] > 0) {
                return null;
            }
        }

        $service = new DiscountService();
        if (! $service->isEligible($order->sales_code, $user)) {
            return null;
        }

        $order->loadMissing(['items.product.category', 'items.product.brand']);
        $carts = $order->items->map(fn (OrderItem $item) => (object) [
            'product' => $item->product,
            'quantity' => (int) $item->quantity,
        ]);
        $cartons = $service->cartonsPerGroup($carts);

        $changed = false;
        foreach ($lines as $index => $line) {
            $item = $line['item'];
            $group = $item->product ? $service->getProductCategoryGroup($item->product) : null;
            $percent = $group ? $service->tierPercentage($user, $group, (int) ($cartons[$group] ?? 0), $item->product) : 0.0;
            if ($percent <= 0) {
                continue;
            }

            $unitAfter = round($line['unit_price_dpp'] * (1 - $percent / 100), 2);
            $calc = self::calculateLine($line['unit_price_dpp'], $unitAfter, $line['quantity'], $taxRate);

            $lines[$index]['discount_percent'] = $percent;
            $lines[$index]['discount_amount'] = $calc['discount_amount'];
            $lines[$index]['line_dpp_after_discount'] = $calc['line_dpp_after_discount'];
            $lines[$index]['ppn'] = $calc['ppn'];
            $lines[$index]['total'] = $calc['total'];
            $changed = true;
        }

        return $changed ? $lines : null;
    }

    public static function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
