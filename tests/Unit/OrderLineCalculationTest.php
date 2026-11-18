<?php

namespace Tests\Unit;

use App\Support\OrderLineBreakdown;
use PHPUnit\Framework\TestCase;

/**
 * Rumus invoice per baris: diskon dari DPP (harga sebelum PPN), PPN dari DPP setelah diskon.
 * Murni (tanpa database) sehingga selalu dijalankan.
 */
class OrderLineCalculationTest extends TestCase
{
    private const PPN = 0.11;

    private function line(float $unit, int $qty, float $discountPercent = 0.0, float $rate = self::PPN): array
    {
        return OrderLineBreakdown::calculateLine($unit, $unit * (1 - $discountPercent / 100), $qty, $rate);
    }

    public function test_product_without_discount(): void
    {
        $l = $this->line(218018.02, 4);

        $this->assertSame(872072.0, $l['line_dpp']);
        $this->assertSame(0.0, $l['discount_amount']);
        $this->assertSame(872072.0, $l['line_dpp_after_discount']);
        $this->assertSame(95928.0, $l['ppn']);   // 11% x 872.072 = 95.927,92 dibulatkan
        $this->assertSame(968000.0, $l['total']);
    }

    public function test_product_with_two_and_a_half_percent_discount(): void
    {
        $l = $this->line(100000, 2, 2.5);

        $this->assertSame(200000.0, $l['line_dpp']);
        $this->assertSame(5000.0, $l['discount_amount']);
        $this->assertSame(195000.0, $l['line_dpp_after_discount']);
        $this->assertSame(21450.0, $l['ppn']);
        $this->assertSame(216450.0, $l['total']);
    }

    public function test_nominal_discount_per_unit(): void
    {
        // Potongan Rp10.000 per satuan: 90.000 x 3 = 270.000 setelah diskon.
        $l = OrderLineBreakdown::calculateLine(100000, 90000, 3, self::PPN);

        $this->assertSame(300000.0, $l['line_dpp']);
        $this->assertSame(30000.0, $l['discount_amount']);
        $this->assertSame(270000.0, $l['line_dpp_after_discount']);
        $this->assertSame(29700.0, $l['ppn']);
        $this->assertSame(299700.0, $l['total']);
    }

    public function test_quantity_scales_linearly(): void
    {
        $one = $this->line(100000, 1, 2.5);
        $five = $this->line(100000, 5, 2.5);

        $this->assertSame($one['total'] * 5, $five['total']);
        $this->assertSame($one['discount_amount'] * 5, $five['discount_amount']);
    }

    public function test_multiple_discounts_use_final_price_once(): void
    {
        // Dua diskon berturut-turut (10% lalu 2,5%) sudah tercermin di harga akhir per satuan:
        // 100.000 x 0,9 x 0,975 = 87.750. Diskon hanya dihitung satu kali dari harga sebelum diskon.
        $final = 100000 * 0.9 * 0.975;
        $l = OrderLineBreakdown::calculateLine(100000, $final, 2, self::PPN);

        $this->assertSame(200000.0, $l['line_dpp']);
        $this->assertSame(24500.0, $l['discount_amount']);
        $this->assertSame(175500.0, $l['line_dpp_after_discount']);
        $this->assertSame(19305.0, $l['ppn']);
        $this->assertSame(194805.0, $l['total']);
    }

    public function test_price_after_discount_never_exceeds_price_before(): void
    {
        $l = OrderLineBreakdown::calculateLine(100000, 120000, 2, self::PPN);

        $this->assertSame(0.0, $l['discount_amount']);
        $this->assertSame(200000.0, $l['line_dpp_after_discount']);
    }

    public function test_invoice_with_several_products_shipping_and_extra_adjustment(): void
    {
        $lines = [
            $this->line(100000, 2, 2.5),    // 216.450
            $this->line(100000, 1, 2.5),    // 108.225
            $this->line(218018.02, 4),      // 968.000
        ];
        $products = array_sum(array_column($lines, 'total'));
        $this->assertSame(1292675.0, $products);

        // Tanpa selisih: total pembayaran = subtotal + ongkir + biaya layanan; diskon tidak dikurangkan lagi.
        $grand = $products + 25000 + 1000;
        $this->assertSame(0.0, OrderLineBreakdown::difference($grand, $products, 0, 25000, 1000));

        // Penyesuaian tambahan: total tersimpan lebih rendah Rp2.000 -> selisih -2.000 (tampil sebagai Penyesuaian).
        $this->assertSame(-2000.0, OrderLineBreakdown::difference($grand - 2000, $products, 0, 25000, 1000));
    }

    public function test_discount_is_not_subtracted_again_from_subtotal(): void
    {
        $l = $this->line(100000, 2, 2.5);

        // Subtotal = jumlah total baris; total sudah memuat diskon dan PPN setelah diskon.
        $this->assertSame(216450.0, $l['total']);
        $this->assertSame(0.0, OrderLineBreakdown::difference(216450, $l['total'], 0, 0, 0));
        $this->assertNotSame(0.0, OrderLineBreakdown::difference(216450, $l['total'] - $l['discount_amount'], 0, 0, 0));
    }

    public function test_zero_percent_ppn(): void
    {
        $l = $this->line(100000, 2, 2.5, 0.0);

        $this->assertSame(195000.0, $l['line_dpp_after_discount']);
        $this->assertSame(0.0, $l['ppn']);
        $this->assertSame(195000.0, $l['total']);
    }

    public function test_ppn_is_taken_once_from_discounted_base(): void
    {
        $l = $this->line(100000, 2, 2.5);

        $this->assertSame(round($l['line_dpp_after_discount'] * self::PPN), $l['ppn']);
        $this->assertSame($l['line_dpp_after_discount'] + $l['ppn'], $l['total']);
    }
}
