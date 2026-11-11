<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Kode Customer QAD diisi admin per hub dan dipakai sebagai customer sales order QAD.
 * Butuh database MySQL yang sudah termigrasi.
 */
class WarehouseQadCustomerCodeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }
    }

    public function test_migration_prefills_bekasi_and_maggiore(): void
    {
        $bekasi = Warehouse::where('qad_location_code', 'FG008')->orWhere('name', 'like', '%bekasi%')->first();
        if ($bekasi) {
            $this->assertSame('HUB00002', $bekasi->qad_customer_code);
        }

        $maggiore = Warehouse::where('name', 'like', '%maggiore%')->first();
        if ($maggiore) {
            $this->assertSame('HUB00001', $maggiore->qad_customer_code);
        }

        $this->assertTrue($bekasi !== null || $maggiore !== null, 'Database uji tidak punya Hub Bekasi/Maggiore.');
    }

    public function test_admin_saves_qad_customer_code_from_edit_form(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $warehouse = Warehouse::whereNotNull('target_role')->first();
        $roles = is_array($warehouse->target_role) ? $warehouse->target_role : ['umum'];

        $this->actingAs($admin, 'admin')
            ->get(route('admin.warehouses.edit', $warehouse))
            ->assertOk()
            ->assertSee('name="qad_customer_code"', false);

        $payload = [
            'name' => $warehouse->name,
            'qad_location_code' => $warehouse->qad_location_code,
            'qad_customer_code' => ' hub00077 ',
            'target_role' => $roles,
            'is_active' => 1,
        ];

        $this->actingAs($admin, 'admin')->put(route('admin.warehouses.update', $warehouse), $payload)->assertSessionHasNoErrors();
        $this->assertSame('HUB00077', $warehouse->fresh()->qad_customer_code);
        $this->assertTrue(Warehouse::isGenericQadCustomerCode('HUB00077'));
        $this->assertFalse(Warehouse::isGenericQadCustomerCode('CS92913'));

        // Dikosongkan: hub kembali memakai customer outlet.
        $this->actingAs($admin, 'admin')->put(route('admin.warehouses.update', $warehouse), ['qad_customer_code' => ''] + $payload);
        $this->assertNull($warehouse->fresh()->qad_customer_code);
        $this->assertFalse(Warehouse::isGenericQadCustomerCode('HUB00077'));
    }

    public function test_migration_prefills_send_to_jubelio_for_ecommerce_bekasi_maggiore(): void
    {
        foreach (Warehouse::all() as $w) {
            $name = strtolower($w->name);
            $expected = str_contains($name, 'bekasi') || str_contains($name, 'maggiore') || str_contains($name, 'ecomm')
                || $w->qad_location_code === 'FG008' || $w->qad_location_code === 'FG007'
                || in_array($w->kode_hub, ['WHRG002', 'FG008', 'WHMPRG003'], true)
                || str_contains(strtolower((string) $w->kode_hub), 'ecomm')
                || in_array('ecommerce', (array) $w->target_role, true);
            $this->assertSame($expected, $w->send_to_jubelio, $w->name);
        }
    }

    public function test_admin_toggles_send_to_jubelio(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $warehouse = Warehouse::whereNotNull('target_role')->first();
        $payload = [
            'name' => $warehouse->name,
            'target_role' => is_array($warehouse->target_role) ? $warehouse->target_role : ['umum'],
            'is_active' => 1,
        ];

        $this->actingAs($admin, 'admin')->get(route('admin.warehouses.edit', $warehouse))
            ->assertOk()->assertSee('name="send_to_jubelio"', false);

        $this->actingAs($admin, 'admin')->put(route('admin.warehouses.update', $warehouse), $payload + ['send_to_jubelio' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($warehouse->fresh()->send_to_jubelio);

        $this->actingAs($admin, 'admin')->put(route('admin.warehouses.update', $warehouse), $payload)->assertSessionHasNoErrors();
        $this->assertFalse($warehouse->fresh()->send_to_jubelio);
    }
}
