<?php

namespace App\Support;

class ApiKey
{
    /**
     * Kunci bersama RasaConnect (RASACONNECT_API_KEY). Kunci lama tiap service tetap berlaku
     * berdampingan dengannya, sehingga klien yang sudah jalan tidak perlu diubah.
     */
    public static function shared(): string
    {
        return (string) config('services.rasaconnect.api_key');
    }

    /**
     * True bila $provided sama dengan salah satu kunci yang diterima (kunci kosong diabaikan).
     */
    public static function matches(?string $provided, string ...$accepted): bool
    {
        $provided = (string) $provided;
        if ($provided === '') {
            return false;
        }

        foreach ($accepted as $key) {
            if ($key !== '' && hash_equals($key, $provided)) {
                return true;
            }
        }

        return false;
    }

    public static function anyConfigured(string ...$keys): bool
    {
        return count(array_filter($keys, fn ($k) => $k !== '')) > 0;
    }
}
