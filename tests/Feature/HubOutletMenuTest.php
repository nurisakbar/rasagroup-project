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

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $hub = Warehouse::where('is_active', true)->firstOrFail();
        $this->hubAdmin = User::factory()->create([
            'role' => 'warehouse', 'sub_role' => 'admin', 'warehouse_id' => $hub->id, 'wa_verified_at' => now(),
        ]);
        $this->outlet = User::factory()->create(['role' => User::ROLE_OUTLET, 'name' => 'Outlet Uji Hub', 'wa_verified_at' => now()]);
        $this->buyer = User::factory()->create(['role' => User::ROLE_BUYER, 'name' => 'Ecom Uji Hub', 'wa_verified_at' => now()]);
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
}
