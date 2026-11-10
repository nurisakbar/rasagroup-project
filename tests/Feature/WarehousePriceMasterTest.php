<?php

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductPrice;
use App\Models\WarehouseStock;
use App\Support\Pricing\WarehousePriceResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Butuh skema MySQL: sebagian migrasi memakai MODIFY ENUM yang tidak jalan di SQLite.
 * Jalankan dengan DB_CONNECTION=mysql dan DB_DATABASE mengarah ke database uji yang sudah termigrasi.
 */
class WarehousePriceMasterTest extends TestCase
{
    use DatabaseTransactions;

    private Product $product;

    private Warehouse $warehouse;

    private Warehouse $otherWarehouse;

    private User $admin;

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
            'code' => 'TST-' . uniqid(),
            'slug' => uniqid('tst-'),
            'price' => 10000,
            'status' => 'active',
            'deleted_at' => null,
        ]));
        $this->product = Product::findOrFail($id);

        [$this->warehouse, $this->otherWarehouse] = Warehouse::limit(2)->get()->all();
        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
    }

    private function master(float $price, string $from, ?string $to = null, ?Warehouse $warehouse = null): WarehouseProductPrice
    {
        return WarehouseProductPrice::create([
            'warehouse_id' => ($warehouse ?? $this->warehouse)->id,
            'product_id' => $this->product->id,
            'price' => $price,
            'effective_from' => now()->modify($from),
            'effective_to' => $to ? now()->modify($to) : null,
        ]);
    }

    private function resolve(?Warehouse $warehouse = null): array
    {
        return app(WarehousePriceResolver::class)->catalogPrice($this->product, ($warehouse ?? $this->warehouse)->id);
    }

    private function orderItem(array $attributes = []): OrderItem
    {
        $order = (array) DB::table('orders')->first();
        $this->assertNotEmpty($order, 'Test butuh minimal satu order di database uji.');

        return OrderItem::create(array_merge([
            'order_id' => $order['id'],
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 9000,
            'subtotal' => 9000,
        ], $attributes));
    }

    public function test_without_master_uses_global_price(): void
    {
        $this->assertSame(['price' => 10000.0, 'price_master_id' => null], $this->resolve());
    }

    public function test_master_price_applies_only_to_its_warehouse(): void
    {
        $row = $this->master(12000, '-1 day');

        $this->assertSame(['price' => 12000.0, 'price_master_id' => $row->id], $this->resolve());
        $this->assertSame(10000.0, $this->resolve($this->otherWarehouse)['price']);
    }

    public function test_latest_effective_row_wins_and_schedule_waits(): void
    {
        $this->master(12000, '-10 days');
        $this->master(13000, '-1 day');
        $this->master(15000, '+2 days');

        $this->assertSame(13000.0, $this->resolve()['price']);
    }

    public function test_expired_promo_falls_back_to_previous_price(): void
    {
        $this->master(12000, '-10 days');
        $this->master(8000, '-5 days', '-1 day');

        $this->assertSame(12000.0, $this->resolve()['price']);

        $this->master(7000, '-1 hour', '+1 day');
        $this->assertSame(7000.0, $this->resolve()['price']);
    }

    public function test_order_item_locks_catalog_price_at_sale(): void
    {
        $item = $this->orderItem();
        $this->assertSame('10000.00', $item->fresh()->catalog_price);

        $this->product->update(['price' => 25000]);
        $this->master(30000, '-1 hour');

        $this->assertSame(10000.0, $item->fresh()->catalogUnitPrice());
    }

    public function test_explicit_catalog_price_is_kept(): void
    {
        $row = $this->master(12000, '-1 day');
        $item = $this->orderItem(['catalog_price' => 12000, 'price_master_id' => $row->id]);

        $this->assertSame('12000.00', $item->fresh()->catalog_price);
        $this->assertTrue($item->fresh()->priceMaster->is($row));
    }

    public function test_admin_sets_price_and_sees_history(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.warehouse-prices.store', $this->warehouse), [
                'product_id' => $this->product->id,
                'price' => 11500,
                'note' => 'Penyesuaian ongkos',
            ])->assertOk();

        $this->assertSame(11500.0, $this->resolve()['price']);
        $row = WarehouseProductPrice::where('product_id', $this->product->id)->firstOrFail();
        $this->assertSame($this->admin->id, $row->created_by);
        $this->assertSame('manual', $row->source);

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.warehouse-prices.history', [$this->warehouse, $this->product]))
            ->assertOk()
            ->assertJsonPath('rows.0.status', 'Berlaku')
            ->assertJsonPath('rows.0.note', 'Penyesuaian ongkos');
    }

    public function test_new_price_applies_now_and_replaces_previous(): void
    {
        $url = route('admin.warehouse-prices.store', $this->warehouse);

        // Tanggal dari klien diabaikan: harga baru selalu berlaku sekarang tanpa batas waktu.
        $this->actingAs($this->admin, 'admin')->postJson($url, [
            'product_id' => $this->product->id, 'price' => 11500,
            'effective_from' => '2030-01-01T07:00', 'effective_to' => '2030-02-01T07:00',
        ])->assertOk();
        $this->actingAs($this->admin, 'admin')->postJson($url, ['product_id' => $this->product->id, 'price' => 12500])->assertOk();

        $this->assertSame(12500.0, $this->resolve()['price']);

        $rows = WarehouseProductPrice::where('product_id', $this->product->id)->get()->keyBy(fn ($row) => (int) $row->price);
        $this->assertNull($rows[12500]->effective_to);
        $this->assertTrue($rows[12500]->effective_from->lte(now()));
        $this->assertNotNull($rows[11500]->effective_to, 'Harga lama ditutup saat digantikan.');

        $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.warehouse-prices.history', [$this->warehouse, $this->product]))
            ->assertOk()
            ->assertJsonPath('rows.0.price', 'Rp 12.500')
            ->assertJsonPath('rows.0.status', 'Berlaku')
            ->assertJsonPath('rows.1.status', 'Tidak berlaku');
    }

    public function test_store_rejects_invalid_input(): void
    {
        $url = route('admin.warehouse-prices.store', $this->warehouse);

        $this->actingAs($this->admin, 'admin')->postJson($url, ['product_id' => $this->product->id, 'price' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('price');
        $this->actingAs($this->admin, 'admin')->postJson($url, ['product_id' => $this->product->id])
            ->assertUnprocessable()->assertJsonValidationErrors('price');

        $this->assertSame(0, WarehouseProductPrice::where('product_id', $this->product->id)->count());
    }

    public function test_only_scheduled_price_can_be_cancelled(): void
    {
        $active = $this->master(12000, '-1 day');
        $scheduled = $this->master(15000, '+2 days');

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.warehouse-prices.destroy', [$this->warehouse, $active]))
            ->assertUnprocessable();
        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.warehouse-prices.destroy', [$this->warehouse, $scheduled]))
            ->assertOk();

        $this->assertNotNull($active->fresh());
        $this->assertNull($scheduled->fresh());
    }

    public function test_access_limited_to_allowed_roles(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'wa_verified_at' => now()]);

        $this->actingAs($sales, 'admin')->get(route('admin.warehouse-prices.index'))->assertForbidden();
        $this->actingAs($sales, 'admin')->postJson(route('admin.warehouse-prices.store', $this->warehouse), [
            'product_id' => $this->product->id, 'price' => 1000,
        ])->assertForbidden();

        $ecommerce = User::factory()->create(['role' => 'ecommerce', 'wa_verified_at' => now()]);
        $this->actingAs($ecommerce, 'admin')->get(route('admin.warehouse-prices.index', ['warehouse_id' => $this->warehouse->id]))
            ->assertOk()->assertSee($this->warehouse->name);
    }

    public function test_datatable_shows_effective_master_price(): void
    {
        $this->master(12345, '-1 day');

        $response = $this->actingAs($this->admin, 'admin')->getJson(
            route('admin.warehouse-prices.datatable', [$this->warehouse, 'only_master' => '1', 'search' => ['value' => $this->product->code]]),
            ['X-Requested-With' => 'XMLHttpRequest']
        )->assertOk();

        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Rp 12.345', $response->json('data.0.effective_price'));
    }

    private function csv(array $rows): UploadedFile
    {
        $lines = ['warehouse_id,nama_warehouse,kode_produk,nama_produk,harga_global,harga_berlaku,harga_baru,catatan'];
        foreach ($rows as $row) {
            $lines[] = implode(',', $row);
        }
        $path = tempnam(sys_get_temp_dir(), 'wpp') . '.csv';
        file_put_contents($path, implode("\n", $lines));

        return new UploadedFile($path, 'harga.csv', 'text/csv', null, true);
    }

    public function test_import_saves_rows_with_new_price_only(): void
    {
        $code = $this->product->code;
        $file = $this->csv([
            [$this->warehouse->id, 'A', $code, 'P', 10000, 10000, 14000, 'Import uji'],
            [$this->otherWarehouse->id, 'B', $code, 'P', 10000, 10000, 16000, ''],
            [$this->warehouse->id, 'A', $code, 'P', 10000, 10000, '', ''],
        ]);
        $previous = $this->master(12000, '-1 day');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.warehouse-prices.import'), ['file' => $file])
            ->assertSessionHas('success');

        $this->assertSame(14000.0, $this->resolve()['price']);
        $this->assertSame(16000.0, $this->resolve($this->otherWarehouse)['price']);
        $this->assertSame(2, WarehouseProductPrice::where('product_id', $this->product->id)->where('source', 'import')->count());
        $this->assertNotNull($previous->fresh()->effective_to, 'Harga lama ditutup oleh import.');
    }

    public function test_import_is_all_or_nothing(): void
    {
        $file = $this->csv([
            [$this->warehouse->id, 'A', $this->product->code, 'P', 10000, 10000, 14000, ''],
            [$this->warehouse->id, 'A', 'KODE-TIDAK-ADA', 'P', 10000, 10000, 15000, ''],
            ['bukan-warehouse', 'A', $this->product->code, 'P', 10000, 10000, 15000, ''],
            [$this->warehouse->id, 'A', $this->product->code, 'P', 10000, 10000, 15000, ''],
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.warehouse-prices.import'), ['file' => $file])
            ->assertSessionHas('error')
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 3);

        $this->assertSame(0, WarehouseProductPrice::where('product_id', $this->product->id)->count());
    }

    public function test_export_lists_current_warehouse_price(): void
    {
        $this->master(12000, '-1 day');

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.warehouse-prices.export', $this->warehouse));
        $response->assertOk();

        $rows = \Maatwebsite\Excel\Facades\Excel::toArray(
            new class implements \Maatwebsite\Excel\Concerns\ToArray { public function array(array $a) { return $a; } },
            $response->baseResponse->getFile()->getPathname(), null, \Maatwebsite\Excel\Excel::XLSX
        )[0];
        $row = collect($rows)->first(fn ($r) => $r[2] === $this->product->code);

        $this->assertSame($this->warehouse->id, $row[0]);
        $this->assertEquals(10000, $row[4]);
        $this->assertEquals(12000, $row[5]);
    }

    public function test_warehouse_stock_tab_shows_hub_price_and_editor(): void
    {
        WarehouseStock::create(['warehouse_id' => $this->warehouse->id, 'product_id' => $this->product->id, 'stock' => 5]);
        $search = ['warehouse' => $this->warehouse, 'search' => $this->product->code];

        // Tanpa harga master: harga global + tombol Set Harga.
        $this->actingAs($this->admin, 'admin')->get(route('admin.warehouses.show', $search))
            ->assertOk()
            ->assertSee('Rp 10.000')
            ->assertSee('btn-set-price', false)
            ->assertSee('id="price-modal"', false);

        $this->master(12500, '-1 hour');

        $this->actingAs($this->admin, 'admin')->get(route('admin.warehouses.show', $search))
            ->assertOk()
            ->assertSee('Rp 12.500')
            ->assertDontSee('Rp 10.000');

        // Peran tanpa hak master harga tidak melihat tombol maupun modal.
        $finance = User::factory()->create(['role' => 'finance', 'wa_verified_at' => now()]);
        $response = $this->actingAs($finance, 'admin')->get(route('admin.warehouses.show', $search));
        if ($response->status() === 200) {
            $response->assertDontSee('btn-set-price', false)->assertDontSee('id="price-modal"', false);
        } else {
            $this->assertContains($response->status(), [302, 403]);
        }
    }
}
