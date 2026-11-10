<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Diskon berjenjang MOQ dihitung per grup: Syrup & Sauce satu kesatuan, Pulp/Powder/Cocoa dsb satu kesatuan.
 * Butuh MySQL (Setting PPN dibaca dari database).
 */
class DiscountTierTest extends TestCase
{
    use DatabaseTransactions;

    private DiscountService $service;

    private string $salesCode;

    private User $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $this->service = new DiscountService();
        $this->outlet = new User(['role' => 'outlet']);

        $this->salesCode = 'TSC' . strtoupper(substr(uniqid(), -6));
        User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => $this->salesCode, 'wa_verified_at' => now()]);
    }

    private function line(string $category, int $quantity, ?int $perCarton = 6, float $price = 111000): Cart
    {
        $product = new Product([
            'name' => $category . ' uji',
            'price' => $price,
            'unit' => 'BT',
            'large_unit' => $perCarton ? 'CTN' : null,
            'units_per_large' => $perCarton,
        ]);
        $product->setRelation('category', new Category(['name' => $category]));

        $cart = new Cart(['quantity' => $quantity]);
        $cart->id = (string) Str::uuid();
        $cart->setRelation('product', $product);

        return $cart;
    }

    public function test_cartons_are_counted_per_group(): void
    {
        // Kasus dari checkout: 4 Syrup (0 karton) + 7 Pulp/Cocoa (1 karton).
        $carts = collect([
            $syrup = $this->line('Syrup', 4),
            $pulp = $this->line('PULP', 6),
            $cocoa = $this->line('COCOA', 1),
        ]);

        $result = $this->service->calculateCartDiscount($carts, $this->outlet, $this->salesCode);

        $this->assertSame(['syrup_sauce' => 0, 'others' => 1], $result['cartons_per_group']);
        $this->assertSame(0.0, $result['line_percentages'][$syrup->id]);
        $this->assertSame(2.0, $result['line_percentages'][$pulp->id]);
        $this->assertSame(2.0, $result['line_percentages'][$cocoa->id]);
        $this->assertCount(1, $result['discount_details']);
    }

    public function test_each_group_gets_its_own_tier(): void
    {
        // Syrup + Sauce digabung: 12 + 6 = 3 karton -> 5%. Pulp + Powder: 6 + 6 = 2 karton -> 2%.
        $carts = collect([
            $syrup = $this->line('Syrup', 12),
            $this->line('SAUCE', 6),
            $pulp = $this->line('PULP', 6),
            $this->line('POWDER', 6),
        ]);

        $result = $this->service->calculateCartDiscount($carts, $this->outlet, $this->salesCode);

        $this->assertSame(['syrup_sauce' => 3, 'others' => 2], $result['cartons_per_group']);
        $this->assertSame(5.0, $result['line_percentages'][$syrup->id]);
        $this->assertSame(2.0, $result['line_percentages'][$pulp->id]);
    }

    public function test_carton_size_follows_product(): void
    {
        // 12 botol produk isi 12/karton = 1 karton (bukan 2).
        $this->assertSame(1, $this->service->cartonsPerGroup(collect([$this->line('Syrup', 12, 12)]))['syrup_sauce']);
        // Tanpa data satuan besar: isi 6.
        $this->assertSame(2, $this->service->cartonsPerGroup(collect([$this->line('Syrup', 12, null)]))['syrup_sauce']);
        // Produk di luar program tidak dihitung.
        $this->assertSame(['syrup_sauce' => 0, 'others' => 0], $this->service->cartonsPerGroup(collect([$this->line('MERCHANDISE', 60)])));
    }

    public function test_below_one_carton_has_no_discount(): void
    {
        $result = $this->service->calculateCartDiscount(collect([$this->line('Syrup', 3), $this->line('PULP', 2)]), $this->outlet, $this->salesCode);

        $this->assertSame(['syrup_sauce' => 0, 'others' => 0], $result['cartons_per_group']);
        $this->assertEquals(0, $result['total_discount_amount']);
    }

    public function test_outlet_tiers_match_table(): void
    {
        $expected = [
            1 => [2.5, 2.0], 2 => [2.5, 2.0],
            3 => [5.0, 4.0], 8 => [5.0, 4.0],
            9 => [7.5, 5.0], 18 => [7.5, 5.0],
            19 => [10.0, 6.0], 30 => [10.0, 6.0],
        ];

        foreach ($expected as $cartons => [$syrup, $others]) {
            $this->assertSame($syrup, $this->service->tierPercentage($this->outlet, 'syrup_sauce', $cartons), "Syrup {$cartons} karton");
            $this->assertSame($others, $this->service->tierPercentage($this->outlet, 'others', $cartons), "Others {$cartons} karton");
        }
    }

    public function test_subdist_needs_more_than_fifty_cartons(): void
    {
        $subdist = new User(['role' => 'outlet']);
        $subdist->sub_role = 'reseller';

        $this->assertSame(0.0, $this->service->tierPercentage($subdist, 'syrup_sauce', 50));
        $this->assertSame(15.0, $this->service->tierPercentage($subdist, 'syrup_sauce', 51));
        $this->assertSame(8.0, $this->service->tierPercentage($subdist, 'others', 51));
    }

    public function test_no_discount_without_valid_sales_code(): void
    {
        $carts = collect([$syrup = $this->line('Syrup', 12), $pulp = $this->line('PULP', 12)]);

        foreach ([null, '', 'KODE-NGAWUR'] as $code) {
            $result = $this->service->calculateCartDiscount($carts, $this->outlet, $code);
            $this->assertEquals(0, $result['total_discount_amount'], 'Kode: ' . var_export($code, true));
            $this->assertSame(0.0, $result['line_percentages'][$syrup->id]);
            $this->assertSame([], $result['discount_details']);
        }

        // Dengan kode sales terdaftar: 2 karton per grup -> 2,5% / 2%.
        $result = $this->service->calculateCartDiscount($carts, $this->outlet, $this->salesCode);
        $this->assertSame(2.5, $result['line_percentages'][$syrup->id]);
        $this->assertSame(2.0, $result['line_percentages'][$pulp->id]);
    }
}
