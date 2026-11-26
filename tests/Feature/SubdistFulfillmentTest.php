<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\SubdistStockService;
use App\Support\Pricing\PricingContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Order subdist: sumber stok = gudang distributor, stok terpotong otomatis, tanpa sinkron QAD/Jubelio/WMS.
 * Butuh MySQL yang sudah termigrasi.
 */
class SubdistFulfillmentTest extends TestCase
{
    use DatabaseTransactions;

    private User $distributor;

    private User $subdist;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->warehouse = Warehouse::create(['name' => 'Gudang Distributor Uji', 'slug' => 'gudang-dist-uji-' . uniqid(), 'is_active' => true, 'target_role' => ['distributor']]);
        $this->distributor = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'warehouse_id' => $this->warehouse->id, 'is_active' => true, 'wa_verified_at' => now()]);
        $this->distributor->refresh();
        $this->subdist = User::factory()->create([
            'role' => User::ROLE_SUBDIST, 'sales_code' => $this->distributor->distributor_sales_code, 'wa_verified_at' => now(),
        ]);
    }

    private function product(): Product
    {
        $template = (array) DB::table('products')->first();
        $id = (string) Str::uuid();
        DB::table('products')->insert(array_merge($template, [
            'id' => $id, 'code' => 'SDT-' . uniqid(), 'slug' => uniqid('sdt-'), 'deleted_at' => null,
        ]));

        return Product::withoutGlobalScopes()->findOrFail($id);
    }

    private function orderWithItem(Product $product, int $qty): Order
    {
        $order = Order::findOrFail(DB::table('orders')->first()->id);
        $order->items()->delete();
        $order->forceFill([
            'user_id' => $this->subdist->id, 'source_warehouse_id' => $this->warehouse->id,
            'fulfilled_by_distributor' => true, 'order_status' => 'pending',
        ])->save();
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $qty, 'price' => 1000, 'subtotal' => 1000 * $qty]);

        return $order->fresh();
    }

    public function test_subdist_shops_from_distributor_warehouse(): void
    {
        $this->assertTrue($this->warehouse->is($this->subdist->subdistWarehouse()));
        $this->assertTrue($this->warehouse->is($this->subdist->registeredShoppingHub()));
    }

    public function test_subdist_is_blocked_when_distributor_warehouse_is_not_ready(): void
    {
        $this->warehouse->update(['is_active' => false]);

        $this->assertNull($this->subdist->fresh()->registeredShoppingHub());
        $this->assertNotNull($this->subdist->fresh()->subdistFulfillmentProblem());
    }

    public function test_subdist_without_distributor_has_no_warehouse(): void
    {
        $orphan = User::factory()->create(['role' => User::ROLE_SUBDIST, 'sales_code' => 'DST99999', 'wa_verified_at' => now()]);

        $this->assertNull($orphan->subdistWarehouse());
        $this->assertNotNull($orphan->subdistFulfillmentProblem());
    }

    public function test_subdist_price_ignores_warehouse_price_context(): void
    {
        $this->actingAs($this->subdist);
        PricingContext::current()->useWarehouse((string) $this->warehouse->id);

        $this->assertNull(PricingContext::current()->warehouseId());
    }

    public function test_deduct_and_restore_stock_with_history(): void
    {
        $product = $this->product();
        $stock = WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'stock' => 10]);
        $order = $this->orderWithItem($product, 4);
        $service = new SubdistStockService();

        $service->deduct($order, (string) $product->id, 4, 'Produk Uji');
        $this->assertSame(6, (int) $stock->fresh()->stock);
        $this->assertDatabaseHas('warehouse_stock_histories', ['order_id' => $order->id, 'quantity_added' => -4, 'stock_before' => 10, 'stock_after' => 6]);

        $service->restore($order);
        $service->restore($order); // idempoten: tidak boleh menambah dua kali
        $this->assertSame(10, (int) $stock->fresh()->stock);
    }

    public function test_deduct_rejects_insufficient_stock(): void
    {
        $product = $this->product();
        WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'stock' => 2]);
        $order = $this->orderWithItem($product, 3);

        $this->expectException(\RuntimeException::class);
        (new SubdistStockService())->deduct($order, (string) $product->id, 3, 'Produk Uji');
    }

    public function test_cancelling_subdist_order_restores_stock(): void
    {
        $product = $this->product();
        $stock = WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'stock' => 5]);
        $order = $this->orderWithItem($product, 2);
        (new SubdistStockService())->deduct($order, (string) $product->id, 2);

        $order->update(['order_status' => 'cancelled']);

        $this->assertSame(5, (int) $stock->fresh()->stock);
    }

    public function test_subdist_order_skips_qad_jubelio_and_wms(): void
    {
        $order = $this->orderWithItem($this->product(), 1);

        $this->assertTrue($order->skipsExternalSync());
        $this->assertFalse($order->shouldSyncToQad());
        $this->assertFalse($order->shouldSyncToJubelio());
        $this->assertFalse($order->shouldSyncToWms());
    }
}
