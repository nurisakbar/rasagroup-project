<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Diskon berjenjang (MOQ) untuk outlet & subdist/reseller:
 *
 * | Tipe customer      | MOQ (total karton) | Syrup & Sauce | Pulp, Powder, Cocoa, dll |
 * |--------------------|--------------------|---------------|--------------------------|
 * | Subdist / Reseller | > 50 karton        | 15%           | 8%                       |
 * | Outlet             | 1–2 karton         | 2,5%          | 2%                       |
 * | Outlet             | 3–8 karton         | 5%            | 4%                       |
 * | Outlet             | 9–18 karton        | 7,5%          | 5%                       |
 * | Outlet             | 19–30 karton       | 10%           | 6%                       |
 *
 * MOQ dihitung PER GRUP: karton Syrup & Sauce dijumlah sendiri, karton Pulp/Powder/Cocoa dsb dijumlah
 * sendiri (memakai isi karton masing-masing produk). Tiap grup mendapat persen dari tier kartonnya sendiri.
 *
 * Diskon hanya berlaku bila pembeli menginput kode sales yang terdaftar (sales atau distributor),
 * kecuali reseller (outlet atau user biasa yang ditandai admin) yang selalu memakai tier "Subdist / Reseller"
 * tanpa kode sales. Reseller yang belanja 50 karton atau kurang per grup tidak mendapat diskon.
 */
class DiscountService
{
    /**
     * Kategori grup 1 (Syrup & Sauce)
     */
    private const GROUP_SYRUP_SAUCE = ['Syrup', 'SAUCE'];

    /**
     * Kategori grup 2 (Pulp, Powder, Cocoa, dsb)
     */
    private const GROUP_OTHERS = ['PULP', 'POWDER', 'COCOA', 'PUREE', 'TEA', 'TOPPING', 'JELLY', 'RAMOE'];

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
            return $cartons > self::RESELLER_MIN_CARTONS_EXCLUSIVE ? ($isSyrupSauce ? 15.0 : 8.0) : 0.0;
        }

        return match (true) {
            $cartons >= 19 => $isSyrupSauce ? 10.0 : 6.0,
            $cartons >= 9 => $isSyrupSauce ? 7.5 : 5.0,
            $cartons >= 3 => $isSyrupSauce ? 5.0 : 4.0,
            $cartons >= 1 => $isSyrupSauce ? 2.5 : 2.0,
            default => 0.0,
        };
    }

    /** Batas karton tier reseller: lebih dari angka ini per grup baru mendapat diskon. */
    public const RESELLER_MIN_CARTONS_EXCLUSIVE = 50;

    /**
     * Info untuk reseller: grup yang belum mencapai tier reseller dan kekurangan kartonnya.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @return list<array{group_name: string, cartons: int, cartons_needed: int, percentage: float}>
     */
    public function resellerHints($carts, User $user): array
    {
        if (! $user->isReseller()) {
            return [];
        }

        $hints = [];
        foreach ($this->cartonsPerGroup($carts) as $group => $cartons) {
            if ($cartons <= 0 || $cartons > self::RESELLER_MIN_CARTONS_EXCLUSIVE) {
                continue;
            }
            $hints[] = [
                'group_name' => $group === 'syrup_sauce' ? 'Syrup & Sauce' : 'Powder, Pulp & Lainnya',
                'cartons' => $cartons,
                'cartons_needed' => self::RESELLER_MIN_CARTONS_EXCLUSIVE + 1 - $cartons,
                'percentage' => $this->tierPercentage($user, $group, self::RESELLER_MIN_CARTONS_EXCLUSIVE + 1),
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
     * reseller (status reseller diberikan admin, tidak perlu kode sales).
     */
    public function isEligible(?string $salesCode, ?User $user = null): bool
    {
        return ($user && $user->isReseller()) || User::isKnownSalesCode($salesCode);
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

            // Item dengan diskon dari distributor perekrut (di hub distributor): diskon MOQ tidak berlaku.
            if ($group && $user->outletDistributorDiscountFor($product, $cartItem->warehouse_id) > 0) {
                $group = null;
            }

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
        return $user->isReseller() ? 'subdist' : 'outlet';
    }
}
