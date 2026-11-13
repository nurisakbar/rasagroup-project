<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Halaman Data User Outlet: tab Outlet, Reseller, User Biasa; user biasa bisa ditandai reseller.
 * Butuh MySQL yang sudah termigrasi.
 */
class OutletTabsResellerTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $outlet;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $this->outlet = User::factory()->create(['role' => User::ROLE_OUTLET, 'name' => 'Outlet Uji Tab', 'wa_verified_at' => now()]);
        $this->buyer = User::factory()->create(['role' => User::ROLE_BUYER, 'name' => 'Buyer Uji Tab', 'wa_verified_at' => now()]);
    }

    private function tabData(string $tab): array
    {
        return $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.outlets.index', ['tab' => $tab, 'search' => ['value' => 'Uji Tab']]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->json('data');
    }

    private function names(string $tab): array
    {
        return array_column($this->tabData($tab), 'name');
    }

    public function test_tabs_separate_outlets_resellers_and_regular_users(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.index'))
            ->assertOk()->assertSee('OUTLET')->assertSee('RESELLER')->assertSee('ECOM');

        $this->assertContains('Outlet Uji Tab', $this->names('outlet'));
        $this->assertNotContains('Buyer Uji Tab', $this->names('outlet'));
        $this->assertContains('Buyer Uji Tab', $this->names('buyer'));
        $this->assertNotContains('Buyer Uji Tab', $this->names('reseller'));
    }

    public function test_marking_regular_user_as_reseller_moves_it_to_reseller_tab(): void
    {
        $payload = ['name' => $this->buyer->name, 'email' => $this->buyer->email, 'phone' => $this->buyer->phone];

        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->buyer))
            ->assertOk()->assertSee('Tandai sebagai Reseller');

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.outlets.update', $this->buyer), $payload + ['is_reseller' => 1])
            ->assertRedirect(route('admin.outlets.index', ['tab' => 'reseller']));

        $fresh = $this->buyer->fresh();
        $this->assertTrue($fresh->is_reseller);
        $this->assertNotNull($fresh->reseller_marked_at);
        $this->assertContains('Buyer Uji Tab', $this->names('reseller'));
        $this->assertNotContains('Buyer Uji Tab', $this->names('buyer'));

        // Centang dilepas: kembali ke User Biasa.
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.outlets.update', $this->buyer), $payload + ['is_reseller' => 0])
            ->assertRedirect(route('admin.outlets.index', ['tab' => 'buyer']));
        $this->assertFalse($this->buyer->fresh()->is_reseller);
        $this->assertNull($this->buyer->fresh()->reseller_marked_at);
    }

    public function test_outlet_can_become_reseller_and_back(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TSR' . strtoupper(substr(uniqid(), -6)), 'wa_verified_at' => now()]);
        $this->outlet->forceFill(['sales_code' => $sales->sales_code])->save();
        $payload = ['name' => $this->outlet->name, 'email' => $this->outlet->email];

        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->outlet))
            ->assertOk()->assertSee('Tandai sebagai Reseller')->assertDontSee('distributor perekrut tidak berlaku');

        // Outlet -> Reseller: role tetap outlet, kode sales perekrut tetap tersimpan.
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.outlets.update', $this->outlet), $payload + ['is_reseller' => 1])
            ->assertRedirect(route('admin.outlets.index', ['tab' => 'reseller']));

        $fresh = $this->outlet->fresh();
        $this->assertTrue($fresh->is_reseller);
        $this->assertSame(User::ROLE_OUTLET, $fresh->role);
        $this->assertSame($sales->sales_code, $fresh->sales_code);
        $this->assertSame('reseller', $fresh->customerLevel());
        $this->assertContains('Outlet Uji Tab', $this->names('reseller'));
        $this->assertNotContains('Outlet Uji Tab', $this->names('outlet'));

        $row = collect($this->tabData('reseller'))->firstWhere('name', 'Outlet Uji Tab');
        $this->assertArrayNotHasKey('origin', $row);

        // Riwayat tercatat untuk audit, tidak ditampilkan di form edit.
        $change = \App\Models\UserLevelChange::where('user_id', $this->outlet->id)->latest('id')->first();
        $this->assertSame(['outlet', 'reseller', (string) $this->admin->id], [$change->from_level, $change->to_level, (string) $change->changed_by]);
        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->outlet))
            ->assertOk()->assertDontSee('Riwayat Perubahan Level')->assertSee('Sales / Distributor Perekrut');

        // Reseller -> Outlet: kembali ke tab Outlet dengan kode sales semula.
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.outlets.update', $this->outlet), $payload + ['is_reseller' => 0, 'sales_code' => $sales->sales_code])
            ->assertRedirect(route('admin.outlets.index', ['tab' => 'outlet']));
        $this->assertFalse($this->outlet->fresh()->is_reseller);
        $this->assertSame($sales->sales_code, $this->outlet->fresh()->sales_code);
        $this->assertSame(2, \App\Models\UserLevelChange::where('user_id', $this->outlet->id)->count());

        // Simpan tanpa mengubah level: tidak menambah riwayat.
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.outlets.update', $this->outlet), $payload + ['is_reseller' => 0, 'sales_code' => $sales->sales_code]);
        $this->assertSame(2, \App\Models\UserLevelChange::where('user_id', $this->outlet->id)->count());
    }

    public function test_outlet_reseller_uses_reseller_tier_without_sales_code(): void
    {
        $service = new DiscountService();
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TSQ' . strtoupper(substr(uniqid(), -6)), 'wa_verified_at' => now()]);
        $carts = collect([$syrup = $this->cart('Syrup', 20 * 6)]);

        // Outlet dengan kode sales: 20 karton -> tier outlet 10%.
        $this->assertSame(10.0, $service->calculateCartDiscount($carts, $this->outlet->fresh(), $sales->sales_code)['line_percentages'][$syrup->id]);

        // Outlet jadi reseller: 20 karton belum dapat diskon (tier reseller > 50 karton).
        $this->outlet->forceFill(['is_reseller' => true])->save();
        $reseller = $this->outlet->fresh();
        $this->assertSame(0.0, $service->calculateCartDiscount($carts, $reseller, $sales->sales_code)['line_percentages'][$syrup->id]);
        $big = collect([$s = $this->cart('Syrup', 51 * 6)]);
        $this->assertSame(15.0, $service->calculateCartDiscount($big, $reseller, null)['line_percentages'][$s->id]);

        // Info kekurangan karton untuk reseller.
        $hints = $service->resellerHints($carts, $reseller);
        $this->assertSame([['group_name' => 'Syrup & Sauce', 'cartons' => 20, 'cartons_needed' => 31, 'percentage' => 15.0]], $hints);
        $this->assertSame([], $service->resellerHints($big, $reseller));
        $this->assertSame([], $service->resellerHints($carts, $this->buyer->fresh()));
    }

    public function test_reseller_always_uses_outlet_channel(): void
    {
        $this->assertFalse($this->buyer->usesOutletChannel(false));
        $this->assertTrue($this->buyer->usesOutletChannel(true));

        $this->outlet->forceFill(['is_reseller' => true])->save();
        $this->assertTrue($this->outlet->fresh()->usesOutletChannel(false));
    }

    public function test_reseller_gets_subdist_reseller_discount_tier(): void
    {
        $service = new DiscountService();
        // User biasa: tier outlet (1 karton 2,5%, 19+ karton 10%).
        $this->assertSame(2.5, $service->tierPercentage($this->buyer, 'syrup_sauce', 1));
        $this->assertSame(10.0, $service->tierPercentage($this->buyer, 'syrup_sauce', 60));

        // Reseller: tier Subdist / Reseller (> 50 karton 15% / 8%, di bawahnya belum dapat).
        $this->buyer->forceFill(['is_reseller' => true])->save();
        $this->assertSame(15.0, $service->tierPercentage($this->buyer->fresh(), 'syrup_sauce', 51));
        $this->assertSame(8.0, $service->tierPercentage($this->buyer->fresh(), 'others', 51));
        $this->assertSame(0.0, $service->tierPercentage($this->buyer->fresh(), 'syrup_sauce', 1));
    }

    private function cart(string $category, int $quantity): \App\Models\Cart
    {
        $product = new \App\Models\Product(['name' => $category, 'price' => 111000, 'unit' => 'BT', 'large_unit' => 'CTN', 'units_per_large' => 6]);
        $product->setRelation('category', new \App\Models\Category(['name' => $category]));
        $cart = new \App\Models\Cart(['quantity' => $quantity]);
        $cart->id = (string) \Illuminate\Support\Str::uuid();
        $cart->setRelation('product', $product);

        return $cart;
    }

    public function test_reseller_gets_discount_without_sales_code(): void
    {
        $service = new DiscountService();
        $carts = collect([$syrup = $this->cart('Syrup', 51 * 6), $pulp = $this->cart('PULP', 51 * 6)]);

        // User biasa tanpa kode sales: tidak dapat diskon MOQ.
        $plain = $service->calculateCartDiscount($carts, $this->buyer->fresh(), null);
        $this->assertSame(0.0, $plain['line_percentages'][$syrup->id]);

        // Reseller tanpa kode sales: 51 karton -> 15% (Syrup & Sauce) dan 8% (Pulp).
        $this->buyer->forceFill(['is_reseller' => true])->save();
        $reseller = $service->calculateCartDiscount($carts, $this->buyer->fresh(), null);
        $this->assertSame(15.0, $reseller['line_percentages'][$syrup->id]);
        $this->assertSame(8.0, $reseller['line_percentages'][$pulp->id]);

        // Reseller di bawah/sama dengan 50 karton: belum dapat diskon.
        $small = collect([$s = $this->cart('Syrup', 50 * 6)]);
        $this->assertSame(0.0, $service->calculateCartDiscount($small, $this->buyer->fresh(), null)['line_percentages'][$s->id]);
    }

    public function test_edit_form_is_read_only_except_reseller_flag(): void
    {
        $this->outlet->forceFill(['qad_customer_code' => 'QADLAMA01'])->save();

        $html = $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->outlet))
            ->assertOk()
            ->assertDontSee('Kode Customer QAD')
            ->assertDontSee('Password Baru')
            ->assertDontSee('name="sales_code"', false)
            ->assertDontSee('Reseller pindah ke tab Reseller')
            ->getContent();
        $this->assertSame(5, substr_count($html, ' readonly>'));

        // Data lain yang dikirim diabaikan; hanya level reseller yang berubah.
        $this->actingAs($this->admin, 'admin')->put(route('admin.outlets.update', $this->outlet), [
            'name' => 'Nama Diubah', 'email' => 'diubah@contoh.test', 'phone' => '0800', 'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'qad_customer_code' => 'QADBARU', 'is_reseller' => 1,
        ])->assertRedirect(route('admin.outlets.index', ['tab' => 'reseller']));

        $fresh = $this->outlet->fresh();
        $this->assertTrue($fresh->is_reseller);
        $this->assertSame(['Outlet Uji Tab', 'QADLAMA01'], [$fresh->name, $fresh->qad_customer_code]);
        $this->assertNotSame('diubah@contoh.test', $fresh->email);
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('rahasia123', $fresh->password));
    }

    public function test_ecom_tab_has_no_hub_column_and_no_actions(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.index', ['tab' => 'buyer']))
            ->assertOk()
            ->assertDontSee('<th>Hub</th>', false)
            ->assertDontSee('<th>Action</th>', false)
            ->assertDontSee("data: 'action'", false);

        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.index', ['tab' => 'outlet']))
            ->assertOk()
            ->assertSee('<th>Hub</th>', false)
            ->assertSee('<th>Action</th>', false);
    }

    /** Kirim pencarian ColumnControl untuk satu kolom tab outlet, kembalikan nama yang cocok. */
    private function columnSearch(string $data, string $logic, string $value): array
    {
        $columns = [];
        foreach (['DT_RowIndex', 'name', 'email', 'phone', 'hub', 'sales_name', 'created_at', 'action'] as $col) {
            // Sama dengan halaman: No, Nama Sales, dan Action tidak ikut pencarian global.
            $searchable = in_array($col, ['DT_RowIndex', 'sales_name', 'action'], true) ? 'false' : 'true';
            $columns[] = ['data' => $col, 'name' => $col, 'searchable' => $searchable, 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']]
                + ($col === $data ? ['columnControl' => ['search' => ['value' => $value, 'logic' => $logic, 'type' => 'text']]] : []);
        }

        // Yajra menyimpan request pertama sebagai singleton; reset agar tiap request test memakai input baru.
        app()->forgetInstance('datatables.request');
        app()->forgetInstance('datatables');
        $res = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.outlets.index', ['tab' => 'outlet', 'draw' => 1, 'start' => 0, 'length' => -1, 'columns' => $columns]), ['X-Requested-With' => 'XMLHttpRequest']);

        return array_column($res->assertOk()->json('data'), 'name');
    }

    public function test_column_control_search_filters_server_side(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'name' => 'Sales Kolom Uji', 'sales_code' => 'TCC' . strtoupper(substr(uniqid(), -6)), 'wa_verified_at' => now()]);
        $other = User::factory()->create(['role' => User::ROLE_OUTLET, 'name' => 'Outlet Lain Kolom', 'wa_verified_at' => now()]);
        $this->outlet->forceFill(['sales_code' => $sales->sales_code])->save();

        $this->assertContains('Outlet Uji Tab', $this->columnSearch('name', 'contains', 'uji tab'));
        $this->assertNotContains('Outlet Lain Kolom', $this->columnSearch('name', 'contains', 'uji tab'));
        $this->assertSame(['Outlet Uji Tab'], array_values(array_intersect(['Outlet Uji Tab', 'Outlet Lain Kolom'], $this->columnSearch('name', 'starts', 'Outlet Uji'))));
        $this->assertNotContains('Outlet Uji Tab', $this->columnSearch('name', 'notContains', 'Uji Tab'));
        $this->assertContains('Outlet Uji Tab', $this->columnSearch('email', 'equal', $this->outlet->email));

        // Kolom relasi (Nama Sales).
        $this->assertSame(['Outlet Uji Tab'], $this->columnSearch('sales_name', 'contains', 'Kolom Uji'));
        $this->assertNotContains('Outlet Uji Tab', $this->columnSearch('sales_name', 'empty', ''));
        $this->assertContains('Outlet Lain Kolom', $this->columnSearch('sales_name', 'empty', ''));

        // Karakter wildcard LIKE diperlakukan sebagai teks biasa.
        $this->assertSame([], $this->columnSearch('name', 'contains', '%'));
    }
}
