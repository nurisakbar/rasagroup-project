<?php

namespace App\Http\Controllers\Driippreneur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Display the stock list.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return view('driippreneur.stock.index', [
                'warehouse' => null,
                'stocks' => collect(),
            ]);
        }

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
        if ($request->filter === 'low') {
            $query->where('stock', '<=', 10);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('driippreneur.stock.index', compact('warehouse', 'stocks'));
    }

    /**
     * Update stock.
     */
    public function updateStock(Request $request, WarehouseStock $stock)
    {
        $user = Auth::user();
        
        // Ensure user can only update their own warehouse stock
        if ($stock->warehouse_id !== $user->warehouse_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $stock->update(['stock' => $validated['stock']]);

        return back()->with('success', 'Stock berhasil diupdate.');
    }

    /**
     * Sync all products to warehouse.
     */
    public function syncProducts()
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return back()->with('error', 'Anda belum terdaftar di hub manapun.');
        }

        // Get all active products not in this warehouse
        $existingProductIds = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->pluck('product_id')
            ->toArray();

        $newProducts = Product::where('status', 'active')
            ->whereNotIn('id', $existingProductIds)
            ->get();

        $count = 0;
        foreach ($newProducts as $product) {
            WarehouseStock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'stock' => 0,
            ]);
            $count++;
        }

        return back()->with('success', "{$count} produk berhasil disinkronkan ke hub Anda.");
    }
}

