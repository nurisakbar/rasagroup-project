<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderLineBreakdown;
use App\Support\TaxAwarePrice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Rincian per baris (diskon & PPN per baris) harus selalu berjumlah sama dengan total pesanan.
 * Butuh MySQL yang sudah termigrasi.
 */
class OrderLineBreakdownTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $user = User::factory()->create(['role' => User::ROLE_OUTLET, 'wa_verified_at' => now()]);
        $row = DB::table('orders')->first();
        $this->order = Order::findOrFail($row->id);
        $this->order->items()->delete();
        $this->order->forceFill(['user_id' => $user->id, 'order_type' => Order::TYPE_REGULAR, 'discount_amount' => 0, 'payment_fee' => 0])->save();
    }

    private function product(float $price): Product
    {
        $template = (array) DB::table('products')->first();
        $id = (string) Str::uuid();
        DB::table('products')->insert(array_merge($template, [
            'id' => $id, 'code' => 'TLB-' . uniqid(), 'slug' => uniqid('tlb-'), 'price' => $price,
            'large_unit' => null, 'units_per_large' => null, 'deleted_at' => null,
        ]));

        return Product::withoutGlobalScopes()->findOrFail($id);
    }

    /**
     * Buat item seperti checkout: harga jual = DPP katalog x (1 - diskon), harga katalog terkunci.
     */
    private function addItem(float $catalogIncl, int $qty, float $discountPercent = 0): void
    {
        $product = $this->product($catalogIncl);
        $sold = round(TaxAwarePrice::excludingTax($catalogIncl) * (1 - $discountPercent / 100), 2);
        OrderItem::create([
            'order_id' => $this->order->id, 'product_id' => $product->id, 'quantity' => $qty,
            'price' => $sold, 'subtotal' => $sold * $qty, 'catalog_price' => $catalogIncl,
        ]);
    }

    private function setTotals(bool $usesPpn, float $shipping = 0): void
    {
        $this->order->load('items');
        $dpp = $this->order->items->sum(fn ($i) => (float) $i->price * $i->quantity);
        $goods = $usesPpn ? $dpp * 1.11 : $dpp;
        $this->order->forceFill([
            'pakai_ppn' => $usesPpn, 'shipping_cost' => $shipping,
            'total_amount' => round($goods + $shipping),
        ])->save();
    }

    public function test_lines_with_discount_and_ppn_sum_to_order_total(): void
    {
        $this->addItem(111000, 4);         // tanpa diskon
        $this->addItem(166500, 4, 2);      // diskon 2%
        $this->addItem(185000, 1, 2);      // diskon 2%, DPP tidak bulat
        $this->setTotals(true, 15000);

        $b = OrderLineBreakdown::fromOrder($this->order->fresh());

        $this->assertTrue($b['reliable']);
        $this->assertTrue($b['uses_ppn']);
        [$plain, $disc] = $b['lines'];

        $this->assertEquals(111000, $plain['unit_price_incl']);
        $this->assertEquals(400000, $plain['line_dpp']);
        $this->assertEquals(0, $plain['discount_amount']);
        $this->assertEquals(44000, $plain['ppn']);
        $this->assertEquals(444000, $plain['total']);

        // Diskon dari harga sebelum PPN, PPN dari harga setelah diskon.
        $this->assertEquals(600000, $disc['line_dpp']);
        $this->assertEquals(12000, $disc['discount_amount']);
        $this->assertEquals(2.0, $disc['discount_percent']);
        $this->assertEquals(64680, $disc['ppn']);
        $this->assertEquals(652680, $disc['total']);

        // Jumlah baris + ongkir = total pesanan, tanpa selisih pembulatan.
        $this->assertEquals($b['grand_total'], $b['products_total'] + $b['shipping']);
        $this->assertEquals(array_sum(array_column($b['lines'], 'total')), $b['products_total']);
    }

    public function test_order_without_ppn_has_no_tax_per_line(): void
    {
        $this->addItem(111000, 2, 2);
        $this->setTotals(false);

        $b = OrderLineBreakdown::fromOrder($this->order->fresh());

        $this->assertTrue($b['reliable']);
        $this->assertFalse($b['uses_ppn']);
        $this->assertEquals(0, $b['lines'][0]['ppn']);
        $this->assertSame('Tanpa PPN', $b['tax_label']);
        $this->assertEquals($b['grand_total'], $b['products_total']);
    }

    public function test_inconsistent_legacy_order_falls_back(): void
    {
        $this->addItem(111000, 2);
        $this->setTotals(true);
        // Data lama: total tidak sesuai item.
        $this->order->forceFill(['total_amount' => 50000])->save();

        $this->assertFalse(OrderLineBreakdown::fromOrder($this->order->fresh())['reliable']);
    }

    public function test_pages_show_per_line_breakdown(): void
    {
        $this->addItem(166500, 4, 2);
        $this->setTotals(true);
        $order = $this->order->fresh(['user']);

        $this->actingAs($order->user)->get(route('buyer.orders.show', $order))
            ->assertOk()
            ->assertSee('sebelum PPN')
            ->assertSee('Rp 652.680')
            ->assertSee('Diskon dihitung dari harga sebelum PPN')
            ->assertDontSee('Total PPN');

        $html = (new \App\Mail\OrderCreatedMail($order))->render();
        $this->assertStringContainsString('Rincian Produk', $html);
        $this->assertStringContainsString('Rp 64.680', $html);
    }
}
