<?php

namespace App\Services\MasterSync;

use App\Models\Product;
use App\Models\Scopes\SyncedInJubelioAndQadScope;
use App\Services\JubelioService;
use App\Support\ProductCodeMatcher;
use Illuminate\Support\Facades\Log;

class JubelioProductPriceSyncService
{
    public function __construct(
        private JubelioService $jubelio
    ) {}

    /**
     * Scan semua variant Jubelio dan samakan harga lokal ke sell_price Jubelio.
     *
     * @return array{
     *     jubelio_variants: int,
     *     matched: int,
     *     updated: int,
     *     already_ok: int,
     *     no_local_product: int,
     *     jubelio_price_zero: int,
     *     local_zero_with_jubelio_price: int,
     *     mismatches: array<int, array{code: string, local: float, jubelio: float}>
     * }
     */
    public function sync(bool $dryRun = false): array
    {
        $token = $this->jubelio->login();
        $groups = $this->jubelio->fetchAllItemGroups($token);

        $stats = [
            'jubelio_variants' => 0,
            'matched' => 0,
            'updated' => 0,
            'already_ok' => 0,
            'no_local_product' => 0,
            'jubelio_price_zero' => 0,
            'local_zero_with_jubelio_price' => 0,
            'mismatches' => [],
        ];

        $jubelioPricesByCode = [];

        foreach ($groups as $group) {
            foreach ($group['variants'] ?? [] as $variant) {
                $stats['jubelio_variants']++;

                $itemCode = ProductCodeMatcher::normalize($variant['item_code'] ?? null);
                if (! $itemCode) {
                    continue;
                }

                $jubelioPrice = (float) ($variant['sell_price'] ?? $group['sell_price'] ?? 0);
                $jubelioPricesByCode[strtoupper($itemCode)] = $jubelioPrice;

                $product = ProductCodeMatcher::findProduct($itemCode);
                if (! $product) {
                    $stats['no_local_product']++;

                    continue;
                }

                $stats['matched']++;
                $localPrice = (float) $product->price;

                if ($jubelioPrice <= 0) {
                    $stats['jubelio_price_zero']++;

                    continue;
                }

                if ($localPrice <= 0) {
                    $stats['local_zero_with_jubelio_price']++;
                }

                if (abs($localPrice - $jubelioPrice) < 0.01) {
                    $stats['already_ok']++;

                    continue;
                }

                if (! $dryRun) {
                    $product->price = $jubelioPrice;
                    $product->markSyncSource('jubelio');
                    $product->save();
                }

                $stats['updated']++;

                if (count($stats['mismatches']) < 100) {
                    $stats['mismatches'][] = [
                        'code' => $itemCode,
                        'local' => $localPrice,
                        'jubelio' => $jubelioPrice,
                    ];
                }
            }
        }

        Log::channel('master_sync')->info('JubelioProductPriceSync finished', [
            'dry_run' => $dryRun,
            'stats' => array_diff_key($stats, ['mismatches' => true]),
            'mismatch_sample' => array_slice($stats['mismatches'], 0, 20),
        ]);

        return $stats;
    }

    /**
     * Produk lokal bertanda Jubelio tapi harga masih 0 (setelah scan).
     *
     * @return array<int, array{code: string|null, name: string, price: float}>
     */
    public function localJubelioProductsStillZeroPrice(): array
    {
        return Product::withoutGlobalScope(SyncedInJubelioAndQadScope::class)
            ->whereJsonContains('sync_sources', 'jubelio')
            ->where('price', '<=', 0)
            ->orderBy('code')
            ->get(['code', 'name', 'price'])
            ->map(fn (Product $p) => [
                'code' => $p->code,
                'name' => $p->name,
                'price' => (float) $p->price,
            ])
            ->all();
    }
}
