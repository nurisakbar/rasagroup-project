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
     * Hitung diskon untuk satu baris Cart (per SKU)
     *
     * @param Cart $cartItem
     * @param User $user
     * @return array
     */
    public function calculateCartItemDiscount(Cart $cartItem, User $user): array
    {
        $product = $cartItem->product;
        $quantity = $cartItem->quantity;
        
        // Default harga asli
        $originalPrice = $user->getProductPrice($product);
        $originalSubtotal = $originalPrice * $quantity;
        
        // 1. Cek apakah user berhak mendapat diskon berjenjang (Bukan distributor)
        if ($user->role === 'distributor') {
            return [
                'original_price' => $originalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'final_price' => $originalPrice,
                'original_subtotal' => $originalSubtotal,
                'final_subtotal' => $originalSubtotal,
            ];
        }

        // 2. Hitung jumlah karton (1 karton = 6 item)
        $karton = floor($quantity / 6);
        if ($karton < 1) {
            // Belum memenuhi minimum order quantity (MOQ)
            return [
                'original_price' => $originalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'final_price' => $originalPrice,
                'original_subtotal' => $originalSubtotal,
                'final_subtotal' => $originalSubtotal,
            ];
        }

        // 3. Tentukan tipe customer (Subdist/Reseller atau Outlet)
        $customerType = $this->getCustomerType($user);

        // 4. Tentukan grup kategori produk
        $categoryName = $product->category ? $product->category->name : '';
        $isSyrupSauce = in_array(strtoupper($categoryName), array_map('strtoupper', self::GROUP_SYRUP_SAUCE));
        $isOthers = in_array(strtoupper($categoryName), array_map('strtoupper', self::GROUP_OTHERS));

        // Jika tidak masuk ke grup manapun, tidak ada diskon
        if (!$isSyrupSauce && !$isOthers) {
            return [
                'original_price' => $originalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'final_price' => $originalPrice,
                'original_subtotal' => $originalSubtotal,
                'final_subtotal' => $originalSubtotal,
            ];
        }

        // 5. Hitung persentase diskon
        $discountPercentage = 0;
        if ($customerType === 'subdist') {
            if ($karton > 50) {
                $discountPercentage = $isSyrupSauce ? 15 : 8;
            }
        } else {
            // Outlet
            if ($karton >= 1 && $karton <= 2) {
                $discountPercentage = $isSyrupSauce ? 2.5 : 2;
            } elseif ($karton >= 3 && $karton <= 8) {
                $discountPercentage = $isSyrupSauce ? 5 : 4;
            } elseif ($karton >= 9 && $karton <= 18) {
                $discountPercentage = $isSyrupSauce ? 7.5 : 5;
            } elseif ($karton >= 19) { // Asumsi >30 juga mentok di sini
                $discountPercentage = $isSyrupSauce ? 10 : 6;
            }
        }

        // 6. Kalkulasi harga akhir
        $discountAmount = ($originalPrice * $discountPercentage) / 100;
        $finalPrice = $originalPrice - $discountAmount;
        $finalSubtotal = $finalPrice * $quantity;

        return [
            'original_price' => $originalPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'original_subtotal' => $originalSubtotal,
            'final_subtotal' => $finalSubtotal,
        ];
    }

    /**
     * Menentukan apakah pembeli ini bertipe 'subdist' atau 'outlet'.
     */
    private function getCustomerType(User $user): string
    {
        // Secara default, pembeli biasa (buyer) dianggap Outlet
        $type = 'outlet';
        
        // Jika ada identifier khusus untuk subdistributor/reseller, tambahkan di sini
        if ($user->sub_role === 'reseller' || $user->sub_role === 'subdist') {
            $type = 'subdist';
        }

        return $type;
    }
}
