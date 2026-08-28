<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

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
     * Menghitung diskon berjenjang untuk seluruh isi keranjang berdasarkan akumulasi kategori produk.
     * Mengembalikan array berisi subtotal dan rincian diskon per kategori.
     *
     * @param \Illuminate\Support\Collection<int, Cart> $carts
     * @param User $user
     * @return array
     */
    public function calculateCartDiscount($carts, User $user): array
    {
        // Pengelompokan kuantitas dan subtotal
        $groupStats = [
            'syrup_sauce' => ['quantity' => 0, 'subtotal' => 0],
            'others'      => ['quantity' => 0, 'subtotal' => 0],
        ];

        // Hitung total kuantitas per grup dan subtotal per grup
        foreach ($carts as $cartItem) {
            $product = $cartItem->product;
            $quantity = $cartItem->quantity;
            $originalPrice = $user->getProductPrice($product);
            $subtotal = $originalPrice * $quantity;

            $group = $this->getProductCategoryGroup($product);
            if ($group) {
                $groupStats[$group]['quantity'] += $quantity;
                $groupStats[$group]['subtotal'] += $subtotal;
            }
        }

        $customerType = $this->getCustomerType($user);
        $discountDetails = [];
        $totalDiscountAmount = 0;

        foreach ($groupStats as $group => $stats) {
            if ($stats['quantity'] == 0) continue;

            $karton = floor($stats['quantity'] / 6);
            if ($karton < 1) continue;

            $isSyrupSauce = ($group === 'syrup_sauce');
            $discountPercentage = 0;

            if ($customerType === 'subdist') {
                if ($karton > 50) {
                    $discountPercentage = $isSyrupSauce ? 15 : 8;
                }
            } else { // Outlet
                if ($karton >= 1 && $karton <= 2) {
                    $discountPercentage = $isSyrupSauce ? 2.5 : 2;
                } elseif ($karton >= 3 && $karton <= 8) {
                    $discountPercentage = $isSyrupSauce ? 5 : 4;
                } elseif ($karton >= 9 && $karton <= 18) {
                    $discountPercentage = $isSyrupSauce ? 7.5 : 5;
                } elseif ($karton >= 19) {
                    $discountPercentage = $isSyrupSauce ? 10 : 6;
                }
            }

            if ($discountPercentage > 0) {
                $discountAmount = ($stats['subtotal'] * $discountPercentage) / 100;
                $totalDiscountAmount += $discountAmount;
                
                $groupName = $isSyrupSauce ? 'Syrup & Sauce' : 'Powder, Pulp & Lainnya';
                $discountDetails[] = [
                    'group_name' => $groupName,
                    'percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                ];
            }
        }

        return [
            'discount_details' => $discountDetails,
            'total_discount_amount' => $totalDiscountAmount,
        ];
    }

    /**
     * Menentukan apakah pembeli ini bertipe 'subdist' atau 'outlet'.
     */
    private function getCustomerType(User $user): string
    {
        $type = 'outlet';
        if ($user->sub_role === 'reseller' || $user->sub_role === 'subdist') {
            $type = 'subdist';
        }
        return $type;
    }
}
