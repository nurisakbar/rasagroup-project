<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Strata diskon untuk outlet, subdist, & reseller:
 *
 * | Tipe customer | MOQ (total karton) | DRiPP Syrup & Sauce | DRiPP Pulp, Powder, Cocoa, Accesoris | Multibev Syrup | Multibev Powder & Creamer |
 * |---------------|--------------------|---------------------|--------------------------------------|----------------|---------------------------|
 * | Reseller      | Tanpa MOQ          | 15%                 | 8%                                   | 10%            | 7%                        |
 * | Subdist       | > 50 karton        | 15%                 | 8%                                   | 10%            | 7%                        |
 * | Outlet        | 1-2 karton         | 2.5%                | 2%                                   | 2.5%           | 2%                        |
 * | Outlet        | 3-8 karton         | 5%                  | 4%                                   | 5%             | 4%                        |
 * | Outlet        | 9-18 karton        | 7.5%                | 5%                                   | 7.5%           | 5%                        |
 * | Outlet        | 19-30 karton       | 10%                 | 6%                                   | 10%            | 6%                        |
 * | Outlet        | > 30 karton        | 10%                 | 6% (tier tertinggi)                  | 10%            | 6% (tier tertinggi)       |
 *
 * Reseller (flag `is_reseller = true`) langsung mendapat diskon sesuai brand & kategori tanpa syarat MOQ
 * dan tanpa kode sales. Subdist (role `subdist` non-reseller) tetap memakai syarat > 50 karton per grup
 * tanpa kode sales. Outlet dan ecom hanya mendapat diskon bila menginput kode sales yang terdaftar.
 */
class DiscountService
{
    /**
     * Kategori grup 1 (Syrup & Sauce)
     */
    private const GROUP_SYRUP_SAUCE = ['Syrup', 'SAUCE'];

    /**
     * Kategori grup 2 (Pulp, Powder, Cocoa, Creamer, Accessories) sesuai tabel referensi diskon.
     * Kategori lain di luar tabel (mis. Puree, Tea, Topping, Paste) tidak mendapat diskon MOQ.
     */
    private const GROUP_OTHERS = ['PULP', 'POWDER', 'COCOA', 'CREAMER', 'ACCESSORIES', 'ACCESORIS', 'AKSESORIS'];

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
     * Apakah produk termasuk brand Multibev.
     */
    public function isMultibev(?Product $product): bool
    {
        if (! $product) {
            return false;
        }

        $brandName = trim((string) ($product->brand?->name ?? ''));
        if ($brandName !== '') {
            return str_contains(strtoupper($brandName), 'MULTIBEV');
        }

        return str_contains(strtoupper((string) ($product->name ?? '')), 'MULTIBEV');
    }

    /**
     * Persen diskon sebuah grup berdasarkan tipe customer, jumlah karton grup tersebut, dan brand produk.
     */
    public function tierPercentage(User $user, string $group, int $cartons, ?Product $product = null): float
    {
        $isSyrupSauce = $group === 'syrup_sauce';
        $isMultibev = $this->isMultibev($product);

        // Reseller (is_reseller = true): langsung dapat diskon sesuai brand & kategori, abaikan jumlah orderan.
        if ($user->isReseller()) {
            if ($isMultibev) {
                return $isSyrupSauce ? 10.0 : 7.0;
            }

            return $isSyrupSauce ? 15.0 : 8.0;
        }

        if ($this->getCustomerType($user) === 'subdist') {
            if ($cartons <= self::SUBDIST_MIN_CARTONS_EXCLUSIVE) {
                return 0.0;
            }

            if ($isMultibev) {
                return $isSyrupSauce ? 10.0 : 7.0;
            }

            return $isSyrupSauce ? 15.0 : 8.0;
        }

        // Di atas 30 karton tetap memakai diskon maksimal (tier 19-30 karton).
        return match (true) {
            $cartons >= 19 => $isSyrupSauce ? 10.0 : 6.0,
            $cartons >= 9 => $isSyrupSauce ? 7.5 : 5.0,
            $cartons >= 3 => $isSyrupSauce ? 5.0 : 4.0,
            $cartons >= 1 => $isSyrupSauce ? 2.5 : 2.0,
            default => 0.0,
        };
    }

    /** Batas karton tier subdist: lebih dari angka ini per grup baru mendapat diskon. */
    public const SUBDIST_MIN_CARTONS_EXCLUSIVE = 50;

    /**
     * Info untuk subdist (non-reseller): grup yang belum mencapai tier subdist dan kekurangan kartonnya.
     * Reseller tidak butuh MOQ sehingga tidak menampilkan hint.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @return list<array{group_name: string, cartons: int, cartons_needed: int, percentage: float}>
     */
    public function subdistHints($carts, User $user): array
    {
        if (! $user->isSubdist() || $user->isReseller()) {
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
     * subdist / reseller (tidak perlu kode sales).
     */
    public function isEligible(?string $salesCode, ?User $user = null): bool
    {
        return ($user && $user->usesSubdistTier()) || User::isKnownSalesCode($salesCode);
    }

    /**
     * Menghitung diskon berjenjang untuk seluruh isi keranjang.
     * Mengembalikan rincian diskon per grup, total diskon, karton per grup, dan persen per baris.
     * Tanpa kode sales yang valid (untuk non-subdist/non-reseller) semua persen 0.
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

        $buckets = [];
        $lines = [];
        foreach ($carts as $cartItem) {
            $product = $cartItem->product;
            $group = $product ? $this->getProductCategoryGroup($product) : null;
            $percentage = ($eligible && $group)
                ? $this->tierPercentage($user, $group, (int) ($cartons[$group] ?? 0), $product)
                : 0.0;

            $lines[(string) $cartItem->id] = $percentage;

            if (! $group || $percentage <= 0) {
                continue;
            }

            $lineSubtotal = $user->getProductPrice($product) * (int) $cartItem->quantity;
            if ($lineSubtotal <= 0) {
                continue;
            }

            $label = $this->discountGroupLabel($group, $product, $user);
            $bucketKey = $label . '|' . $percentage;
            if (! isset($buckets[$bucketKey])) {
                $buckets[$bucketKey] = [
                    'group_name' => $label,
                    'percentage' => $percentage,
                    'subtotal' => 0.0,
                ];
            }
            $buckets[$bucketKey]['subtotal'] += $lineSubtotal;
        }

        $discountDetails = [];
        $totalDiscountAmount = 0.0;
        foreach ($buckets as $bucket) {
            $discountAmount = ($bucket['subtotal'] * $bucket['percentage']) / 100;
            $totalDiscountAmount += $discountAmount;
            $discountDetails[] = [
                'group_name' => $bucket['group_name'],
                'percentage' => $bucket['percentage'],
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

    private function discountGroupLabel(string $group, ?Product $product, User $user): string
    {
        if ($user->usesSubdistTier() && $this->isMultibev($product)) {
            return $group === 'syrup_sauce' ? 'Multibev Syrup' : 'Multibev Powder & Creamer';
        }

        return $group === 'syrup_sauce' ? 'Syrup & Sauce' : 'Powder, Pulp & Lainnya';
    }

    /**
     * Menentukan apakah pembeli ini bertipe 'subdist' atau 'outlet'.
     */
    private function getCustomerType(User $user): string
    {
        return $user->usesSubdistTier() ? 'subdist' : 'outlet';
    }
}
