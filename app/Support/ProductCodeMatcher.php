<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Scopes\SyncedInJubelioAndQadScope;

class ProductCodeMatcher
{
    public static function normalize(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $code = trim($code);

        return $code === '' ? null : $code;
    }

    public static function findProduct(?string $code): ?Product
    {
        $normalized = self::normalize($code);

        if (! $normalized) {
            return null;
        }

        return Product::withoutGlobalScope(SyncedInJubelioAndQadScope::class)
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper($normalized)])
            ->first();
    }

    /**
     * Buat atau perbarui produk berdasarkan kode/SKU (case-insensitive).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function upsert(?string $code, array $attributes, ?string $createdBy = null, ?string $syncSource = null): Product
    {
        $normalized = self::normalize($code);

        if (! $normalized) {
            throw new \InvalidArgumentException('Kode/SKU produk wajib diisi.');
        }

        $product = self::findProduct($normalized);

        if ($product) {
            $product->fill($attributes);
            $product->code = $normalized;

            if ($syncSource) {
                $product->markSyncSource($syncSource);
            }

            $product->save();

            return $product;
        }

        $product = Product::create(array_merge($attributes, [
            'code' => $normalized,
            'created_by' => $createdBy,
            'sync_sources' => $syncSource ? [$syncSource] : null,
        ]));

        return $product;
    }
}
