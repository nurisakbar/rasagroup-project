<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\QadInventory;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\WmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        return view('warehouse.stock.index', compact(
            'warehouse',
            'stocks',
            'usesQadStock',
            'qadLocationCode'
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
}

