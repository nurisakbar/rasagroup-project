<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\OutletCategoryDiscount;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DiscountService;
use App\Support\Pricing\PricingContext;
use App\Support\TaxAwarePrice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Diskon per kategori dari distributor ke outlet rekrutannya: hanya di hub distributor, dibatasi admin,
 * dan menggantikan diskon MOQ untuk item tersebut. Butuh MySQL yang sudah termigrasi.
 */
class OutletDistributorDiscountTest extends TestCase
{
    use DatabaseTransactions;

    private User $distributor;

    private User $outlet;

    private Product $product;

    private Warehouse $distributorHub;

    private Warehouse $otherHub;

    private string $brandId;

    private string $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $pair = DB::table('brand_category')->first();
        $this->assertNotNull($pair, 'Test butuh minimal satu pasangan brand-kategori.');
        [$this->brandId, $this->categoryId] = [$pair->brand_id, $pair->category_id];

        [$this->distributorHub, $this->otherHub] = Warehouse::limit(2)->get()->all();

        $this->distributor = User::factory()->create([
            'role' => User::ROLE_DISTRIBUTOR,
            'warehouse_id' => $this->distributorHub->id,
            'wa_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->distributor->forceFill(['outlet_discount_max' => 10])->save();
        $this->distributor->refresh();

        $this->outlet = User::factory()->create([
            'role' => User::ROLE_OUTLET,
            'sales_code' => $this->distributor->distributor_sales_code,
            'wa_verified_at' => now(),
        ]);

        $template = (array) DB::table('products')->first();
        $id = (string) Str::uuid();
        DB::table('products')->insert(array_merge($template, [
            'id' => $id,
            'code' => 'TOD-' . uniqid(),
            'slug' => uniqid('tod-'),
            'price' => 111000,
            'is_discount' => 0,
            'discount_price' => null,
            'brand_id' => $this->brandId,
            'category_id' => $this->categoryId,
            'status' => 'active',
            'deleted_at' => null,
        ]));
        $this->product = Product::withoutGlobalScopes()->findOrFail($id);
    }

    private function giveDiscount(float $percent): void
    {
        OutletCategoryDiscount::updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'brand_id' => $this->brandId, 'category_id' => $this->categoryId],
            ['distributor_id' => $this->distributor->id, 'discount_percentage' => $percent]
        );
        $this->outlet->unsetRelation('outletCategoryDiscounts');
    }

    private function priceAt(?Warehouse $hub): float
    {
        return PricingContext::current()->forWarehouse($hub?->id, fn () => $this->outlet->fresh()->getProductPrice($this->product));
    }

    public function test_discount_applies_only_at_distributor_hub(): void
    {
        $this->giveDiscount(5);
        $base = TaxAwarePrice::excludingTax(111000);

        $this->assertEqualsWithDelta($base * 0.95, $this->priceAt($this->distributorHub), 0.01);
        $this->assertEqualsWithDelta($base, $this->priceAt($this->otherHub), 0.01);
        $this->assertEqualsWithDelta($base, $this->priceAt(null), 0.01);
    }

    public function test_discount_is_capped_by_admin_max(): void
    {
        $this->giveDiscount(25);
        $this->assertSame(10.0, $this->outlet->fresh()->outletDistributorDiscountFor($this->product, $this->distributorHub->id));

        // Admin mencabut izin: diskon tidak berlaku lagi.
        $this->distributor->forceFill(['outlet_discount_max' => null])->save();
        $this->assertSame(0.0, $this->outlet->fresh()->outletDistributorDiscountFor($this->product, $this->distributorHub->id));
    }

    public function test_item_with_distributor_discount_gets_no_moq(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TSO' . strtoupper(substr(uniqid(), -6)), 'wa_verified_at' => now()]);
        $this->product->setRelation('category', new \App\Models\Category(['name' => 'Syrup']));

        $cart = new Cart(['quantity' => 12, 'warehouse_id' => $this->distributorHub->id]);
        $cart->id = (string) Str::uuid();
        $cart->setRelation('product', $this->product);

        $service = new DiscountService();
        $outlet = $this->outlet->fresh();

        // Tanpa diskon distributor: MOQ 2 karton -> 2,5%.
        $this->assertSame(2.5, $service->calculateCartDiscount(collect([$cart]), $outlet, $sales->sales_code)['line_percentages'][$cart->id]);

        // Dengan diskon distributor: MOQ tidak berlaku untuk item ini.
        $this->giveDiscount(5);
        $outlet = $this->outlet->fresh();
        $this->assertSame(0.0, $service->calculateCartDiscount(collect([$cart]), $outlet, $sales->sales_code)['line_percentages'][$cart->id]);

        // Di hub lain diskon distributor tidak berlaku, jadi MOQ kembali berlaku.
        $cart->warehouse_id = $this->otherHub->id;
        $this->assertSame(2.5, $service->calculateCartDiscount(collect([$cart]), $outlet, $sales->sales_code)['line_percentages'][$cart->id]);
    }

    public function test_distributor_saves_discounts_within_max(): void
    {
        $url = route('buyer.outlet-referral.discounts.update', $this->outlet);

        $this->actingAs($this->distributor)->get(route('buyer.outlet-referral.discounts.edit', $this->outlet))->assertOk();

        $this->actingAs($this->distributor)
            ->post($url, ['discounts' => [$this->brandId => [$this->categoryId => 15]]])
            ->assertSessionHasErrors();
        $this->assertSame(0, OutletCategoryDiscount::where('outlet_id', $this->outlet->id)->count());

        $this->actingAs($this->distributor)
            ->post($url, ['discounts' => [$this->brandId => [$this->categoryId => 7.5]]])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertEquals(7.5, OutletCategoryDiscount::where('outlet_id', $this->outlet->id)->value('discount_percentage'));

        // Isi 0 menghapus diskon.
        $this->actingAs($this->distributor)->post($url, ['discounts' => [$this->brandId => [$this->categoryId => 0]]]);
        $this->assertSame(0, OutletCategoryDiscount::where('outlet_id', $this->outlet->id)->count());
    }

    public function test_distributor_cannot_manage_other_outlets(): void
    {
        $otherDistributor = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'wa_verified_at' => now(), 'is_active' => true]);

        $this->actingAs($otherDistributor)
            ->get(route('buyer.outlet-referral.discounts.edit', $this->outlet))
            ->assertNotFound();
        $this->actingAs($this->outlet)
            ->get(route('buyer.outlet-referral.discounts.edit', $this->outlet))
            ->assertForbidden();
    }

    public function test_admin_sets_max_discount(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.distributors.outlet-discount-max.update', $this->distributor), ['outlet_discount_max' => 12.5])
            ->assertRedirect();
        $this->assertSame(12.5, $this->distributor->fresh()->outletDiscountMax());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.distributors.outlet-discount-max.update', $this->distributor), ['outlet_discount_max' => 0]);
        $this->assertSame(0.0, $this->distributor->fresh()->outletDiscountMax());
    }
}
