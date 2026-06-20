<?php

namespace App\Services\MasterSync;

use App\Services\JubelioProductContentSyncService;
use App\Services\JubelioStockSyncService;
use App\Support\MasterSync\ProductMatchDebugLogger;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class QadJubelioMasterSyncService
{
    public function __construct(
        private JubelioHubSyncService $jubelioHubs,
        private QadHubSyncService $qadHubs,
        private JubelioProductSyncService $jubelioProducts,
        private QadProductSyncService $qadProducts,
        private JubelioProductContentSyncService $jubelioProductContent,
        private JubelioStockSyncService $jubelioStock
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function sync(?\App\Support\MasterSyncProgress $progress = null): array
    {
        $this->assertJubelioConfigured();

        Log::channel('master_sync')->info('QadJubelioMasterSync: start');

        $progress?->update(5, 'Sinkronisasi hub Jubelio...');
        $hubJubelio = $this->jubelioHubs->sync();

        $progress?->update(15, 'Sinkronisasi hub QAD...');
        $hubQad = $this->qadHubs->sync(withStock: false);

        $progress?->update(30, 'Sinkronisasi produk Jubelio...');
        $productJubelio = $this->jubelioProducts->sync();

        $progress?->update(45, 'Sinkronisasi produk QAD...');
        $productQad = $this->qadProducts->sync($productJubelio['codes'] ?? null);

        if (ProductMatchDebugLogger::isEnabled()) {
            ProductMatchDebugLogger::compare(
                $productJubelio['debug_catalog'] ?? [],
                $productQad['debug_catalog'] ?? []
            );
        }

        $progress?->update(60, 'Sinkronisasi deskripsi & foto produk Jubelio...');
        $productContent = $this->syncProductContent();

        $progress?->update(75, 'Sinkronisasi stok QAD...');
        $stockQad = $this->qadHubs->syncAllStocks();

        $progress?->update(90, 'Sinkronisasi stok Jubelio...');
        $stockJubelio = $this->jubelioStock->sync();

        $stats = [
            'hubs' => [
                'jubelio' => $hubJubelio,
                'qad' => $hubQad,
            ],
            'products' => [
                'jubelio' => $productJubelio,
                'qad' => $productQad,
            ],
            'product_content' => $productContent,
            'stock' => [
                'qad' => $stockQad,
                'jubelio' => $stockJubelio,
            ],
        ];

        Log::channel('master_sync')->info('QadJubelioMasterSync: finished', $stats);

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    public function formatSummary(array $stats): string
    {
        $hubJubelio = (int) ($stats['hubs']['jubelio']['synced'] ?? 0);
        $hubQad = (int) ($stats['hubs']['qad']['synced'] ?? 0);
        $qadStockRows = (int) ($stats['stock']['qad']['stock_rows'] ?? 0);

        $productJubelio = (int) ($stats['products']['jubelio']['synced'] ?? 0);
        $productJubelioUpdated = (int) ($stats['products']['jubelio']['updated'] ?? 0);
        $productQadNew = (int) ($stats['products']['qad']['synced'] ?? 0);
        $productQadUpdated = (int) ($stats['products']['qad']['updated'] ?? 0);

        $jubelioStockRows = (int) ($stats['stock']['jubelio']['stock_rows'] ?? 0);
        $jubelioStockProducts = (int) ($stats['stock']['jubelio']['products'] ?? 0);

        $contentUpdated = (int) ($stats['product_content']['updated'] ?? 0);
        $contentImages = (int) ($stats['product_content']['images_downloaded'] ?? 0);
        $contentEnabled = (bool) ($stats['product_content']['enabled'] ?? false);

        $parts = [
            "Hub Jubelio: {$hubJubelio}",
            'Hub QAD: ' . ($stats['hubs']['qad']['configured'] ?? false ? (string) $hubQad : 'dilewati (QAD belum dikonfigurasi)'),
            "Produk Jubelio: {$productJubelio} baru, {$productJubelioUpdated} diperbarui",
            'Produk QAD: ' . ($stats['products']['qad']['configured'] ?? false ? "{$productQadNew} baru, {$productQadUpdated} diperbarui" : 'dilewati (QAD belum dikonfigurasi)'),
            'Konten Jubelio: ' . ($contentEnabled ? "{$contentUpdated} produk diperbarui, {$contentImages} gambar" : 'dilewati (fitur nonaktif)'),
            'Stok QAD: ' . ($stats['stock']['qad']['configured'] ?? false ? "{$qadStockRows} baris" : 'dilewati'),
            "Stok Jubelio: {$jubelioStockRows} baris ({$jubelioStockProducts} produk)",
        ];

        return 'Sinkronisasi gabungan QAD + Jubelio selesai. ' . implode(' · ', $parts);
    }

    private function assertJubelioConfigured(): void
    {
        $email = config('jubelio.email') ?: env('JUBELIO_EMAIL');
        $password = config('jubelio.password') ?: env('JUBELIO_PASSWORD');

        if (! $email || ! $password) {
            throw new RuntimeException('Kredensial Jubelio tidak ditemukan di file .env');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function syncProductContent(): array
    {
        if (! config('jubelio.product_content.enabled', true)) {
            return [
                'enabled' => false,
                'updated' => 0,
                'images_downloaded' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];
        }

        $stats = $this->jubelioProductContent->sync();
        $stats['enabled'] = true;

        return $stats;
    }
}
