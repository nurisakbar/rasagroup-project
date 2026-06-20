<?php

namespace App\Jobs;

use App\Services\JubelioStockSyncService;
use App\Services\MasterSync\JubelioHubSyncService;
use App\Services\MasterSync\JubelioProductSyncService;
use App\Services\MasterSync\QadJubelioMasterSyncService;
use App\Support\MasterSyncProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RunMasterSyncJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        private string $runId,
        private string $type
    ) {}

    public function uniqueId(): string
    {
        return 'master-sync:' . $this->type;
    }

    public function handle(
        QadJubelioMasterSyncService $qadJubelio,
        JubelioHubSyncService $jubelioHubs,
        JubelioProductSyncService $jubelioProducts,
        JubelioStockSyncService $jubelioStock
    ): void {
        $progress = MasterSyncProgress::find($this->runId);

        if (! $progress) {
            Log::channel('master_sync')->warning('RunMasterSyncJob: progress not found', [
                'run_id' => $this->runId,
                'type' => $this->type,
            ]);

            return;
        }

        $progress->markRunning();

        try {
            match ($this->type) {
                'qad_jubelio' => $this->runQadJubelio($qadJubelio, $progress),
                'jubelio_hubs' => $this->runJubelioHubs($jubelioHubs, $progress),
                'jubelio_stock' => $this->runJubelioStock($jubelioStock, $progress),
                'jubelio_products' => $this->runJubelioProducts($jubelioProducts, $progress),
                default => throw new InvalidArgumentException('Tipe sinkronisasi tidak dikenal: ' . $this->type),
            };
        } catch (\Throwable $e) {
            $progress->fail($e->getMessage());

            Log::channel('master_sync')->error('RunMasterSyncJob failed', [
                'run_id' => $this->runId,
                'type' => $this->type,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function runQadJubelio(QadJubelioMasterSyncService $service, MasterSyncProgress $progress): void
    {
        $stats = $service->sync($progress);
        $progress->complete($service->formatSummary($stats));
    }

    private function runJubelioHubs(JubelioHubSyncService $service, MasterSyncProgress $progress): void
    {
        $progress->update(10, 'Login ke Jubelio...');
        $stats = $service->sync();
        $synced = (int) ($stats['synced'] ?? 0);
        $skipped = (int) ($stats['skipped'] ?? 0);

        $progress->complete("Berhasil mensinkronisasi {$synced} hub dari Jubelio" . ($skipped > 0 ? " ({$skipped} dilewati)" : '') . '.');
    }

    private function runJubelioStock(JubelioStockSyncService $service, MasterSyncProgress $progress): void
    {
        $progress->update(10, 'Mengambil data stok dari Jubelio...');
        $stats = $service->sync();

        $progress->complete(
            "Berhasil mensinkronisasi stok Jubelio: {$stats['stock_rows']} baris stok, {$stats['products']} produk. "
            . "({$stats['skipped_products']} produk & {$stats['skipped_locations']} lokasi dilewati — belum ada di website/hub lokal)"
        );
    }

    private function runJubelioProducts(JubelioProductSyncService $service, MasterSyncProgress $progress): void
    {
        $progress->update(10, 'Mengambil data produk dari Jubelio...');
        $stats = $service->sync();
        $synced = (int) ($stats['synced'] ?? 0);
        $skipped = (int) ($stats['skipped'] ?? 0);

        $progress->complete("Berhasil mensinkronisasi {$synced} produk dari Jubelio" . ($skipped > 0 ? " ({$skipped} dilewati)" : '') . '.');
    }
}
