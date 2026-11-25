<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Strata diskon MOQ untuk outlet & subdist:
 *
 * | Tipe customer | MOQ (total karton) | Syrup & Sauce | Pulp, Powder, Cocoa, dll |
 * |---------------|--------------------|---------------|--------------------------|
 * | Subdist       | > 50 karton        | 10%           | 7%                       |
 * | Outlet        | 1-2 karton         | 0%            | 0%                       |
 * | Outlet        | 3-5 karton         | 4%            | 2%                       |
 * | Outlet        | 6-10 karton        | 5%            | 4%                       |
 * | Outlet        | 11-20 karton       | 6%            | 5%                       |
 * | Outlet        | 21-32 karton       | 7%            | 6%                       |
 * | Outlet        | > 32 karton        | 7%            | 6% (tier tertinggi)      |
 *
 * MOQ dihitung PER GRUP: karton Syrup & Sauce dijumlah sendiri, karton Pulp/Powder/Cocoa dsb dijumlah
 * sendiri (memakai isi karton masing-masing produk). Tiap grup mendapat persen dari tier kartonnya sendiri.
 *
 * Subdist = user yang mendaftar lewat kode distributor (role subdist) atau user yang ditandai admin
 * (flag reseller). Subdist selalu memakai tier "Subdist" tanpa kode sales; yang belanja 50 karton atau
 * kurang per grup tidak mendapat diskon. Outlet dan ecom hanya mendapat diskon bila menginput kode sales
 * yang terdaftar (sales atau distributor).
 */
class DiscountService
{
    /**
     * Kategori grup 1 (Syrup & Sauce)
     */
    private const GROUP_SYRUP_SAUCE = ['Syrup', 'SAUCE'];

    /**
     * Kategori grup 2 (Pulp, Powder, Cocoa, Accessories) sesuai tabel referensi diskon.
     * Kategori lain di luar tabel (mis. Puree, Tea, Topping, Paste) tidak mendapat diskon MOQ.
     */
    private const GROUP_OTHERS = ['PULP', 'POWDER', 'COCOA', 'ACCESSORIES', 'ACCESORIS', 'AKSESORIS'];

    /**
     * Isi karton bila produk tidak punya data satuan besar.
     */
    private const DEFAULT_UNITS_PER_CARTON = 6;

    /**
     * Menentukan grup kategori untuk sebuah produk.
     * Mengembalikan 'syrup_sauce', 'others', atau null jika tidak masuk kriteria.
     */
    public function getProductCategoryGroup(Product $product): ?string
    {
        $categoryName = $product->category ? $product->category->name : '';
        $isSyrupSauce = in_array(strtoupper($categoryName), array_map('strtoupper', self::GROUP_SYRUP_SAUCE));
        $isOthers = in_array(strtoupper($categoryName), array_map('strtoupper', self::GROUP_OTHERS));

        if ($isSyrupSauce) return 'syrup_sauce';
        if ($isOthers) return 'others';

        return null;
    }

    /**
     * Total karton (dibulatkan ke bawah) per grup produk.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @return array{syrup_sauce: int, others: int}
     */
    public function cartonsPerGroup($carts): array
    {
        $cartons = ['syrup_sauce' => 0.0, 'others' => 0.0];
        foreach ($carts as $cartItem) {
            $product = $cartItem->product;
            $group = $product ? $this->getProductCategoryGroup($product) : null;
            if (! $group) {
                continue;
            }

            $perCarton = $product->hasDualUnitOrdering()
                ? max(1, (int) $product->units_per_large)
                : self::DEFAULT_UNITS_PER_CARTON;
            $cartons[$group] += (int) $cartItem->quantity / $perCarton;
        }

        // Toleransi pembulatan float (mis. 6 × 1/6).
        return array_map(fn (float $value) => (int) floor($value + 1e-9), $cartons);
    }

    /**
     * Persen diskon sebuah grup berdasarkan tipe customer dan jumlah karton grup tersebut.
     */
    public function tierPercentage(User $user, string $group, int $cartons): float
    {
        $isSyrupSauce = $group === 'syrup_sauce';

        if ($this->getCustomerType($user) === 'subdist') {
            return $cartons > self::SUBDIST_MIN_CARTONS_EXCLUSIVE ? ($isSyrupSauce ? 10.0 : 7.0) : 0.0;
        }

        // Di atas 32 karton tetap memakai diskon maksimal (tier 21-32 karton).
        return match (true) {
            $cartons >= 21 => $isSyrupSauce ? 7.0 : 6.0,
            $cartons >= 11 => $isSyrupSauce ? 6.0 : 5.0,
            $cartons >= 6 => $isSyrupSauce ? 5.0 : 4.0,
            $cartons >= 3 => $isSyrupSauce ? 4.0 : 2.0,
            default => 0.0,
        };
    }

