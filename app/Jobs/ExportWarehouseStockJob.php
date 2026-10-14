<?php

namespace App\Jobs;

use App\Exports\WarehouseStockExport;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\JubelioService;
use App\Services\WmsService;
use App\Support\WarehouseStockExportProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportWarehouseStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        private string $progressId,
        private string $warehouseId,
        private array $filters = []
    ) {}

    public function handle(JubelioService $jubelio, WmsService $wms): void
    {
        $progress = WarehouseStockExportProgress::find($this->progressId);
        if (! $progress) {
            Log::warning('ExportWarehouseStockJob: progress record not found', ['id' => $this->progressId]);
            return;
        }

        $warehouse = Warehouse::find($this->warehouseId);
        if (! $warehouse) {
            $progress->fail('Gudang tidak ditemukan.');
            return;
        }

        try {
            // 1. Tarik data realtime dari Jubelio jika ada kode_hub (15% - 40%)
            if (!empty($warehouse->kode_hub)) {
                $progress->update(15, 'Menarik data stok realtime dari Jubelio (' . $warehouse->kode_hub . ')...');
                try {
                    $token = $jubelio->login();
                    $locationId = $jubelio->findLocationIdByCode($token, $warehouse->kode_hub);
                    if ($locationId) {
                        $items = $jubelio->fetchItemsToSell($token, $locationId);
                        $productsByCode = Product::query()
                            ->whereNotNull('code')
                            ->where('code', '!=', '')
                            ->pluck('id', 'code');

                        DB::transaction(function () use ($items, $productsByCode, $warehouse) {
                            foreach ($items as $item) {
                                $itemCode = $item['item_code'] ?? null;
                                if (! $itemCode || ! isset($productsByCode[$itemCode])) {
                                    continue;
                                }
                                $productId = $productsByCode[$itemCode];
                                $availableQty = max(0, (int) ($item['available_qty'] ?? 0));

                                WarehouseStock::updateOrCreate(
                                    [
                                        'warehouse_id' => $warehouse->id,
                                        'product_id' => $productId,
                                    ],
                                    [
                                        'stock' => $availableQty,
                                    ]
                                );
                            }
                        });
                        $progress->update(40, 'Stok Jubelio berhasil diperbarui.');
                    } else {
                        $progress->update(40, 'Lokasi Jubelio tidak ditemukan, melanjutkan...');
                    }
                } catch (\Throwable $e) {
                    Log::warning('ExportWarehouseStockJob: Jubelio sync warning', [
                        'warehouse' => $warehouse->kode_hub,
                        'error' => $e->getMessage(),
                    ]);
                    $progress->update(40, 'Sinkronisasi Jubelio dilewati: ' . $e->getMessage());
                }
            } else {
                $progress->update(40, 'Melewati sinkronisasi Jubelio...');
            }

            // 2. Tarik data realtime dari QAD / WMS jika ada qadLocationCode (45% - 65%)
            $qadLocationCode = WmsService::locationCode($warehouse);
            if (!empty($qadLocationCode)) {
                $progress->update(50, 'Menarik batch stok realtime dari QAD/WMS (' . $qadLocationCode . ')...');
                try {
                    $wms->syncLocationBatches($qadLocationCode);
                    $progress->update(65, 'Batch stok QAD berhasil diperbarui.');
                } catch (\Throwable $e) {
                    Log::warning('ExportWarehouseStockJob: QAD sync warning', [
                        'location' => $qadLocationCode,
                        'error' => $e->getMessage(),
                    ]);
                    $progress->update(65, 'Sinkronisasi QAD dilewati: ' . $e->getMessage());
                }
            } else {
                $progress->update(65, 'Melewati sinkronisasi QAD...');
            }

            // 3. Ambil data stok yang sudah ter-update (70% - 80%)
            $progress->update(75, 'Menyiapkan data produk dan batch...');
            $query = WarehouseStock::with(['product'])
                ->whereHas('product')
                ->where('warehouse_id', $warehouse->id);

            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            if (!empty($this->filters['filter']) && $this->filters['filter'] === 'low') {
                $query->where('stock', '<=', 10);
            }

            $stocks = $query->orderBy('updated_at', 'desc')->get();

            $wmsBatches = [];
            if ($qadLocationCode) {
                $wmsBatches = $wms->storedBatchesByItemCode($qadLocationCode);
            }

            // 4. Generate & simpan file Excel (85% - 95%)
            $progress->update(85, 'Menyusun dan menyimpan file Excel...');
            Storage::disk('local')->makeDirectory('exports');

            $hubIdentifier = $warehouse->kode_hub ?: Str::slug($warehouse->name);
            $filename = 'stok-hub-' . $hubIdentifier . '-' . now()->timezone('Asia/Jakarta')->format('Ymd-His') . '.xlsx';
            $relativePath = 'exports/' . $filename;

            Excel::store(new WarehouseStockExport($warehouse, $stocks, $wmsBatches), $relativePath, 'local');

            $downloadUrl = route('warehouse.stock.export-download', ['filename' => $filename]);

            // 5. Selesai (100%)
            $progress->complete($downloadUrl, $filename, 'File Excel berhasil dibuat! Memulai unduhan...');
        } catch (\Throwable $e) {
            Log::error('ExportWarehouseStockJob error', [
                'warehouse_id' => $this->warehouseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $progress->fail('Gagal membuat file export: ' . $e->getMessage());
        }
    }
}
