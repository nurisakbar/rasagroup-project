<?php

namespace Tests\Feature;

use App\Models\DistributorApiClient;
use App\Models\DistributorExternalSale;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DistributorExternalSalesReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Butuh skema MySQL: sebagian migrasi memakai MODIFY ENUM yang tidak jalan di SQLite.
 * Jalankan dengan DB_CONNECTION=mysql dan DB_DATABASE mengarah ke database uji yang sudah termigrasi.
 */
class DistributorExternalSalesApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $distributor;

    private string $apiKey;

    private string $productCode;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        Queue::fake();

        $this->distributor = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'name' => 'PT Mitra Rasa', 'is_active' => true]);
        $this->apiKey = DistributorApiClient::issueFor($this->distributor, 'ERP')['plain_key'];

        $this->productCode = 'TST-' . uniqid();
        DB::table('products')->insert($this->productRow(['code' => $this->productCode]));
    }

    private function productRow(array $overrides): array
    {
        $template = (array) DB::table('products')->first();
        $this->assertNotEmpty($template, 'Test butuh minimal satu produk di database uji.');

        return array_merge($template, ['id' => (string) \Illuminate\Support\Str::uuid(), 'slug' => uniqid('tst-'), 'alternate_codes' => null], $overrides);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'external_order_number' => 'SO/2026/001',
            'sold_at' => '2026-11-07T10:30:00+07:00',
            'customer' => ['code' => 'CUST-1', 'name' => 'Toko Maju'],
            'items' => [
                ['product_code' => $this->productCode, 'quantity' => 10, 'uom' => 'CTN', 'unit_price' => 150000],
            ],
            'tax' => 165000,
            'total' => 1665000,
        ], $overrides);
    }

    private function send(array $payload, ?string $key = null)
    {
        return $this->postJson('/api/v1/distributor/sales', $payload, ['X-API-KEY' => $key ?? $this->apiKey]);
    }

    public function test_requires_valid_active_key(): void
    {
        $this->postJson('/api/v1/distributor/sales', $this->payload())->assertUnauthorized();
        $this->send($this->payload(), 'rdk_salah')->assertForbidden();

        DistributorApiClient::where('user_id', $this->distributor->id)->update(['is_active' => false]);
        $this->send($this->payload())->assertForbidden();

        $this->assertSame(0, DistributorExternalSale::where('distributor_id', $this->distributor->id)->count());
    }

    public function test_key_stops_working_when_distributor_is_inactive_or_deleted(): void
    {
        $this->distributor->update(['is_active' => false]);
        $this->send($this->payload())->assertForbidden();

        $this->distributor->update(['is_active' => true]);
        $this->distributor->delete();
        $this->send($this->payload())->assertForbidden();
    }

    public function test_key_is_stored_hashed_only(): void
    {
        $client = DistributorApiClient::where('user_id', $this->distributor->id)->first();

        $this->assertNotSame($this->apiKey, $client->key_hash);
        $this->assertSame(hash('sha256', $this->apiKey), $client->key_hash);
        $this->assertStringStartsWith($client->key_prefix, $this->apiKey);
    }

    public function test_records_sale_for_key_owner_with_external_price(): void
    {
        $this->send($this->payload(['distributor_id' => 'id-lain']))
            ->assertCreated()
            ->assertJsonPath('data.unmapped_product_codes', []);

        $sale = DistributorExternalSale::with('items')->where('external_order_number', 'SO/2026/001')->firstOrFail();

        $this->assertSame($this->distributor->id, $sale->distributor_id);
        $this->assertSame('1665000.00', $sale->total);
        $this->assertSame('1500000.00', $sale->subtotal);
        $this->assertSame('confirmed', $sale->status);
        $this->assertSame('150000.00', $sale->items->first()->unit_price);
        $this->assertNotNull($sale->items->first()->product_id);
    }

    public function test_recording_does_not_touch_orders_stock_or_jobs(): void
    {
        $orders = Order::withTrashed()->count();
        $stock = DB::table('warehouse_stocks')->sum('stock');

        $this->send($this->payload())->assertCreated();

        $this->assertSame($orders, Order::withTrashed()->count());
        $this->assertEquals($stock, DB::table('warehouse_stocks')->sum('stock'));
        Queue::assertNothingPushed();
    }

    public function test_resend_updates_instead_of_duplicating(): void
    {
        $this->send($this->payload())->assertCreated();

        $this->send($this->payload([
            'items' => [
                ['product_code' => $this->productCode, 'quantity' => 2, 'unit_price' => 100000],
                ['product_code' => 'KODE-LAIN', 'quantity' => 1, 'unit_price' => 50000],
            ],
            'tax' => 0,
            'total' => 250000,
        ]))->assertOk();

        $sales = DistributorExternalSale::with('items')->where('distributor_id', $this->distributor->id)->get();
        $this->assertCount(1, $sales);
        $this->assertCount(2, $sales->first()->items);
        $this->assertSame('250000.00', $sales->first()->total);
    }

    public function test_same_number_from_two_distributors_stays_separate(): void
    {
        $other = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'is_active' => true]);
        $otherKey = DistributorApiClient::issueFor($other, 'ERP')['plain_key'];

        $this->send($this->payload())->assertCreated();
        $this->send($this->payload(), $otherKey)->assertCreated();

        $this->assertSame(2, DistributorExternalSale::where('external_order_number', 'SO/2026/001')->count());
    }

    public function test_unknown_product_is_kept_and_flagged(): void
    {
        $this->send($this->payload([
            'items' => [['product_code' => 'TIDAK-DIKENAL', 'quantity' => 1, 'unit_price' => 1000]],
            'tax' => 0,
            'total' => 1000,
        ]))->assertCreated()->assertJsonPath('data.unmapped_product_codes', ['TIDAK-DIKENAL']);
    }

    public function test_alternate_code_maps_to_product(): void
    {
        DB::table('products')->insert($this->productRow(['code' => 'TST-UTAMA-' . uniqid(), 'alternate_codes' => json_encode(['ALT-KHUSUS-77'])]));

        $this->send($this->payload([
            'items' => [['product_code' => 'alt-khusus-77', 'quantity' => 1, 'unit_price' => 1000]],
            'tax' => 0,
            'total' => 1000,
        ]))->assertCreated()->assertJsonPath('data.unmapped_product_codes', []);
    }

    public function test_rejects_total_mismatch_and_incomplete_data(): void
    {
        $this->send($this->payload(['total' => 999]))->assertUnprocessable()->assertJsonValidationErrors('total');
        $this->send($this->payload(['items' => []]))->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->send($this->payload(['customer' => []]))->assertUnprocessable()->assertJsonValidationErrors('customer');

        $this->assertSame(0, DistributorExternalSale::where('distributor_id', $this->distributor->id)->count());
    }

    public function test_validation_error_is_json_without_accept_header(): void
    {
        $response = $this->call('POST', '/api/v1/distributor/sales', [], [], [], [
            'HTTP_X-API-KEY' => $this->apiKey,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['total' => 1]));

        $response->assertUnprocessable();
        $this->assertArrayHasKey('errors', $response->json());
    }

    public function test_timezone_conversion_uses_wib_sale_date(): void
    {
        $this->send($this->payload(['sold_at' => '2026-11-07T23:30:00Z']))->assertCreated();

        $sale = DistributorExternalSale::where('distributor_id', $this->distributor->id)->firstOrFail();
        $this->assertSame('2026-11-08', $sale->sold_date->toDateString());
        $this->assertTrue($sale->sold_at->equalTo(\Carbon\Carbon::parse('2026-11-07T23:30:00Z')));
    }

    public function test_show_handles_slash_number_and_is_scoped_to_owner(): void
    {
        $this->send($this->payload())->assertCreated();

        $this->getJson('/api/v1/distributor/sales/SO/2026/001', ['X-API-KEY' => $this->apiKey])
            ->assertOk()
            ->assertJsonPath('data.external_order_number', 'SO/2026/001');

        $other = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'is_active' => true]);
        $otherKey = DistributorApiClient::issueFor($other, 'ERP')['plain_key'];
        $this->getJson('/api/v1/distributor/sales/SO/2026/001', ['X-API-KEY' => $otherKey])->assertNotFound();
    }

    public function test_products_endpoint_does_not_expose_prices(): void
    {
        $response = $this->getJson('/api/v1/distributor/products', ['X-API-KEY' => $this->apiKey])->assertOk();

        $this->assertArrayNotHasKey('price', $response->json('data.0'));
    }

    public function test_report_excludes_cancelled_and_separates_purchases(): void
    {
        $this->send($this->payload())->assertCreated();
        $this->send($this->payload(['external_order_number' => 'BATAL-1', 'status' => 'cancelled']))->assertCreated();

        $report = app(DistributorExternalSalesReport::class);
        $filters = $report->parseFilters([
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'distributor_id' => $this->distributor->id,
        ]);

        $summary = $report->summary($filters);
        $this->assertSame(1665000.0, $summary['total_sales']);
        $this->assertSame(1, $summary['transactions']);
        $this->assertSame(1, $summary['cancelled']);

        $rows = $report->byDistributor($filters);
        $this->assertSame(1665000.0, $rows->first()->total_sales);
        $this->assertSame(0.0, $rows->first()->total_purchases);
        $this->assertCount(1, $report->topProducts($filters));
    }

    public function test_report_pages_render_for_allowed_roles_only(): void
    {
        $this->send($this->payload())->assertCreated();
        $sale = DistributorExternalSale::where('distributor_id', $this->distributor->id)->firstOrFail();
        $range = ['start_date' => '2026-11-01', 'end_date' => '2026-11-30'];

        $finance = User::factory()->create(['role' => 'finance', 'wa_verified_at' => now()]);
        $this->actingAs($finance, 'admin')->get(route('admin.reports.distributor-sales.index', $range))
            ->assertOk()->assertSee('PT Mitra Rasa')->assertSee('Rp 1.665.000');
        $this->actingAs($finance, 'admin')->get(route('admin.reports.distributor-sales.show', $sale))
            ->assertOk()->assertSee('SO/2026/001');
        $this->actingAs($finance, 'admin')
            ->getJson(route('admin.reports.distributor-sales.datatable', $range), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', fn ($total) => $total >= 1);

        $sales = User::factory()->create(['role' => 'sales', 'wa_verified_at' => now()]);
        $this->actingAs($sales, 'admin')->get(route('admin.reports.distributor-sales.index'))->assertForbidden();
    }

    public function test_only_super_admin_manages_api_keys_and_key_shows_once(): void
    {
        $finance = User::factory()->create(['role' => 'finance', 'wa_verified_at' => now()]);
        $this->actingAs($finance, 'admin')
            ->post(route('admin.distributors.api-clients.store', $this->distributor), ['name' => 'X'])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $this->actingAs($admin, 'admin')
            ->post(route('admin.distributors.api-clients.store', $this->distributor), ['name' => 'ERP Baru'])
            ->assertSessionHas('new_api_key');

        $page = $this->actingAs($admin, 'admin')->get(route('admin.distributors.show', ['distributor' => $this->distributor->id, 'tab' => 'api']));
        $page->assertOk()->assertSee('ERP Baru');

        $client = DistributorApiClient::where('name', 'ERP Baru')->firstOrFail();
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.distributors.api-clients.destroy', [$this->distributor, $client]))
            ->assertRedirect();
        $this->assertFalse($client->fresh()->is_active);
    }
}
