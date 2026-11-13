<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLevelChange;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Menu Outlet dan Reseller di panel Admin Hub: tab OUTLET & RESELLER saja (tanpa ECOM), tanpa hapus,
 * hanya untuk Admin Hub (sub_role admin). Butuh MySQL yang sudah termigrasi.
 */
class HubOutletMenuTest extends TestCase
{
    use DatabaseTransactions;

    private User $hubAdmin;

    private User $outlet;

    private User $buyer;

    private User $otherHubOutlet;

    private User $sales;

    private Warehouse $hub;

    private Warehouse $otherHub;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->hub = Warehouse::create(['name' => 'Hub Uji Bekasi', 'slug' => 'hub-uji-bekasi', 'is_active' => true, 'target_role' => ['outlet']]);
        $this->otherHub = Warehouse::create(['name' => 'Hub Uji Lain', 'slug' => 'hub-uji-lain', 'is_active' => true, 'target_role' => ['outlet']]);
        $this->hubAdmin = User::factory()->create([
            'role' => 'warehouse', 'sub_role' => 'admin', 'warehouse_id' => $this->hub->id, 'wa_verified_at' => now(),
        ]);
        $this->sales = User::factory()->create([
            'role' => User::ROLE_SALES, 'sales_code' => 'BKS' . strtoupper(substr(uniqid(), -6)), 'warehouse_id' => $this->hub->id, 'wa_verified_at' => now(),
        ]);
        $this->outlet = User::factory()->create([
            'role' => User::ROLE_OUTLET, 'name' => 'Outlet Uji Hub', 'sales_code' => $this->sales->sales_code, 'hub_id' => $this->hub->id, 'wa_verified_at' => now(),
        ]);
        $this->otherHubOutlet = User::factory()->create([
            'role' => User::ROLE_OUTLET, 'name' => 'Outlet Hub Lain', 'hub_id' => $this->otherHub->id, 'wa_verified_at' => now(),
        ]);
        $this->buyer = User::factory()->create(['role' => User::ROLE_BUYER, 'name' => 'Ecom Uji Hub', 'hub_id' => $this->hub->id, 'wa_verified_at' => now()]);
    }

    private function names(string $tab): array
    {
        return array_column($this->actingAs($this->hubAdmin)
            ->getJson(route('warehouse.outlets.index', ['tab' => $tab]) . '&length=-1', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data'), 'name');
    }

    public function test_hub_admin_sees_outlet_and_reseller_tabs_without_ecom(): void
    {
        $this->actingAs($this->hubAdmin)->get(route('warehouse.outlets.index'))
            ->assertOk()
            ->assertSee('OUTLET DAN RESELLER')
            ->assertSee('RESELLER')
            ->assertDontSee('ECOM')
            ->assertDontSee('tab=buyer', false);

        $this->assertContains('Outlet Uji Hub', $this->names('outlet'));
        $this->assertNotContains('Outlet Hub Lain', $this->names('outlet'));
        // Tab ECOM diminta lewat URL: kembali ke tab OUTLET, user ecom tidak tampil.
        $this->assertNotContains('Ecom Uji Hub', $this->names('buyer'));
        $this->assertContains('Outlet Uji Hub', $this->names('buyer'));
    }

    public function test_hub_admin_cannot_open_ecom_user(): void
    {
        $this->actingAs($this->hubAdmin)->get(route('warehouse.outlets.edit', $this->buyer))
            ->assertRedirect(route('warehouse.outlets.index'));
        $this->actingAs($this->hubAdmin)->put(route('warehouse.outlets.update', $this->buyer), [
            'name' => 'Diubah', 'email' => $this->buyer->email, 'is_reseller' => 1,
        ])->assertForbidden();
        $this->assertFalse($this->buyer->fresh()->is_reseller);
    }

    public function test_hub_admin_can_make_outlet_reseller(): void
    {
        $this->actingAs($this->hubAdmin)->get(route('warehouse.outlets.edit', $this->outlet))
            ->assertOk()->assertSee('Tandai sebagai Reseller');

        $this->actingAs($this->hubAdmin)->put(route('warehouse.outlets.update', $this->outlet), [
            'name' => $this->outlet->name, 'email' => $this->outlet->email, 'is_reseller' => 1,
        ])->assertRedirect(route('warehouse.outlets.index', ['tab' => 'reseller']));

        $this->assertTrue($this->outlet->fresh()->is_reseller);
        $this->assertSame((string) $this->hubAdmin->id, (string) UserLevelChange::where('user_id', $this->outlet->id)->value('changed_by'));
        $this->assertContains('Outlet Uji Hub', $this->names('reseller'));
    }

    public function test_hub_staff_and_delete_are_not_allowed(): void
    {
        $staff = User::factory()->create([
            'role' => 'warehouse', 'sub_role' => 'staff', 'warehouse_id' => $this->hubAdmin->warehouse_id, 'wa_verified_at' => now(),
        ]);
        $this->actingAs($staff)->get(route('warehouse.outlets.index'))->assertForbidden();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('warehouse.outlets.destroy'));
        $this->actingAs($this->hubAdmin)
            ->getJson(route('warehouse.outlets.index', ['tab' => 'outlet']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertDontSee('delete-form');
    }

    public function test_registration_with_hub_sales_code_records_outlet_hub(): void
    {
        $email = 'daftar.hub.' . uniqid() . '@contoh.test';
        $this->post(route('register'), [
            'name' => 'Outlet Daftar Hub',
            'email' => $email,
            'password' => 'RahasiaBaru123',
            'password_confirmation' => 'RahasiaBaru123',
            'phone' => '08' . random_int(1000000000, 1999999999),
            'sales_code' => $this->sales->sales_code . ' - ' . $this->sales->name,
        ])->assertRedirect(route('register.success'));

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame(User::ROLE_OUTLET, $user->role);
        $this->assertSame((string) $this->hub->id, (string) $user->hub_id);
        $this->assertContains('Outlet Daftar Hub', $this->names('outlet'));
    }

    public function test_outlet_stays_in_hub_after_becoming_reseller_and_other_hub_cannot_see_it(): void
    {
        $this->actingAs($this->hubAdmin)->put(route('warehouse.outlets.update', $this->outlet), [
            'name' => $this->outlet->name, 'email' => $this->outlet->email, 'is_reseller' => 1,
        ])->assertRedirect(route('warehouse.outlets.index', ['tab' => 'reseller']));

        $this->assertSame((string) $this->hub->id, (string) $this->outlet->fresh()->hub_id);
        $this->assertContains('Outlet Uji Hub', $this->names('reseller'));

        // Admin hub lain tidak melihat dan tidak bisa membuka outlet/reseller hub ini.
        $otherAdmin = User::factory()->create([
            'role' => 'warehouse', 'sub_role' => 'admin', 'warehouse_id' => $this->otherHub->id, 'wa_verified_at' => now(),
        ]);
        $rows = $this->actingAs($otherAdmin)
            ->getJson(route('warehouse.outlets.index', ['tab' => 'reseller']) . '&length=-1', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');
        $this->assertNotContains('Outlet Uji Hub', array_column($rows, 'name'));
        $this->actingAs($otherAdmin)->get(route('warehouse.outlets.edit', $this->outlet))
            ->assertRedirect(route('warehouse.outlets.index'));
    }

    public function test_hub_admin_creates_outlet_in_own_hub_with_own_sales_only(): void
    {
        $otherSales = User::factory()->create([
            'role' => User::ROLE_SALES, 'sales_code' => 'LAN' . strtoupper(substr(uniqid(), -6)), 'warehouse_id' => $this->otherHub->id, 'wa_verified_at' => now(),
        ]);
        $payload = [
            'name' => 'Outlet Baru Hub', 'email' => 'outlet.baru.' . uniqid() . '@contoh.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ];

        $this->actingAs($this->hubAdmin)->get(route('warehouse.outlets.create'))
            ->assertOk()->assertSee($this->sales->sales_code)->assertDontSee($otherSales->sales_code);

        $this->actingAs($this->hubAdmin)->post(route('warehouse.outlets.store'), $payload + ['sales_code' => $otherSales->sales_code])
            ->assertSessionHasErrors('sales_code');
        $this->actingAs($this->hubAdmin)->post(route('warehouse.outlets.store'), $payload + ['hub_id' => $this->otherHub->id])
            ->assertSessionHasErrors('hub_id');

        $this->actingAs($this->hubAdmin)->post(route('warehouse.outlets.store'), $payload + ['sales_code' => $this->sales->sales_code])
            ->assertRedirect(route('warehouse.outlets.index'));
        $this->assertSame((string) $this->hub->id, (string) User::where('email', $payload['email'])->value('hub_id'));
    }

    public function test_central_admin_sees_hub_column_and_sets_hub(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $rows = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.outlets.index', ['tab' => 'outlet']) . '&length=-1', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');
        $this->assertSame('Hub Uji Lain', collect($rows)->firstWhere('name', 'Outlet Hub Lain')['hub'] ?? null);

        $this->actingAs($admin, 'admin')->put(route('admin.outlets.update', $this->otherHubOutlet), [
            'name' => $this->otherHubOutlet->name, 'email' => $this->otherHubOutlet->email, 'hub_id' => $this->hub->id,
        ])->assertRedirect();
        $this->assertSame((string) $this->hub->id, (string) $this->otherHubOutlet->fresh()->hub_id);
        $this->assertContains('Outlet Hub Lain', $this->names('outlet'));
    }

    public function test_setting_sales_hub_assigns_its_outlets_without_hub(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'NEW' . strtoupper(substr(uniqid(), -6)), 'wa_verified_at' => now()]);
        $orphan = User::factory()->create(['role' => User::ROLE_OUTLET, 'sales_code' => $sales->sales_code, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')->put(route('admin.sales.update', $sales), [
            'name' => $sales->name, 'email' => $sales->email, 'sales_code' => $sales->sales_code, 'warehouse_id' => $this->hub->id,
        ])->assertRedirect(route('admin.sales.index'));

        $this->assertSame((string) $this->hub->id, (string) $orphan->fresh()->hub_id);
    }
}
