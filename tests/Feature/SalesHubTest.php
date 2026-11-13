<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Data Sales menyimpan hub asal kode sales; pilihan hanya hub aktif dengan Gudang Order "outlet".
 * Butuh MySQL yang sudah termigrasi.
 */
class SalesHubTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Warehouse $outletHub;

    private Warehouse $ecommerceHub;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $this->outletHub = Warehouse::create(['name' => 'Hub Outlet Uji Sales', 'slug' => 'hub-outlet-uji-sales', 'is_active' => true, 'target_role' => ['outlet']]);
        $this->ecommerceHub = Warehouse::create(['name' => 'Hub Ecom Uji Sales', 'slug' => 'hub-ecom-uji-sales', 'is_active' => true, 'target_role' => ['ecommerce']]);
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'name' => 'Sales Uji Hub',
            'email' => 'sales.uji.hub@contoh.test',
            'sales_code' => 'SLSUJIHUB',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'is_active' => 1,
        ];
    }

    public function test_form_only_offers_outlet_hubs(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.sales.create'))
            ->assertOk()
            ->assertSee('Hub Outlet Uji Sales')
            ->assertDontSee('Hub Ecom Uji Sales');
    }

    public function test_store_and_update_save_hub_and_list_shows_it(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.sales.store'), $this->payload(['warehouse_id' => $this->outletHub->id]))
            ->assertRedirect(route('admin.sales.index'));

        $sale = User::where('email', 'sales.uji.hub@contoh.test')->firstOrFail();
        $this->assertSame((string) $this->outletHub->id, (string) $sale->warehouse_id);

        $rows = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.sales.index', ['length' => -1]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');
        $row = collect($rows)->firstWhere('email', 'sales.uji.hub@contoh.test');
        $this->assertSame('Hub Outlet Uji Sales', $row['hub'] ?? null);

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.sales.update', $sale), $this->payload(['warehouse_id' => '']))
            ->assertRedirect(route('admin.sales.index'));
        $this->assertNull($sale->fresh()->warehouse_id);
    }

    public function test_non_outlet_hub_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.sales.store'), $this->payload(['warehouse_id' => $this->ecommerceHub->id]))
            ->assertSessionHasErrors('warehouse_id');
    }
}
