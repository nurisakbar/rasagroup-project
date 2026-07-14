<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JubelioProductContentSyncService
{
    /** @var array<int, array<string, mixed>|null> */
    private array $groupDetailCache = [];

    public function __construct(
        private JubelioService $jubelio
    ) {}

    /**
     * @return array{
     *     groups: int,
     *     updated: int,
     *     skipped: int,
     *     failed: int,
     *     images_downloaded: int
     * }
     */
    public function sync(?string $productCode = null): array
    {
        $stats = [
            'groups' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'images_downloaded' => 0,
        ];

        $token = $this->jubelio->token();
        $groups = $this->jubelio->fetchAllItemGroups($token);
        $stats['groups'] = count($groups);

        $productsByCode = Product::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->when($productCode, fn ($query) => $query->where('code', $productCode))
            ->get()
            ->keyBy(fn (Product $product) => strtoupper(trim((string) $product->code)));

        if ($productsByCode->isEmpty()) {
            Log::channel('jubelio_product_content')->info('JubelioProductContentSync: no local products to sync');

            return $stats;
        }

        Log::channel('jubelio_product_content')->info('JubelioProductContentSync: start', [
            'jubelio_groups' => count($groups),
            'local_products' => $productsByCode->count(),
            'product_code_filter' => $productCode,
        ]);

        foreach ($groups as $group) {
            $itemGroupId = (int) ($group['item_group_id'] ?? 0);
            if ($itemGroupId <= 0) {
                continue;
            }

            foreach ($group['variants'] ?? [] as $variant) {
                $itemCode = strtoupper(trim((string) ($variant['item_code'] ?? '')));
                if ($itemCode === '' || ! $productsByCode->has($itemCode)) {
                    continue;
                }

                /** @var Product $product */
                $product = $productsByCode->get($itemCode);

                try {
                    $result = $this->syncProductVariant(
                        $token,
                        $product,
                        $group,
                        $variant,
                        $itemGroupId
                    );

                    if ($result['status'] === 'updated') {
                        $stats['updated']++;
                        $stats['images_downloaded'] += $result['images_downloaded'];
                    } else {
                        $stats['skipped']++;
                    }
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    Log::channel('jubelio_product_content')->error('JubelioProductContentSync: product failed', [
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'item_group_id' => $itemGroupId,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::channel('jubelio_product_content')->info('JubelioProductContentSync: finished', $stats);

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $groupSummary
     * @param  array<string, mixed>  $variant
     * @return array{status: 'updated'|'skipped', images_downloaded: int}
     */
    private function syncProductVariant(
        string $token,
        Product $product,
        array $groupSummary,
        array $variant,
        int $itemGroupId
    ): array {
        $groupDetail = $this->getGroupDetail($token, $itemGroupId);
        if (! $groupDetail) {
            throw new \RuntimeException("Detail item group {$itemGroupId} tidak ditemukan");
        }

        $itemId = (int) ($variant['item_id'] ?? 0);
        $sku = $this->findProductSku($groupDetail, $variant, $itemId);
        $description = $this->resolveDescription($groupDetail, $token, $itemId);
        $technicalDescription = $this->normalizeNullableText($groupDetail['notes'] ?? null);
        $imageUrls = $this->collectImageUrls($groupSummary, $variant, $sku);

        $updates = [];

        if ($description !== null) {
            $updates['description'] = $description;
        }
        if ($technicalDescription !== null) {
            $updates['technical_description'] = $technicalDescription;
        }

        // === IMAGE SYNC DISABLED PER USER REQUEST ===
        // $downloadedPaths = $this->downloadImages($product->code, $imageUrls);
        $imagesDownloaded = 0; // count($downloadedPaths);

        // if ($downloadedPaths !== []) {
        //     $this->replaceGalleryImages($product, $downloadedPaths);
        // 
        //     $mainImagePath = $downloadedPaths[0];
        //     if ($mainImagePath !== $product->image) {
        //         $this->deleteStoredFile($product->image);
        //     }
        //     $updates['image'] = $mainImagePath;
        // }

        $hasContent = $updates !== []; // || $downloadedPaths !== [];

        if (! $hasContent) {
            Log::channel('jubelio_product_content')->debug('JubelioProductContentSync: no content from Jubelio', [
                'product_code' => $product->code,
                'item_group_id' => $itemGroupId,
            ]);

            return ['status' => 'skipped', 'images_downloaded' => 0];
        }

        $product->fill($updates);
        $product->markSyncSource('jubelio');
        $product->save();

        Log::channel('jubelio_product_content')->info('JubelioProductContentSync: updated', [
            'product_id' => $product->id,
            'product_code' => $product->code,
            'item_group_id' => $itemGroupId,
            'images' => $imagesDownloaded,
            'fields' => array_keys($updates),
        ]);

        return ['status' => 'updated', 'images_downloaded' => $imagesDownloaded];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getGroupDetail(string $token, int $itemGroupId): ?array
    {
        if (! array_key_exists($itemGroupId, $this->groupDetailCache)) {
            $this->groupDetailCache[$itemGroupId] = $this->jubelio->getItemGroup($token, $itemGroupId);
        }

        return $this->groupDetailCache[$itemGroupId];
    }

    /**
     * @param  array<string, mixed>  $groupDetail
     * @param  array<string, mixed>  $variant
     * @return array<string, mixed>|null
     */
    private function findProductSku(array $groupDetail, array $variant, int $itemId): ?array
    {
        $itemCode = strtoupper(trim((string) ($variant['item_code'] ?? '')));

        foreach ($groupDetail['product_skus'] ?? [] as $sku) {
            if ($itemId > 0 && (int) ($sku['item_id'] ?? 0) === $itemId) {
                return is_array($sku) ? $sku : null;
            }

            $skuCode = strtoupper(trim((string) ($sku['item_code'] ?? '')));
            if ($skuCode !== '' && $skuCode === $itemCode) {
                return is_array($sku) ? $sku : null;
            }
        }

        return null;
    }

    private function resolveDescription(array $groupDetail, string $token, int $itemId): ?string
    {
        $description = $this->normalizeNullableText($groupDetail['description'] ?? null);

        if (($description === null || $description === '') && $itemId > 0) {
            $itemDetail = $this->jubelio->getItem($token, $itemId);
            $description = $this->normalizeNullableText($itemDetail['description'] ?? null);
        }

        return $description;
    }

    /**
     * @param  array<string, mixed>  $groupSummary
     * @param  array<string, mixed>  $variant
     * @param  array<string, mixed>|null  $sku
     * @return array<int, string>
     */
    private function collectImageUrls(array $groupSummary, array $variant, ?array $sku): array
    {
        $urls = [];

        foreach ($sku['images'] ?? [] as $image) {
            if (! is_array($image)) {
                continue;
            }

            $url = trim((string) ($image['cloud_key'] ?? $image['thumbnail'] ?? ''));
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        if ($urls !== []) {
            usort($urls, function (string $a, string $b) use ($sku) {
                $seqA = $this->imageSequence($sku['images'] ?? [], $a);
                $seqB = $this->imageSequence($sku['images'] ?? [], $b);

                return $seqA <=> $seqB;
            });

            return array_values(array_unique($urls));
        }

        foreach ([
            $variant['thumbnail'] ?? null,
            $groupSummary['thumbnail'] ?? null,
        ] as $fallback) {
            $url = trim((string) $fallback);
            if ($url !== '') {
                return [$url];
            }
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $images
     */
    private function imageSequence(array $images, string $url): int
    {
        foreach ($images as $image) {
            $candidate = trim((string) ($image['cloud_key'] ?? $image['thumbnail'] ?? ''));
            if ($candidate === $url) {
                return (int) ($image['sequence_number'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    private function downloadImages(?string $productCode, array $urls): array
    {
        $paths = [];
        $safeCode = Str::slug((string) $productCode) ?: 'product';

        foreach ($urls as $index => $url) {
            $path = $this->downloadImage($safeCode, $url, $index);
            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function downloadImage(string $productCode, string $url, int $index): ?string
    {
        try {
            $response = Http::timeout(60)->get($url);
            if (! $response->successful()) {
                Log::channel('jubelio_product_content')->warning('JubelioProductContentSync: image download failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $extension = $this->extensionFromUrl($url, $response->header('Content-Type'));
            $filename = sprintf('products/jubelio/%s_%02d.%s', $productCode, $index + 1, $extension);

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable $e) {
            Log::channel('jubelio_product_content')->warning('JubelioProductContentSync: image download exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function extensionFromUrl(string $url, ?string $contentType): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        $contentType = strtolower((string) $contentType);
        if (str_contains($contentType, 'png')) {
            return 'png';
        }
        if (str_contains($contentType, 'gif')) {
            return 'gif';
        }
        if (str_contains($contentType, 'webp')) {
            return 'webp';
        }

        return 'jpg';
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function replaceGalleryImages(Product $product, array $paths): void
    {
        if (! config('jubelio.product_content.replace_gallery', true)) {
            return;
        }

        $galleryPaths = array_slice($paths, 1);

        foreach ($product->images as $image) {
            $this->deleteStoredFile($image->image_path);
            $image->delete();
        }

        foreach ($galleryPaths as $sortOrder => $path) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'sort_order' => $sortOrder + 1,
            ]);
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! $path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
