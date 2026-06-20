<?php

namespace App\Services\MasterSync;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\QadService;
use App\Support\MasterSync\ProductMatchDebugLogger;
use App\Support\ProductCodeMatcher;
use App\Support\QadIntegration;
use App\Support\QadResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QadProductSyncService
{
    public function __construct(
        private QadService $qad
    ) {}

    /**
     * @param  array<int, string>|null  $jubelioCodes  SKU yang ada di katalog Jubelio (nama tidak ditimpa QAD)
     * @return array{synced: int, updated: int, skipped: int, configured: bool, debug_catalog: array<int, array<string, mixed>>}
     */
    public function sync(?array $jubelioCodes = null): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'configured' => QadIntegration::isConfigured(),
            'debug_catalog' => [],
        ];

        if (! $stats['configured']) {
            Log::channel('master_sync')->info('QadProductSync skipped: QAD not configured');

            return $stats;
        }

        $response = $this->qad->listItem([
            'prodLine' => 'FG',
            'status' => 'active',
        ]);

        $items = QadResponseHelper::list($response);
        $createdBy = $this->defaultCreatedBy();
        $jubelioCodeSet = $jubelioCodes !== null
            ? array_flip(array_map('strtoupper', $jubelioCodes))
            : null;
        $rawSampleLogged = false;

        foreach ($items as $index => $item) {
            if (! $rawSampleLogged && ProductMatchDebugLogger::isEnabled()) {
                ProductMatchDebugLogger::logSampleRaw('qad', [], [
                    'item_keys' => array_keys($item),
                    'item_sample' => $item,
                ]);
                $rawSampleLogged = true;
            }

            $itemCode = ProductCodeMatcher::normalize($item['itemCode'] ?? $item['item_code'] ?? null);
            $itemName = $item['description'] ?? $item['item_name'] ?? null;

            if (ProductMatchDebugLogger::isEnabled()) {
                $stats['debug_catalog'][] = ProductMatchDebugLogger::formatQadEntry($item);
            }

            if (! $itemCode || ! $itemName) {
                Log::channel('master_sync')->warning('QadProductSync: skip item', [
                    'index' => $index,
                    'item' => $item,
                ]);
                $stats['skipped']++;

                continue;
            }

            $brandId = null;
            if (! empty($item['brand'])) {
                $brand = Brand::firstOrCreate(
                    ['name' => $item['brand']],
                    ['is_active' => true]
                );
                $brandId = $brand->id;
            }

            $categoryId = null;
            if (! empty($item['category'])) {
                $category = Category::firstOrCreate(
                    ['name' => $item['category']],
                    ['is_active' => true]
                );
                $categoryId = $category->id;
            }

            $existing = ProductCodeMatcher::findProduct($itemCode);

            $attributes = [
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'unit' => $item['uom'] ?? null,
                'size' => $item['sizing'] ?? null,
                'status' => isset($item['status']) ? strtolower((string) $item['status']) : 'active',
                'weight' => 1000,
            ];

            // Harga dari Jubelio; QAD hanya mengisi harga jika produk belum ada di Jubelio.
            if (! $this->shouldUseJubelioPrice($itemCode, $existing, $jubelioCodeSet)) {
                $attributes['price'] = $item['defaultPrice'] ?? 0;
            }

            // Nama dari Jubelio; QAD hanya mengisi nama jika SKU tidak ada di Jubelio.
            if ($this->shouldUseQadProductName($itemCode, $existing, $jubelioCodeSet)) {
                $attributes['name'] = $itemName;
                $attributes['commercial_name'] = $itemName;
            }

            ProductCodeMatcher::upsert($itemCode, $attributes, $createdBy, 'qad');

            $existing ? $stats['updated']++ : $stats['synced']++;
        }

        Log::channel('master_sync')->info('QadProductSync finished', $stats);

        return $stats;
    }

    /**
     * @param  array<string, int>|null  $jubelioCodeSet
     */
    private function shouldUseQadProductName(string $itemCode, ?Product $existing, ?array $jubelioCodeSet): bool
    {
        if ($jubelioCodeSet !== null && isset($jubelioCodeSet[strtoupper($itemCode)])) {
            return false;
        }

        return ! $existing || ! $existing->hasSyncSource('jubelio');
    }

    /**
     * @param  array<string, int>|null  $jubelioCodeSet
     */
    private function shouldUseJubelioPrice(string $itemCode, ?Product $existing, ?array $jubelioCodeSet): bool
    {
        if ($jubelioCodeSet !== null && isset($jubelioCodeSet[strtoupper($itemCode)])) {
            return true;
        }

        return $existing !== null && $existing->hasSyncSource('jubelio');
    }

    private function defaultCreatedBy(): string
    {
        $userId = Auth::id() ?? User::where('role', 'super_admin')->value('id');

        if (! $userId) {
            throw new \RuntimeException('Tidak ada user admin untuk created_by produk.');
        }

        return (string) $userId;
    }
}
