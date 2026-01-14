<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WarehouseStock;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display stock list for user's warehouse.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $query = WarehouseStock::with('product')
            ->where('warehouse_id', $warehouse->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Low stock filter
        if ($request->filled('filter') && $request->filter === 'low') {
            $query->where('stock', '<=', 10);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('warehouse.stock.index', compact('warehouse', 'stocks'));
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