    /** Batas karton tier subdist: lebih dari angka ini per grup baru mendapat diskon. */
    public const SUBDIST_MIN_CARTONS_EXCLUSIVE = 50;

    /**
     * Info untuk subdist: grup yang belum mencapai tier subdist dan kekurangan kartonnya.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @return list<array{group_name: string, cartons: int, cartons_needed: int, percentage: float}>
     */
    public function subdistHints($carts, User $user): array
    {
        if (! $user->usesSubdistTier()) {
            return [];
        }

        $hints = [];
        foreach ($this->cartonsPerGroup($carts) as $group => $cartons) {
            if ($cartons <= 0 || $cartons > self::SUBDIST_MIN_CARTONS_EXCLUSIVE) {
                continue;
            }
            $hints[] = [
                'group_name' => $group === 'syrup_sauce' ? 'Syrup & Sauce' : 'Powder, Pulp & Lainnya',
                'cartons' => $cartons,
                'cartons_needed' => self::SUBDIST_MIN_CARTONS_EXCLUSIVE + 1 - $cartons,
                'percentage' => $this->tierPercentage($user, $group, self::SUBDIST_MIN_CARTONS_EXCLUSIVE + 1),
            ];
        }

        return $hints;
    }

    /**
     * Persen diskon untuk setiap baris keranjang (dikunci per id baris keranjang).
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @return array<string, float>
     */
    public function linePercentages($carts, User $user, ?string $salesCode = null): array
    {
        return $this->calculateCartDiscount($carts, $user, $salesCode)['line_percentages'];
    }

    /**
     * Diskon MOQ berlaku untuk pembelian dengan kode sales yang diinput dan terdaftar, atau untuk
     * subdist (tidak perlu kode sales).
     */
    public function isEligible(?string $salesCode, ?User $user = null): bool
    {
        return ($user && $user->usesSubdistTier()) || User::isKnownSalesCode($salesCode);
    }

    /**
     * Menghitung diskon berjenjang untuk seluruh isi keranjang.
     * Mengembalikan rincian diskon per grup, total diskon, karton per grup, dan persen per baris.
     * Tanpa kode sales yang valid semua persen 0.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @param User $user
     * @param string|null $salesCode  kode sales yang diinput pembeli saat checkout
     * @return array
     */
    public function calculateCartDiscount($carts, User $user, ?string $salesCode = null): array
    {
        $eligible = $this->isEligible($salesCode, $user);
        $cartons = $this->cartonsPerGroup($carts);
        $percentages = [];
        foreach ($cartons as $group => $count) {
            $percentages[$group] = $eligible ? $this->tierPercentage($user, $group, $count) : 0.0;
        }

        $groupSubtotals = ['syrup_sauce' => 0.0, 'others' => 0.0];
        $lines = [];
        foreach ($carts as $cartItem) {
            $product = $cartItem->product;
            $group = $product ? $this->getProductCategoryGroup($product) : null;
            $lines[(string) $cartItem->id] = $group ? $percentages[$group] : 0.0;
            if ($group) {
                $groupSubtotals[$group] += $user->getProductPrice($product) * (int) $cartItem->quantity;
            }
        }

        $discountDetails = [];
        $totalDiscountAmount = 0;
        foreach ($groupSubtotals as $group => $subtotal) {
            $percentage = $percentages[$group];
            if ($subtotal <= 0 || $percentage <= 0) {
                continue;
            }

            $discountAmount = ($subtotal * $percentage) / 100;
            $totalDiscountAmount += $discountAmount;
            $discountDetails[] = [
                'group_name' => $group === 'syrup_sauce' ? 'Syrup & Sauce' : 'Powder, Pulp & Lainnya',
                'percentage' => $percentage,
                'discount_amount' => $discountAmount,
            ];
        }

        return [
            'discount_details' => $discountDetails,
            'total_discount_amount' => $totalDiscountAmount,
            'cartons_per_group' => $cartons,
            'line_percentages' => $lines,
        ];
    }

    /**
     * Menentukan apakah pembeli ini bertipe 'subdist' atau 'outlet'.
     */
    private function getCustomerType(User $user): string
    {
        return $user->usesSubdistTier() ? 'subdist' : 'outlet';
    }
}
