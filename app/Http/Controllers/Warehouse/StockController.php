<?php

namespace App\Http\Controllers\Warehouse;

use App\Exports\WarehouseStockExport;
use App\Http\Controllers\Controller;
use App\Jobs\ExportWarehouseStockJob;
use App\Models\Product;
use App\Models\QadInventory;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\JubelioService;
use App\Services\WmsService;
use App\Support\WarehouseStockExportProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    /**
     * Display stock list for user's warehouse.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;
        $qadLocationCode = WmsService::locationCode($warehouse);
        $usesQadStock = filled($qadLocationCode);

        // Tarik data realtime dari Jubelio dan QAD saat halaman diakses (dengan cooldown 60 detik)
        $this->autoSyncRealtimeData($warehouse, $qadLocationCode);

        $query = WarehouseStock::with(['product.images'])
            ->whereHas('product')
            ->where('warehouse_id', $warehouse->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter') && $request->filter === 'low') {
            $query->where('stock', '<=', 10);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(15);
        $qadBatches = $usesQadStock
            ? $this->qadBatchesIndexed($warehouse, (string) $qadLocationCode)
            : [];

        $stocks->getCollection()->transform(function (WarehouseStock $stock) use ($qadBatches) {
            $code = trim((string) ($stock->product?->code ?? ''));
            $batches = $qadBatches[$code]
                ?? $qadBatches[strtoupper($code)]
                ?? [];
            $stock->setAttribute('qad_batches', $batches);
            $stock->setAttribute('qad_qty', (int) array_sum(array_map(
                fn ($row) => (int) ($row['qty'] ?? 0),
                $batches
            )));

            return $stock;
        });

        $lastSyncAt = $usesQadStock
            ? QadInventory::query()->where('qad_location_code', $qadLocationCode)->max('last_sync_at')
            : null;

        return view('warehouse.stock.index', compact(
            'warehouse',
            'stocks',
            'usesQadStock',
            'qadLocationCode',
            'lastSyncAt'
        ));
    }

    /**
     * @return array<string, list<array{lot_serial: string, qty: int, expired: ?string}>>
     */
    private function qadBatchesIndexed(Warehouse $warehouse, string $locationCode): array
    {
        $grouped = [];

        try {
            $grouped = app(WmsService::class)->batchesByItemCode($locationCode) ?? [];
        } catch (\Throwable $e) {
            Log::warning('Warehouse stock: gagal ambil batch WMS/QAD', [
                'warehouse_id' => $warehouse->id,
                'location' => $locationCode,
                'message' => $e->getMessage(),
            ]);
        }

        if ($grouped === []) {
            $rows = QadInventory::query()
                ->where('qad_location_code', $locationCode)
                ->where('qty', '>', 0)
                ->get();

            foreach ($rows as $row) {
                $itemCode = trim((string) $row->item_code);
                $lot = trim((string) ($row->lot_serial ?? ''));
                if ($itemCode === '' || $lot === '' || str_contains($lot, '-')) {
                    continue;
                }

                $grouped[$itemCode][] = [
                    'lot_serial' => $lot,
                    'qty' => (int) $row->qty,
                    'expired' => $row->expired_date?->format('Y-m-d'),
                ];
            }
        }

        $indexed = [];
        foreach ($grouped as $code => $batches) {
            $indexed[(string) $code] = $batches;
            $indexed[strtoupper(trim((string) $code))] = $batches;
        }

        return $indexed;
    }

    /**
     * Update stock for a product.
     */
    public function update(Request $request, WarehouseStock $stock)
    {
        $user = auth()->user();

        // Verify the stock belongs to user's warehouse
        if ($stock->warehouse_id !== $user->warehouse_id) {
            abort(403, 'Akses ditolak.');
        }

        if (filled(WmsService::locationCode($user->warehouse))) {
            return back()->with('error', 'Stok hub QAD tidak diubah manual. Gunakan stok dari QAD/WMS.');
        }

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $stock->update(['stock' => $validated['stock']]);

        return back()->with('success', 'Stock berhasil diperbarui.');
    }

    /**
     * Sync all products to warehouse.
     */
    public function sync()
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        // Get all active products
        $products = Product::where('status', 'active')->get();
        
        // Get existing product IDs in this warehouse
        $existingProductIds = $warehouse->stocks()->pluck('product_id')->toArray();
        
        $addedCount = 0;
        
        foreach ($products as $product) {
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }
            
            WarehouseStock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'stock' => 0,
            ]);
            
            $addedCount++;
        }
        
        if ($addedCount > 0) {
            return back()->with('success', "Berhasil menambahkan {$addedCount} produk dengan stock 0.");
        }
        
        return back()->with('info', 'Semua produk sudah tersinkron.');
    }

    /**
     * Download / Export stock and batch info to Excel.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $qadLocationCode = WmsService::locationCode($warehouse);
        if ($qadLocationCode) {
            try {
                app(WmsService::class)->syncLocationBatches($qadLocationCode);
            } catch (\Throwable $e) {
                Log::warning('Warehouse stock export: failed realtime sync', [
                    'location' => $qadLocationCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $query = WarehouseStock::with(['product'])
            ->whereHas('product')
            ->where('warehouse_id', $warehouse->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter') && $request->filter === 'low') {
            $query->where('stock', '<=', 10);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->get();

        $wmsBatches = [];
        if ($qadLocationCode) {
            $wmsBatches = app(WmsService::class)->storedBatchesByItemCode($qadLocationCode);
        }

        $hubIdentifier = $warehouse->kode_hub ?: Str::slug($warehouse->name);
        $filename = 'stok-hub-' . $hubIdentifier . '-' . now()->timezone('Asia/Jakarta')->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new WarehouseStockExport($warehouse, $stocks, $wmsBatches),
            $filename
        );
    }

    /**
     * Manually sync stock and batches from QAD/WMS.
     */
    public function syncQad()
    {
        set_time_limit(180);
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $locationCode = WmsService::locationCode($warehouse);
        if (! $locationCode) {
            return back()->with('error', 'Lokasi QAD/WMS pada hub Anda belum dikonfigurasi.');
        }

        $wms = app(WmsService::class);
        if (! $wms->isConfigured()) {
            return back()->with('error', 'API QAD/WMS belum dikonfigurasi.');
        }

        try {
            $synced = $wms->syncLocationBatches($locationCode);
            return back()->with('success', "Berhasil mensinkronisasi {$synced} batch stok dari QAD.");
        } catch (\Throwable $e) {
            Log::error('Warehouse stock: gagal sync QAD/WMS', [
                'warehouse_id' => $warehouse->id,
                'location' => $locationCode,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal melakukan sinkronisasi dengan QAD: ' . $e->getMessage());
        }
    }

    /**
     * Manually sync product stocks from Jubelio API for current hub.
     */
    public function syncJubelio(JubelioService $jubelio)
    {
        set_time_limit(180);
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if (! $warehouse || empty($warehouse->kode_hub)) {
            return back()->with('error', 'Kode Hub belum dikonfigurasi pada hub Anda.');
        }

        try {
            $updatedCount = $this->performJubelioSync($warehouse);
            Cache::put('warehouse_realtime_jubelio_' . $warehouse->id, true, 60);

            return back()->with('success', "Berhasil mensinkronisasi {$updatedCount} stok produk dari Jubelio.");
        } catch (\Throwable $e) {
            Log::error('Warehouse stock: gagal sync Jubelio', [
                'warehouse_id' => $warehouse->id,
                'kode_hub' => $warehouse->kode_hub,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal melakukan sinkronisasi dengan Jubelio: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch background job for exporting stock with realtime sync & progress tracking.
     */
    public function dispatchExportJob(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;
        if (! $warehouse) {
            return response()->json(['message' => 'Hub/Warehouse tidak ditemukan.'], 403);
        }

        $filters = $request->only(['search', 'filter']);
        $progress = WarehouseStockExportProgress::create($warehouse->id, (string) $user->id, $filters);

        ExportWarehouseStockJob::dispatch($progress->id(), $warehouse->id, $filters);

        return response()->json([
            'progress_id' => $progress->id(),
        ]);
    }

    /**
     * Check progress of warehouse stock export job.
     */
    public function exportProgress(string $progressId)
    {
        $progress = WarehouseStockExportProgress::find($progressId);
        if (! $progress) {
            return response()->json(['message' => 'Status export tidak ditemukan.'], 404);
        }

        return response()->json($progress->toArray());
    }

    /**
     * Download completed exported Excel file.
     */
    public function downloadExport(Request $request)
    {
        $filename = basename((string) $request->query('filename'));
        if (! Str::startsWith($filename, 'stok-hub-') || ! Str::endsWith($filename, '.xlsx')) {
            abort(400, 'Nama file export tidak valid.');
        }

        $disk = Storage::disk('local');
        $relativePath = 'exports/' . $filename;

        if ($disk->exists($relativePath)) {
            return $disk->download($relativePath, $filename);
        }

        $fallbackPath = storage_path('app/exports/' . $filename);
        if (file_exists($fallbackPath)) {
            return response()->download($fallbackPath, $filename);
        }

        $privatePath = storage_path('app/private/exports/' . $filename);
        if (file_exists($privatePath)) {
            return response()->download($privatePath, $filename);
        }

        abort(404, 'File export tidak ditemukan atau telah kedaluwarsa.');
    }

    /**
     * Automatically sync realtime stock data when viewing the stock list.
     */
    protected function autoSyncRealtimeData($warehouse, $qadLocationCode): void
    {
        if (! $warehouse) {
            return;
        }

        // Realtime Jubelio sync with 60s cooldown per warehouse
        if (! empty($warehouse->kode_hub)) {
            $cacheKeyJubelio = 'warehouse_realtime_jubelio_' . $warehouse->id;
            if (! Cache::has($cacheKeyJubelio)) {
                try {
                    $this->performJubelioSync($warehouse);
                    Cache::put($cacheKeyJubelio, true, 60);
                } catch (\Throwable $e) {
                    Log::warning('StockController: autoSyncRealtimeData Jubelio warning', [
                        'warehouse_id' => $warehouse->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Realtime QAD sync with 60s cooldown per warehouse
        if (! empty($qadLocationCode)) {
            $cacheKeyQad = 'warehouse_realtime_qad_' . $warehouse->id;
            if (! Cache::has($cacheKeyQad)) {
                try {
                    app(WmsService::class)->syncLocationBatches($qadLocationCode);
                    Cache::put($cacheKeyQad, true, 60);
                } catch (\Throwable $e) {
                    Log::warning('StockController: autoSyncRealtimeData QAD warning', [
                        'location' => $qadLocationCode,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Helper to perform Jubelio stock sync for a warehouse.
     */
    protected function performJubelioSync(Warehouse $warehouse): int
    {
        if (empty($warehouse->kode_hub)) {
            return 0;
        }

        $jubelio = app(JubelioService::class);
        $token = $jubelio->login();
        $locationId = $jubelio->findLocationIdByCode($token, $warehouse->kode_hub);
        if (! $locationId) {
            return 0;
        }

        $items = $jubelio->fetchItemsToSell($token, $locationId);
        $productsByCode = Product::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->pluck('id', 'code');

        $updatedCount = 0;
        DB::transaction(function () use ($items, $productsByCode, $warehouse, &$updatedCount) {
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
                $updatedCount++;
            }
        });

        return $updatedCount;
    }
}

