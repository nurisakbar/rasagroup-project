<?php

namespace App\Services\MasterSync;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Services\JubelioService;
use App\Support\MasterSync\ProductMatchDebugLogger;
use App\Support\ProductCodeMatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JubelioProductSyncService
{
    public function __construct(
        private JubelioService $jubelio
    ) {}

    /**
     * @return array{synced: int, updated: int, skipped: int, codes: array<int, string>, debug_catalog: array<int, array<string, mixed>>}
     */
    public function sync(): array
    {
        $token = $this->jubelio->login();
        $groups = $this->jubelio->fetchAllItemGroups($token);
        $categoryMap = $this->fetchCategoryMap($token);
        $createdBy = $this->defaultCreatedBy();

        $stats = ['synced' => 0, 'updated' => 0, 'skipped' => 0, 'codes' => [], 'debug_catalog' => []];
        $rawSampleLogged = false;

        foreach ($groups as $groupIndex => $group) {
            $categoryId = null;
            $itemCatId = $group['item_category_id'] ?? null;
            if ($itemCatId && isset($categoryMap[$itemCatId])) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryMap[$itemCatId]],
                    ['is_active' => true]
                );
                $categoryId = $category->id;
            }

            foreach ($group['variants'] ?? [] as $variantIndex => $variant) {
                if (! $rawSampleLogged && ProductMatchDebugLogger::isEnabled()) {
                    ProductMatchDebugLogger::logSampleRaw('jubelio', [], [
                        'group_keys' => array_keys($group),
                        'variant_keys' => array_keys($variant),
                        'group_sample' => $group,
                        'variant_sample' => $variant,
                    ]);
                    $rawSampleLogged = true;
                }

                $itemCode = ProductCodeMatcher::normalize($variant['item_code'] ?? null);
                $itemName = $variant['item_name'] ?? $group['item_name'] ?? null;

                if ($itemCode) {
                    $stats['codes'][] = strtoupper($itemCode);
                }

                if (ProductMatchDebugLogger::isEnabled()) {
                    $stats['debug_catalog'][] = ProductMatchDebugLogger::formatJubelioEntry($group, $variant);
                }

                if (! $itemCode || ! $itemName) {
                    Log::channel('master_sync')->warning('JubelioProductSync: skip variant', [
                        'group' => $groupIndex,
                        'variant' => $variantIndex,
                    ]);
                    $stats['skipped']++;

                    continue;
                }

                $brandId = $this->resolveBrandId($token, $variant['item_id'] ?? null);
                $price = $variant['sell_price'] ?? $group['sell_price'] ?? 0;
                $exists = ProductCodeMatcher::findProduct($itemCode) !== null;

                ProductCodeMatcher::upsert($itemCode, [
                    'name' => $itemName,
                    'commercial_name' => $itemName,
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'price' => $price,
                    'status' => 'active',
                    'weight' => 1000,
                ], $createdBy, 'jubelio');

                $exists ? $stats['updated']++ : $stats['synced']++;
            }
        }

        $stats['codes'] = array_values(array_unique($stats['codes']));

        Log::channel('master_sync')->info('JubelioProductSync finished', $stats);

        return $stats;
    }

    /**
     * @return array<int, string>
     */
    private function fetchCategoryMap(string $token): array
    {
        $baseUrl = rtrim((string) config('jubelio.base_url', 'https://api2.jubelio.com'), '/');
        try {
            $catResponse = Http::withToken($token)
                ->retry(3, 1000)
                ->timeout(30)
                ->get("{$baseUrl}/inventory/categories/item-categories/");

            $map = [];
            if ($catResponse->successful()) {
                foreach ($catResponse->json() as $cat) {
                    if (isset($cat['category_id'], $cat['category_name'])) {
                        $map[$cat['category_id']] = $cat['category_name'];
                    }
                }
            }
            return $map;
        } catch (\Exception $e) {
            Log::warning('JubelioProductSyncService.fetchCategoryMap connection error', ['message' => $e->getMessage()]);
            return [];
        }

        return $map;
    }

    private function resolveBrandId(string $token, mixed $itemId): ?string
    {
        if (! $itemId) {
            return null;
        }

        try {
            $detailResponse = Http::withToken($token)
                ->retry(3, 1000)
                ->timeout(30)
                ->get("{$baseUrl}/inventory/items/{$itemId}");

            if (! $detailResponse->successful()) {
                return null;
            }

            $detailData = $detailResponse->json();
        } catch (\Exception $e) {
            Log::warning('JubelioProductSyncService.resolveBrandId connection error', [
                'item_id' => $itemId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
        $brandName = $detailData['selected_brand_name'] ?? $detailData['brand_name'] ?? null;

        if (! $brandName) {
            return null;
        }

        $brand = Brand::firstOrCreate(
            ['name' => $brandName],
            ['is_active' => true]
        );

        return $brand->id;
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
