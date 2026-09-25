<?php

namespace App\Support;

final class QadCreditTerms
{
    /**
     * @return array<string, array{label: string, days: int, immediate?: bool}>
     */
    public static function all(): array
    {
        return config('qad_credit_terms', []);
    }

    /**
     * Terms tempo (bukan CASH / CIA / COD), untuk dropdown TOP.
     *
     * @return array<string, array{label: string, days: int}>
     */
    public static function forTop(): array
    {
        return array_filter(self::all(), fn (array $term) => empty($term['immediate']));
    }

    /**
     * @return list<string>
     */
    public static function topCodes(): array
    {
        return array_keys(self::forTop());
    }

    public static function daysFromCode(?string $code): int
    {
        $code = strtoupper(trim((string) $code));
        $term = self::all()[$code] ?? null;

        return (int) ($term['days'] ?? 0);
    }

    public static function label(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        $term = self::all()[$code] ?? null;

        return $term['label'] ?? null;
    }

    /**
     * Balikkan jumlah hari tersimpan ke kode QAD terdekat (untuk selected dropdown).
     */
    public static function codeFromDays(null|int|string $days): ?string
    {
        if ($days === null || $days === '') {
            return null;
        }

        $days = (int) $days;
        foreach (self::forTop() as $code => $term) {
            if ((int) $term['days'] === $days) {
                return $code;
            }
        }

        return null;
    }

    public static function optionLabel(string $code, array $term): string
    {
        return (string) (int) ($term['days'] ?? 0);
    }
}
