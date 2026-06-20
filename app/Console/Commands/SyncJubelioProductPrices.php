<?php

namespace App\Console\Commands;

use App\Services\MasterSync\JubelioProductPriceSyncService;
use Illuminate\Console\Command;

class SyncJubelioProductPrices extends Command
{
    protected $signature = 'products:sync-jubelio-prices
                            {--dry-run : Scan saja, tidak update database}';

    protected $description = 'Scan semua SKU Jubelio dan samakan harga produk lokal ke sell_price Jubelio';

    public function handle(JubelioProductPriceSyncService $sync): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode dry-run — tidak ada perubahan database.');
        }

        $this->info('Mengambil katalog Jubelio dan membandingkan harga lokal...');

        try {
            $stats = $sync->sync($dryRun);
        } catch (\Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Variant Jubelio', $stats['jubelio_variants']],
                ['Cocok dengan produk lokal', $stats['matched']],
                ['Harga sudah sama', $stats['already_ok']],
                [$dryRun ? 'Perlu update (dry-run)' : 'Diupdate dari Jubelio', $stats['updated']],
                ['Tidak ada di DB lokal', $stats['no_local_product']],
                ['Harga Jubelio = 0 (dilewati)', $stats['jubelio_price_zero']],
                ['Lokal 0 tapi Jubelio > 0', $stats['local_zero_with_jubelio_price']],
            ]
        );

        if ($stats['mismatches'] !== []) {
            $this->newLine();
            $this->info('Contoh perbedaan harga (max 20):');
            $this->table(
                ['Kode', 'Harga lokal', 'Harga Jubelio'],
                collect($stats['mismatches'])->take(20)->map(fn ($row) => [
                    $row['code'],
                    number_format($row['local'], 0, ',', '.'),
                    number_format($row['jubelio'], 0, ',', '.'),
                ])->all()
            );
        }

        if (! $dryRun) {
            $stillZero = $sync->localJubelioProductsStillZeroPrice();
            if ($stillZero !== []) {
                $this->newLine();
                $this->warn('Produk bertanda Jubelio yang harga masih 0 (' . count($stillZero) . '):');
                $this->table(
                    ['Kode', 'Nama', 'Harga'],
                    collect($stillZero)->take(30)->map(fn ($row) => [
                        $row['code'] ?? '-',
                        \Illuminate\Support\Str::limit($row['name'], 50),
                        $row['price'],
                    ])->all()
                );
                if (count($stillZero) > 30) {
                    $this->line('... dan ' . (count($stillZero) - 30) . ' lainnya.');
                }
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? 'Dry-run selesai. Jalankan tanpa --dry-run untuk menerapkan harga Jubelio.'
            : 'Sinkronisasi harga Jubelio selesai. Log: storage/logs/master-sync.log');

        return self::SUCCESS;
    }
}
