<?php

namespace App\Support;

use App\Models\User;
use App\Services\QidApiService;

final class QadExistingCustomer
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function parseList(?array $result): array
    {
        $data = $result['data'] ?? [];
        if (isset($data['customerCode'])) {
            return [$data];
        }

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, 'is_array'));
    }

    public static function normalizeName(?string $name): string
    {
        $name = strtolower(trim((string) $name));
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? '';
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        $name = preg_replace('/^gudang\s+/', '', $name) ?? '';

        return $name;
    }

    public static function addressSearchTerm(?string $name): ?string
    {
        $stop = ['pt', 'cv', 'ud', 'tbk', 'ltd', 'the', 'dan', 'and', 'co', 'company'];
        foreach (explode(' ', self::normalizeName($name)) as $token) {
            if (strlen($token) >= 4 && ! in_array($token, $stop, true)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Cari customer yang sudah ada di QAD (bukan membuat baru).
     */
    public static function findCode(QidApiService $qid, User $user): ?string
    {
        $term = self::addressSearchTerm($user->name);
        if ($term === null) {
            return null;
        }

        $result = $qid->get('/api/master/customer/list', ['addressSearchName' => $term]);
        $customers = self::parseList(is_array($result) ? $result : null);
        $match = self::pickBest($customers, $user);
        if ($match === null) {
            return null;
        }

        $code = trim((string) ($match['customerCode'] ?? ''));
        if ($code === '') {
            return null;
        }

        $taken = User::query()
            ->where('qad_customer_code', $code)
            ->where('id', '!=', $user->id)
            ->exists();

        return $taken ? null : $code;
    }

    /**
     * @param  array<int, array<string, mixed>>  $customers
     * @return array<string, mixed>|null
     */
    public static function pickBest(array $customers, User $user): ?array
    {
        $target = self::normalizeName($user->name);
        if ($target === '') {
            return null;
        }

        $best = null;
        $bestScore = 0;
        $ties = 0;

        foreach ($customers as $c) {
            $code = trim((string) ($c['customerCode'] ?? ''));
            if ($code === '') {
                continue;
            }

            $qadName = self::normalizeName($c['businessRelationName'] ?? $c['addressName'] ?? '');
            if ($qadName === '') {
                continue;
            }

            $score = 0;
            if ($qadName === $target) {
                $score = 100;
            } elseif (str_starts_with($target, $qadName) || str_starts_with($qadName, $target)) {
                $score = 70;
            } else {
                continue;
            }

            if (str_starts_with(strtoupper($code), 'CS')) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $best = $c;
                $bestScore = $score;
                $ties = 1;
            } elseif ($score === $bestScore) {
                $ties++;
            }
        }

        if ($best === null || $ties > 1 || $bestScore < 80) {
            return null;
        }

        return $best;
    }
}
