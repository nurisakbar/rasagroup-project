<?php

namespace App\Services;

use App\Models\QadInventory;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WmsService
{
    /** @var array<string, array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>|null> */
    private static array $requestCache = [];

    public static function locationCode(?Warehouse $warehouse): ?string
    {
        if (! $warehouse) {
            return null;
        }

        return $warehouse->qad_location_code ?: $warehouse->kode_hub ?: null;
    }

    public function isConfigured(): bool
    {
        return filled(config('services.wms.api_url'))
            && filled(config('services.wms.api_key'));
    }

    public function listBatchItems(array $payload): ?array
    {
        if (! $this->isConfigured()) {
            Log::warning('WmsService: WMS API is not configured.');

            return null;
        }

        $url = rtrim((string) config('services.wms.api_url'), '/') . '/batch-items';

        $response = Http::withHeaders([
            'x-api-key' => (string) config('services.wms.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($url, $payload);

        if (! $response->successful()) {
            Log::error('WmsService: batch-items failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * @param  array<int, mixed>  $rows
     * @param  array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>  $grouped
     */
    private function mergeBatchRows(array &$grouped, array $rows, ?string $onlyItemCode = null): void
    {
        $wanted = $onlyItemCode !== null ? strtoupper(trim($onlyItemCode)) : null;

        foreach ($rows as $row) {
            $itemCode = $row['location']['item']['code'] ?? null;
            $batches = $row['location']['item']['batches'] ?? [];
            if (! is_string($itemCode) || $itemCode === '' || ! is_array($batches)) {
                continue;
            }

            if ($wanted !== null && strtoupper(trim($itemCode)) !== $wanted) {
                continue;
            }

            foreach ($batches as $batch) {
                $lotSerial = trim((string) ($batch['code'] ?? $batch['batch_number'] ?? ''));
                if ($lotSerial === '' || str_contains($lotSerial, '-')) {
                    continue;
                }

                $grouped[$itemCode][] = [
                    'lot_serial' => $lotSerial,
                    'qty' => (int) ($batch['quantity'] ?? $batch['qty'] ?? 0),
                    'expired' => $batch['expired_at'] ?? $batch['expired'] ?? null,
                ];
            }
        }
    }

    /**
     * Ambil batch WMS hanya untuk daftar item yang dipesan (bukan seluruh hub).
     *
     * @param  list<string>  $itemCodes
     * @return array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>
     */
    public function batchesForItemCodes(string $locationCode, array $itemCodes, int $minMasaBerlakuBulan = 0): array
    {
        $codes = [];
        foreach ($itemCodes as $code) {
            $normalized = strtoupper(trim((string) $code));
            if ($normalized !== '') {
                $codes[$normalized] = trim((string) $code);
            }
        }

        if ($codes === []) {
            return [];
        }

        $grouped = [];

        foreach ($codes as $code) {
            $page = 1;
            $lastPage = 1;
            $maxPages = 5;

            do {
                $response = $this->listBatchItems([
                    'location_code' => $locationCode,
                    'item_code' => $code,
                    'page' => $page,
                    'per_page' => 50,
                ]);

                if (! is_array($response)) {
                    throw new \RuntimeException('WMS batch-items gagal untuk item '.$code.' di lokasi '.$locationCode);
                }

                $rows = $response['data'] ?? [];
                if (is_array($rows)) {
                    $this->mergeBatchRows($grouped, $rows, $code);
                }

                $lastPage = (int) ($response['meta']['pagination']['last_page'] ?? $page);
                $page++;
            } while ($page <= $lastPage && $page <= $maxPages);
        }

        return $this->normalizeBatches($grouped, $minMasaBerlakuBulan);
    }

    /**
     * Ambil semua batch WMS untuk satu lokasi, dikelompokkan per item_code (FEFO).
     *
     * @return array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>|null
     */
    public function batchesByItemCode(string $locationCode, int $minMasaBerlakuBulan = 0): ?array
    {
        $cacheKey = $locationCode . '|' . $minMasaBerlakuBulan;
        if (array_key_exists($cacheKey, self::$requestCache)) {
            return self::$requestCache[$cacheKey];
        }

        $grouped = [];
        $page = 1;
        $lastPage = 1;
        $maxPages = 50;
        $fetched = false;

        do {
            $response = $this->listBatchItems([
                'location_code' => $locationCode,
                'page' => $page,
                'per_page' => 100,
            ]);

            if (! is_array($response)) {
                break;
            }

            $fetched = true;
            $rows = $response['data'] ?? [];
            if (! is_array($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $this->mergeBatchRows($grouped, [$row]);
            }

            $lastPage = (int) ($response['meta']['pagination']['last_page'] ?? $page);
            $page++;
        } while ($page <= $lastPage && $page <= $maxPages);

        if (! $fetched) {
            $fromCache = $this->batchesFromCache($locationCode, $minMasaBerlakuBulan);
            self::$requestCache[$cacheKey] = $fromCache;

            return $fromCache;
        }

        $normalized = $this->normalizeBatches($grouped, $minMasaBerlakuBulan);
        self::$requestCache[$cacheKey] = $normalized;

        return $normalized;
    }

    /**
     * Qty WMS per item_code. null jika lokasi kosong atau WMS & cache gagal.
     *
     * @return array<string, int>|null
     */
    public function qtyByItemCode(?Warehouse $warehouse, int $minMasaBerlakuBulan = 0): ?array
    {
        $location = self::locationCode($warehouse);
        if (! $location) {
            return null;
        }

        $batches = $this->batchesByItemCode($location, $minMasaBerlakuBulan);
        if ($batches === null) {
            return null;
        }

        $map = [];
        foreach ($batches as $itemCode => $rows) {
            $qty = 0;
            foreach ($rows as $row) {
                $qty += (int) ($row['qty'] ?? 0);
            }

            $map[$itemCode] = $qty;
            $map[strtoupper(trim((string) $itemCode))] = $qty;
        }

        return $map;
    }

    /**
     * @return list<array{item_code: string, lot_serial: string, qty: int, expired: ?string}>
     */
    public function flatBatches(string $locationCode): array
    {
        $grouped = $this->batchesByItemCode($locationCode) ?? [];
        $flat = [];

        foreach ($grouped as $itemCode => $rows) {
            foreach ($rows as $row) {
                $flat[] = [
                    'item_code' => $itemCode,
                    'lot_serial' => $row['lot_serial'],
                    'qty' => $row['qty'],
                    'expired' => $row['expired'],
                ];
            }
        }

        return $flat;
    }

    /**
     * @param  array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>  $grouped
     * @return list<array{lot_serial: string, qty: int, expired: ?string}>
     */
    public function batchesForProduct(array $grouped, string $productCode): array
    {
        return $grouped[$productCode]
            ?? $grouped[strtoupper(trim($productCode))]
            ?? [];
    }

    /**
     * Batch satu item dari cache lokal (qad_inventories), tanpa tarik semua halaman WMS.
     *
     * @return list<array{lot_serial: string, qty: int, expired: ?string}>
     */
    public function localBatchesForItem(string $locationCode, string $productCode, int $minMasaBerlakuBulan = 0): array
    {
        $codes = array_values(array_unique(array_filter([
            $productCode,
            trim($productCode),
            strtoupper(trim($productCode)),
        ])));

        if ($codes === []) {
            return [];
        }

        $rows = QadInventory::query()
            ->where('qad_location_code', $locationCode)
            ->whereIn('item_code', $codes)
            ->where('qty', '>', 0)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $itemCode = trim((string) $row->item_code);
            $lotSerial = trim((string) ($row->lot_serial ?? ''));
            if ($itemCode === '' || $lotSerial === '' || str_contains($lotSerial, '-')) {
                continue;
            }

            $grouped[$itemCode][] = [
                'lot_serial' => $lotSerial,
                'qty' => (int) $row->qty,
                'expired' => $row->expired_date?->format('Y-m-d'),
            ];
        }

        $normalized = $this->normalizeBatches($grouped, $minMasaBerlakuBulan);

        return $this->batchesForProduct($normalized, $productCode);
    }

    /**
     * @return array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>|null
     */
    private function batchesFromCache(string $locationCode, int $minMasaBerlakuBulan): ?array
    {
        $rows = QadInventory::where('qad_location_code', $locationCode)->get();
        if ($rows->isEmpty()) {
            return null;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $itemCode = trim((string) $row->item_code);
            $lotSerial = trim((string) ($row->lot_serial ?? ''));
            if ($itemCode === '' || $lotSerial === '' || str_contains($lotSerial, '-')) {
                continue;
            }

            $grouped[$itemCode][] = [
                'lot_serial' => $lotSerial,
                'qty' => (int) $row->qty,
                'expired' => $row->expired_date?->format('Y-m-d'),
            ];
        }

        return $this->normalizeBatches($grouped, $minMasaBerlakuBulan);
    }

    /**
     * @param  array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>  $grouped
     * @return array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>
     */
    private function normalizeBatches(array $grouped, int $minMasaBerlakuBulan): array
    {
        $minDate = $minMasaBerlakuBulan > 0 ? Carbon::now()->addMonths($minMasaBerlakuBulan) : null;
        $out = [];

        foreach ($grouped as $itemCode => $batches) {
            $filtered = [];
            foreach ($batches as $batch) {
                if ((int) ($batch['qty'] ?? 0) <= 0) {
                    continue;
                }

                if ($minDate && ! empty($batch['expired'])) {
                    try {
                        if (Carbon::parse($batch['expired'])->lt($minDate)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $filtered[] = $batch;
            }

            usort($filtered, function ($a, $b) {
                return strtotime($a['expired'] ?? '2099-12-31') <=> strtotime($b['expired'] ?? '2099-12-31');
            });

            if ($filtered !== []) {
                $out[$itemCode] = $filtered;
            }
        }

        return $out;
    }
}
