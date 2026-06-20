<?php

namespace App\Support\MasterSync;

use App\Support\ProductCodeMatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProductMatchDebugLogger
{
    public static function isEnabled(): bool
    {
        return (bool) config('master_sync.product_match_debug', false);
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>  $variant
     * @return array<string, mixed>
     */
    public static function formatJubelioEntry(array $group, array $variant): array
    {
        $code = ProductCodeMatcher::normalize($variant['item_code'] ?? null);

        return [
            'source' => 'jubelio',
            'code' => $code,
            'code_upper' => $code ? strtoupper($code) : null,
            'code_raw' => $variant['item_code'] ?? null,
            'code_normalized' => self::normalizeCodeForMatch($code),
            'name' => $variant['item_name'] ?? $group['item_name'] ?? null,
            'name_normalized' => self::normalizeName($variant['item_name'] ?? $group['item_name'] ?? null),
            'group_name' => $group['item_name'] ?? null,
            'item_id' => $variant['item_id'] ?? null,
            'item_group_id' => $group['item_group_id'] ?? $group['id'] ?? null,
            'sell_price' => $variant['sell_price'] ?? $group['sell_price'] ?? null,
            'barcode' => $variant['barcode'] ?? $variant['bar_code'] ?? null,
            'sku' => $variant['sku'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function formatQadEntry(array $item): array
    {
        $code = ProductCodeMatcher::normalize($item['itemCode'] ?? $item['item_code'] ?? null);
        $name = $item['description'] ?? $item['item_name'] ?? null;

        return [
            'source' => 'qad',
            'code' => $code,
            'code_upper' => $code ? strtoupper($code) : null,
            'code_raw' => $item['itemCode'] ?? $item['item_code'] ?? null,
            'code_normalized' => self::normalizeCodeForMatch($code),
            'name' => $name,
            'name_normalized' => self::normalizeName($name),
            'brand' => $item['brand'] ?? null,
            'category' => $item['category'] ?? null,
            'uom' => $item['uom'] ?? null,
            'sizing' => $item['sizing'] ?? null,
            'default_price' => $item['defaultPrice'] ?? null,
            'status' => $item['status'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $jubelioEntries
     * @param  array<int, array<string, mixed>>  $qadEntries
     */
    public static function compare(array $jubelioEntries, array $qadEntries): void
    {
        if (! self::isEnabled()) {
            return;
        }

        $jubelioByCode = self::indexByUpperCode($jubelioEntries);
        $qadByCode = self::indexByUpperCode($qadEntries);

        $jubelioCodes = array_keys($jubelioByCode);
        $qadCodes = array_keys($qadByCode);

        $exactMatches = array_values(array_intersect($jubelioCodes, $qadCodes));
        $jubelioOnly = array_values(array_diff($jubelioCodes, $qadCodes));
        $qadOnly = array_values(array_diff($qadCodes, $jubelioCodes));

        $nameMatchesDifferentCode = self::findNameMatchesDifferentCode($jubelioEntries, $qadEntries);
        $normalizedCodeMatches = self::findNormalizedCodeMatches($jubelioEntries, $qadEntries);
        $partialCodeMatches = self::findPartialCodeMatches($jubelioOnly, $qadOnly, $jubelioByCode, $qadByCode);

        $report = [
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'jubelio_total' => count($jubelioEntries),
                'qad_total' => count($qadEntries),
                'exact_code_match' => count($exactMatches),
                'jubelio_only' => count($jubelioOnly),
                'qad_only' => count($qadOnly),
                'name_match_different_code' => count($nameMatchesDifferentCode),
                'normalized_code_match' => count($normalizedCodeMatches),
                'partial_code_match' => count($partialCodeMatches),
            ],
            'exact_matches' => self::buildExactMatchPairs($exactMatches, $jubelioByCode, $qadByCode),
            'jubelio_only' => self::pickEntries($jubelioOnly, $jubelioByCode),
            'qad_only' => self::pickEntries($qadOnly, $qadByCode),
            'name_match_different_code' => $nameMatchesDifferentCode,
            'normalized_code_match' => $normalizedCodeMatches,
            'partial_code_match' => $partialCodeMatches,
            'jubelio_sample_raw' => self::sampleRaw($jubelioEntries, 'jubelio'),
            'qad_sample_raw' => self::sampleRaw($qadEntries, 'qad'),
        ];

        self::writeJsonReport($report);
        self::writeLogSummary($report);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    public static function logSampleRaw(string $source, array $entries, ?array $rawSample = null): void
    {
        if (! self::isEnabled()) {
            return;
        }

        Log::channel('master_sync')->debug("ProductMatchDebug: sample raw {$source}", [
            'count' => count($entries),
            'sample_entry' => $entries[0] ?? null,
            'sample_raw_api' => $rawSample,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, array<string, mixed>>
     */
    private static function indexByUpperCode(array $entries): array
    {
        $indexed = [];

        foreach ($entries as $entry) {
            $upper = $entry['code_upper'] ?? null;

            if (! $upper) {
                continue;
            }

            $indexed[$upper] = $entry;
        }

        return $indexed;
    }

    /**
     * @param  array<int, string>  $codes
     * @param  array<string, array<string, mixed>>  $indexed
     * @return array<int, array<string, mixed>>
     */
    private static function pickEntries(array $codes, array $indexed): array
    {
        $result = [];

        foreach ($codes as $code) {
            if (isset($indexed[$code])) {
                $result[] = $indexed[$code];
            }
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $codes
     * @param  array<string, array<string, mixed>>  $jubelioByCode
     * @param  array<string, array<string, mixed>>  $qadByCode
     * @return array<int, array<string, mixed>>
     */
    private static function buildExactMatchPairs(array $codes, array $jubelioByCode, array $qadByCode): array
    {
        $pairs = [];

        foreach ($codes as $code) {
            $pairs[] = [
                'code' => $code,
                'jubelio' => $jubelioByCode[$code] ?? null,
                'qad' => $qadByCode[$code] ?? null,
            ];
        }

        return $pairs;
    }

    /**
     * @param  array<int, array<string, mixed>>  $jubelioEntries
     * @param  array<int, array<string, mixed>>  $qadEntries
     * @return array<int, array<string, mixed>>
     */
    private static function findNameMatchesDifferentCode(array $jubelioEntries, array $qadEntries): array
    {
        $qadByName = [];

        foreach ($qadEntries as $qad) {
            $nameKey = $qad['name_normalized'] ?? null;

            if (! $nameKey) {
                continue;
            }

            $qadByName[$nameKey][] = $qad;
        }

        $matches = [];

        foreach ($jubelioEntries as $jubelio) {
            $nameKey = $jubelio['name_normalized'] ?? null;

            if (! $nameKey || ! isset($qadByName[$nameKey])) {
                continue;
            }

            foreach ($qadByName[$nameKey] as $qad) {
                if (($jubelio['code_upper'] ?? null) === ($qad['code_upper'] ?? null)) {
                    continue;
                }

                $matches[] = [
                    'name' => $jubelio['name'],
                    'jubelio_code' => $jubelio['code'],
                    'qad_code' => $qad['code'],
                    'jubelio' => $jubelio,
                    'qad' => $qad,
                ];
            }
        }

        return $matches;
    }

    /**
     * @param  array<int, array<string, mixed>>  $jubelioEntries
     * @param  array<int, array<string, mixed>>  $qadEntries
     * @return array<int, array<string, mixed>>
     */
    private static function findNormalizedCodeMatches(array $jubelioEntries, array $qadEntries): array
    {
        $qadByNormalized = [];

        foreach ($qadEntries as $qad) {
            $key = $qad['code_normalized'] ?? null;

            if (! $key) {
                continue;
            }

            $qadByNormalized[$key][] = $qad;
        }

        $matches = [];

        foreach ($jubelioEntries as $jubelio) {
            $key = $jubelio['code_normalized'] ?? null;

            if (! $key || ! isset($qadByNormalized[$key])) {
                continue;
            }

            foreach ($qadByNormalized[$key] as $qad) {
                if (($jubelio['code_upper'] ?? null) === ($qad['code_upper'] ?? null)) {
                    continue;
                }

                $matches[] = [
                    'normalized_code' => $key,
                    'jubelio_code' => $jubelio['code'],
                    'qad_code' => $qad['code'],
                    'jubelio_name' => $jubelio['name'],
                    'qad_name' => $qad['name'],
                ];
            }
        }

        return $matches;
    }

    /**
     * @param  array<int, string>  $jubelioOnly
     * @param  array<int, string>  $qadOnly
     * @param  array<string, array<string, mixed>>  $jubelioByCode
     * @param  array<string, array<string, mixed>>  $qadByCode
     * @return array<int, array<string, mixed>>
     */
    private static function findPartialCodeMatches(
        array $jubelioOnly,
        array $qadOnly,
        array $jubelioByCode,
        array $qadByCode
    ): array {
        $matches = [];

        foreach ($jubelioOnly as $jCode) {
            $jubelio = $jubelioByCode[$jCode] ?? null;

            if (! $jubelio) {
                continue;
            }

            $jNorm = $jubelio['code_normalized'] ?? '';

            if ($jNorm === '') {
                continue;
            }

            foreach ($qadOnly as $qCode) {
                $qad = $qadByCode[$qCode] ?? null;

                if (! $qad) {
                    continue;
                }

                $qNorm = $qad['code_normalized'] ?? '';

                if ($qNorm === '' || $jNorm === $qNorm) {
                    continue;
                }

                if (str_contains($jNorm, $qNorm) || str_contains($qNorm, $jNorm)) {
                    $matches[] = [
                        'jubelio_code' => $jubelio['code'],
                        'qad_code' => $qad['code'],
                        'jubelio_code_normalized' => $jNorm,
                        'qad_code_normalized' => $qNorm,
                        'jubelio_name' => $jubelio['name'],
                        'qad_name' => $qad['name'],
                    ];
                }
            }
        }

        return $matches;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, mixed>|null
     */
    private static function sampleRaw(array $entries, string $source): ?array
    {
        if ($entries === []) {
            return null;
        }

        return [
            'source' => $source,
            'formatted_sample' => $entries[0],
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private static function writeJsonReport(array $report): void
    {
        $path = storage_path('logs/product-match-debug.json');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private static function writeLogSummary(array $report): void
    {
        $summary = $report['summary'] ?? [];

        Log::channel('master_sync')->debug('ProductMatchDebug: ringkasan pencocokan SKU Jubelio vs QAD', $summary);

        Log::channel('master_sync')->debug('ProductMatchDebug: contoh exact match', [
            'samples' => array_slice($report['exact_matches'] ?? [], 0, 10),
        ]);

        Log::channel('master_sync')->debug('ProductMatchDebug: Jubelio saja (tidak ada di QAD)', [
            'samples' => array_slice($report['jubelio_only'] ?? [], 0, 20),
        ]);

        Log::channel('master_sync')->debug('ProductMatchDebug: QAD saja (tidak ada di Jubelio)', [
            'samples' => array_slice($report['qad_only'] ?? [], 0, 20),
        ]);

        Log::channel('master_sync')->debug('ProductMatchDebug: nama sama, kode berbeda (kandidat mapping)', [
            'samples' => array_slice($report['name_match_different_code'] ?? [], 0, 20),
        ]);

        Log::channel('master_sync')->debug('ProductMatchDebug: kode normalized sama, format berbeda', [
            'samples' => array_slice($report['normalized_code_match'] ?? [], 0, 20),
        ]);

        Log::channel('master_sync')->debug('ProductMatchDebug: kode partial match (satu mengandung yang lain)', [
            'samples' => array_slice($report['partial_code_match'] ?? [], 0, 20),
        ]);

        Log::channel('master_sync')->info('ProductMatchDebug: laporan lengkap disimpan', [
            'json' => storage_path('logs/product-match-debug.json'),
        ]);
    }

    private static function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', mb_strtolower(trim($name)));

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeCodeForMatch(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(trim($code)));

        return $normalized === '' ? null : $normalized;
    }
}
