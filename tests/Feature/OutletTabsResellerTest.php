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
            ->assertOk()->assertSee('Outlet')->assertSee('Reseller')->assertSee('User Biasa');

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

    public function test_outlet_cannot_be_flagged_as_reseller(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->outlet))
            ->assertOk()->assertDontSee('Tandai sebagai Reseller');

        $this->actingAs($this->admin, 'admin')->put(route('admin.outlets.update', $this->outlet), [
            'name' => $this->outlet->name, 'email' => $this->outlet->email, 'is_reseller' => 1,
        ])->assertRedirect(route('admin.outlets.index', ['tab' => 'outlet']));

        $this->assertFalse($this->outlet->fresh()->is_reseller);
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

    public function test_reseller_edit_form_hides_sales_and_keeps_sales_code(): void
    {
        $this->buyer->forceFill(['is_reseller' => true, 'reseller_marked_at' => now()])->save();

        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->buyer))
            ->assertOk()
            ->assertSee('id="sales-code-group" style="display: none;"', false);

        $salesCode = User::where('role', User::ROLE_SALES)->whereNotNull('sales_code')->value('sales_code');
        $this->actingAs($this->admin, 'admin')->put(route('admin.outlets.update', $this->buyer), [
            'name' => $this->buyer->name, 'email' => $this->buyer->email, 'is_reseller' => 1,
            'sales_code' => $salesCode,
        ])->assertRedirect(route('admin.outlets.index', ['tab' => 'reseller']));

        $this->assertNull($this->buyer->fresh()->sales_code);

        // User biasa (bukan reseller): pilihan sales tampil.
        $this->buyer->forceFill(['is_reseller' => false])->save();
        $this->actingAs($this->admin, 'admin')->get(route('admin.outlets.edit', $this->buyer))
            ->assertOk()
            ->assertSee('id="sales-code-group" style=""', false);
    }
}
