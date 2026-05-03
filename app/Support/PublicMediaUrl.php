<?php

namespace App\Support;

/**
 * URL media publik untuk atribut HTML: path relatif dari root situs
 * sehingga selalu mengikuti domain permintaan (tidak terikat APP_URL/localhost).
 */
final class PublicMediaUrl
{
    public static function resolve(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        $stored = trim(str_replace('\\', '/', $stored));

        if (filter_var($stored, FILTER_VALIDATE_URL)) {
            if (preg_match('#^https?://[^/]+(/storage/.+)$#i', $stored, $m)) {
                return $m[1];
            }

            return $stored;
        }

        if (str_starts_with($stored, 'themes/')) {
            return '/'.ltrim($stored, '/');
        }

        return '/storage/'.ltrim($stored, '/');
    }

    /**
     * Path relatif di disk `public` untuk hapus file (abaikan URL absolut & aset tema).
     */
    public static function storagePathForDelete(?string $stored): ?string
    {
        if (! $stored || filter_var($stored, FILTER_VALIDATE_URL)) {
            return null;
        }
        if (str_starts_with($stored, 'themes/')) {
            return null;
        }
        if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $stored, $m)) {
            return $m[1];
        }

        return ltrim(str_replace('\\', '/', $stored), '/');
    }
}
