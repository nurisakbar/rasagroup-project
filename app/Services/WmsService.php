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
                $itemCode = $row['location']['item']['code'] ?? null;
                $batches = $row['location']['item']['batches'] ?? [];
                if (! is_string($itemCode) || $itemCode === '' || ! is_array($batches)) {
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
