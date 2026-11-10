<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductPrice;
use App\Support\Pricing\PricingContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Harga storefront mengikuti harga master hub terpilih; admin & job tetap harga global.
 * Butuh database MySQL yang sudah termigrasi (lihat WarehousePriceMasterTest).
 */
class StorefrontWarehousePricingTest extends TestCase
{
    use DatabaseTransactions;

    private Product $product;

    private Warehouse $hub;

    private Warehouse $otherHub;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $template = (array) DB::table('products')->first();
        $this->assertNotEmpty($template, 'Test butuh minimal satu produk di database uji.');
        $id = (string) Str::uuid();
        DB::table('products')->insert(array_merge($template, [
            'id' => $id,
            'code' => 'TSF-' . uniqid(),
            'slug' => uniqid('tsf-'),
            'name' => 'Produk Uji Harga Hub',
            'price' => 10000,
            'is_discount' => 0,
            'discount_price' => null,
            'status' => 'active',
            'sync_sources' => json_encode(['jubelio', 'qad']),
            'deleted_at' => null,
        ]));
        $this->product = Product::withoutGlobalScopes()->findOrFail($id);

        [$this->hub, $this->otherHub] = Warehouse::limit(2)->get()->all();

        WarehouseProductPrice::replaceCurrent([
            'warehouse_id' => $this->hub->id,
            'product_id' => $this->product->id,
            'price' => 12500,
        ]);
        PricingContext::current()->flush();
    }

    private function freshProduct(): Product
    {
        return Product::withoutGlobalScopes()->findOrFail($this->product->id);
    }

    public function test_without_context_price_is_global(): void
    {
        $this->assertEquals(10000, $this->freshProduct()->price);
        $this->assertFalse($this->freshProduct()->usesWarehousePrice());
    }

    public function test_storefront_context_uses_selected_hub_price(): void
    {
        $context = PricingContext::current();
        $context->enableStorefront();

        session(['selected_hub_id' => $this->hub->id]);
        $this->assertEquals(12500, $this->freshProduct()->price);
        $this->assertEquals(12500, $this->freshProduct()->final_price);

        // Hub lain tanpa harga master: harga global.
        session(['selected_hub_id' => $this->otherHub->id]);
        $this->assertEquals(10000, $this->freshProduct()->price);
    }

    public function test_product_detail_page_shows_hub_price(): void
    {
        $this->withSession(['selected_hub_id' => $this->hub->id])
            ->get(route('products.show', $this->product->slug))
            ->assertOk()
            ->assertSee('12.500');

        $this->withSession(['selected_hub_id' => $this->otherHub->id])
            ->get(route('products.show', $this->product->slug))
            ->assertOk()
            ->assertDontSee('12.500')
            ->assertSee('10.000');
    }

    public function test_product_detail_follows_requested_warehouse(): void
    {
        $this->withSession(['selected_hub_id' => $this->otherHub->id])
            ->get(route('products.show', [$this->product->slug, 'warehouse_id' => $this->hub->id]))
            ->assertOk()
            ->assertSee('12.500');
    }

    public function test_quick_view_shows_hub_price(): void
    {
        $this->withSession(['selected_hub_id' => $this->hub->id])
            ->get(route('products.quick-view', $this->product->slug))
            ->assertOk()
            ->assertSee('12.500');
    }

    public function test_admin_sees_global_price(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->withSession(['selected_hub_id' => $this->hub->id])
            ->get(route('admin.warehouses.show', ['warehouse' => $this->otherHub, 'search' => $this->product->code]))
            ->assertOk();

        // Middleware storefront tidak aktif di /admin, jadi konteks tetap kosong.
        $this->assertNull(PricingContext::current()->warehouseId());
    }

    public function test_cart_line_uses_its_own_warehouse_price(): void
    {
        $user = User::factory()->create(['role' => 'outlet', 'wa_verified_at' => now()]);
        $cart = Cart::create([
            'user_id' => $user->id,
            'cart_type' => 'regular',
            'product_id' => $this->product->id,
            'warehouse_id' => $this->hub->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(12500, $cart->displayUnitPrice());

        $cart->warehouse_id = $this->otherHub->id;
        $this->assertEquals(10000, $cart->displayUnitPrice());
    }

    public function test_order_item_locks_hub_price_from_context(): void
    {
        $order = (array) DB::table('orders')->first();
        $this->assertNotEmpty($order, 'Test butuh minimal satu order di database uji.');

        $item = PricingContext::current()->forWarehouse($this->hub->id, fn () => OrderItem::create([
            'order_id' => $order['id'],
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 12500,
            'subtotal' => 12500,
        ]));

        $this->assertEquals(12500, $item->catalog_price);
        $this->assertNotNull($item->price_master_id);

        // Tanpa konteks (admin/job): harga global, tanpa master.
        $plain = OrderItem::create([
            'order_id' => $order['id'],
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 10000,
            'subtotal' => 10000,
        ]);
        $this->assertEquals(10000, $plain->catalog_price);
        $this->assertNull($plain->price_master_id);
    }

    public function test_price_filter_and_sort_use_hub_price(): void
    {
        $query = fn () => Product::withoutGlobalScopes()->whereKey($this->product->id);

        PricingContext::current()->forWarehouse($this->hub->id, function () use ($query) {
            $this->assertTrue($query()->whereEffectivePrice('>=', 12000)->exists());
            $this->assertFalse($query()->whereEffectivePrice('<=', 11000)->exists());
            $this->assertNotEmpty($query()->orderByEffectivePrice('desc')->get());
        });

        PricingContext::current()->forWarehouse($this->otherHub->id, function () use ($query) {
            $this->assertFalse($query()->whereEffectivePrice('>=', 12000)->exists());
            $this->assertTrue($query()->whereEffectivePrice('<=', 11000)->exists());
        });
    }

    public function test_order_keeps_price_from_time_of_sale(): void
    {
        $order = (array) DB::table('orders')->first();
        $this->assertNotEmpty($order, 'Test butuh minimal satu order di database uji.');

        $item = PricingContext::current()->forWarehouse($this->hub->id, fn () => OrderItem::create([
            'order_id' => $order['id'],
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 12500,
            'subtotal' => 12500,
        ]));

        // Harga berubah setelah transaksi: harga global & harga hub.
        DB::table('products')->where('id', $this->product->id)->update(['price' => 99000]);
        WarehouseProductPrice::replaceCurrent(['warehouse_id' => $this->hub->id, 'product_id' => $this->product->id, 'price' => 55000]);
        PricingContext::current()->flush();

        PricingContext::current()->forWarehouse($this->hub->id, function () use ($item) {
            $fresh = OrderItem::with('product', 'order')->findOrFail($item->id);
            $this->assertEquals(55000, $fresh->product->price, 'Harga produk sekarang sudah berubah.');
            $this->assertEquals(12500, $fresh->catalogUnitPrice(), 'Harga katalog order tetap harga saat transaksi.');
            $this->assertEquals(12500, $fresh->price);
        });
    }
}
