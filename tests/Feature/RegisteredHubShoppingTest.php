<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Outlet/reseller yang terdaftar di sebuah hub selalu belanja (stok, harga, pengiriman) dari hub tersebut.
 * Butuh MySQL yang sudah termigrasi.
 */
class RegisteredHubShoppingTest extends TestCase
{
    use DatabaseTransactions;

    private Warehouse $hub;

    private Warehouse $otherHub;

    private User $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->hub = Warehouse::create(['name' => 'Hub Terdaftar Uji', 'slug' => 'hub-terdaftar-uji', 'is_active' => true, 'target_role' => ['outlet'], 'latitude' => -6.24, 'longitude' => 107.0]);
        $this->otherHub = Warehouse::create(['name' => 'Hub Dekat Uji', 'slug' => 'hub-dekat-uji', 'is_active' => true, 'target_role' => ['outlet', 'ecommerce'], 'latitude' => -6.2, 'longitude' => 106.8]);
        $this->outlet = User::factory()->create([
            'role' => User::ROLE_OUTLET, 'hub_id' => $this->hub->id, 'wa_verified_at' => now(),
        ]);
    }

    private function address(User $user): Address
    {
        // Alamat persis di lokasi hub lain (hub lain adalah yang terdekat).
        return Address::create([
            'user_id' => $user->id, 'label' => 'Toko', 'recipient_name' => $user->name, 'phone' => '0812',
            'address_detail' => 'Jl. Uji', 'latitude' => -6.2, 'longitude' => 106.8, 'is_default' => true,
        ]);
    }

    public function test_storefront_session_and_cart_follow_registered_hub(): void
    {
        $product = Product::withoutGlobalScopes()->firstOrFail();
        $cart = Cart::create(['user_id' => $this->outlet->id, 'cart_type' => 'regular', 'product_id' => $product->id, 'warehouse_id' => $this->otherHub->id, 'quantity' => 1]);

        $this->actingAs($this->outlet)
            ->withSession(['selected_hub_id' => $this->otherHub->id, 'selected_hub_name' => $this->otherHub->name])
            ->get(route('home'))
            ->assertOk()
            ->assertSessionHas('selected_hub_id', $this->hub->id)
            ->assertSessionHas('selected_hub_name', 'Hub Terdaftar Uji');

        $this->assertSame((string) $this->hub->id, (string) $cart->fresh()->warehouse_id);
    }

    public function test_address_based_hub_is_registered_hub_but_big_order_goes_to_mm2100(): void
    {
        $address = $this->address($this->outlet);
        $this->assertSame((string) $this->hub->id, (string) Warehouse::findBestHubForAddress($address)->id);
        $this->assertSame((string) $this->hub->id, (string) Warehouse::findBestHubForAddress($address, null, 24999999)->id);

        // Aturan order >= 25 juta tetap berlaku: dikirim dari DC MM2100.
        $mm2100 = Warehouse::where('is_active', true)->where('name', 'like', '%MM2100%')->first()
            ?? Warehouse::create(['name' => 'DC MM2100 Uji', 'slug' => 'dc-mm2100-uji', 'is_active' => true, 'target_role' => ['distributor']]);
        $this->assertSame((string) $mm2100->id, (string) Warehouse::findBestHubForAddress($address, null, 25000000)->id);

        // Tanpa hub terdaftar: tetap memakai aturan lama (hub terdekat sesuai jalur).
        $plain = User::factory()->create(['role' => User::ROLE_BUYER, 'wa_verified_at' => now()]);
        $this->assertNotSame((string) $this->hub->id, (string) Warehouse::findBestHubForAddress($this->address($plain))?->id);
    }

    public function test_reseller_keeps_registered_hub_and_cannot_pick_other_hub(): void
    {
        $this->outlet->forceFill(['is_reseller' => true])->save();

        $this->actingAs($this->outlet)
            ->withSession(['selected_hub_id' => $this->hub->id])
            ->post(route('hubs.select'), ['warehouse_id' => $this->otherHub->id])
            ->assertRedirect(route('home'))
            ->assertSessionHas('selected_hub_id', $this->hub->id);
    }

    public function test_inactive_registered_hub_is_ignored(): void
    {
        $this->hub->forceFill(['is_active' => false])->save();
        $this->assertNull($this->outlet->fresh()->registeredShoppingHub());

        $this->actingAs($this->outlet)
            ->withSession(['selected_hub_id' => $this->otherHub->id])
            ->get(route('home'))
            ->assertSessionHas('selected_hub_id', $this->otherHub->id);
    }

    public function test_checkout_keeps_hub_chosen_for_big_order(): void
    {
        $mm2100 = Warehouse::where('is_active', true)->where('name', 'like', '%MM2100%')->first()
            ?? Warehouse::create(['name' => 'DC MM2100 Uji', 'slug' => 'dc-mm2100-uji', 'is_active' => true, 'target_role' => ['distributor']]);
        $product = Product::withoutGlobalScopes()->firstOrFail();
        $cart = Cart::create(['user_id' => $this->outlet->id, 'cart_type' => 'regular', 'product_id' => $product->id, 'warehouse_id' => $mm2100->id, 'quantity' => 1]);

        // Di checkout (mis. hitung ongkir) hub & keranjang tidak ditarik kembali ke hub terdaftar.
        $this->actingAs($this->outlet)
            ->withSession(['selected_hub_id' => $mm2100->id])
            ->get(route('checkout.check-stock'));
        $this->assertSame((string) $mm2100->id, (string) $cart->fresh()->warehouse_id);

        // Kembali ke toko: stok & keranjang mengikuti hub terdaftar lagi (checkout akan menghitung ulang).
        $this->actingAs($this->outlet)->withSession(['selected_hub_id' => $mm2100->id])
            ->get(route('home'))->assertSessionHas('selected_hub_id', $this->hub->id);
        $this->assertSame((string) $this->hub->id, (string) $cart->fresh()->warehouse_id);
    }
}
