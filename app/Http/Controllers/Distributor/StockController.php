<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    /**
     * Display the stock list.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if ($request->ajax()) {
            $query = WarehouseStock::with(['product.brand', 'product.category'])
                ->where('warehouse_id', $warehouse->id)
                ->whereHas('product'); // Only show stocks with valid products

            // Filter by stock level
            if ($request->filled('filter')) {
                switch ($request->filter) {
                    case 'low':
                        $query->where('stock', '<=', 10);
                        break;
                    case 'medium':
                        $query->where('stock', '>', 10)->where('stock', '<=', 50);
                        break;
                    case 'high':
                        $query->where('stock', '>', 50);
                        break;
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('product_image', function ($stock) {
                    if ($stock->product && $stock->product->image) {
                        return '<img src="' . asset('storage/' . $stock->product->image) . '" alt="' . ($stock->product->name ?? '') . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">';
                    }
                    return '<div style="width: 50px; height: 50px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-image text-muted"></i></div>';
                })
                ->addColumn('product_name', function ($stock) {
                    if (!$stock->product) {
                        return '<span class="text-muted">Produk tidak ditemukan</span>';
                    }
                    $html = '<strong>' . $stock->product->name . '</strong>';
                    if ($stock->product->code) {
                        $html .= '<br><small class="text-muted">Kode: ' . $stock->product->code . '</small>';
                    }
                    if ($stock->product->status !== 'active') {
                        $html .= '<br><span class="label label-warning">Produk Nonaktif</span>';
                    }
                    return $html;
                })
                ->addColumn('product_brand', function ($stock) {
                    if ($stock->product && $stock->product->brand) {
                        return '<span class="label label-primary">' . $stock->product->brand->name . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('product_price', function ($stock) {
                    if (!$stock->product) {
                        return '-';
                    }
                    return 'Rp ' . number_format($stock->product->price, 0, ',', '.');
                })
                ->addColumn('stock_badge', function ($stock) {
                    if ($stock->stock <= 10) {
                        return '<span class="badge bg-red" style="font-size: 14px;">' . number_format($stock->stock) . '</span><br><small class="text-red"><i class="fa fa-warning"></i> Stock rendah!</small>';
                    } elseif ($stock->stock <= 50) {
                        return '<span class="badge bg-yellow" style="font-size: 14px;">' . number_format($stock->stock) . '</span>';
                    } else {
                        return '<span class="badge bg-green" style="font-size: 14px;">' . number_format($stock->stock) . '</span>';
                    }
                })
                ->addColumn('updated_at_formatted', function ($stock) {
                    return $stock->updated_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($stock) {
                    $updateBtn = '<button type="button" class="btn btn-primary btn-xs btn-update-stock" 
                        data-stock-id="' . $stock->id . '"
                        data-product-name="' . ($stock->product ? htmlspecialchars($stock->product->name) : '') . '"
                        data-product-price="' . ($stock->product ? number_format($stock->product->price, 0, ',', '.') : '0') . '"
                        data-current-stock="' . $stock->stock . '"
                        data-product-image="' . ($stock->product && $stock->product->image ? asset('storage/' . $stock->product->image) : '') . '"
                        data-toggle="modal" 
                        data-target="#updateStockModal">
                        <i class="fa fa-edit"></i> Update
                    </button>';
                    
                    $historyBtn = '<a href="' . route('distributor.stock.history', $stock) . '" class="btn btn-info btn-xs" title="Riwayat Stock">
                        <i class="fa fa-history"></i> History
                    </a>';
                    
                    return $updateBtn . ' ' . $historyBtn;
                })
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->whereHas('product', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['product_image', 'product_name', 'product_brand', 'stock_badge', 'action'])
                ->make(true);
        }

        return view('distributor.stock.index', compact('warehouse'));
    }

    /**
     * Update stock.
     */
    public function update(Request $request, WarehouseStock $stock)
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock berhasil diupdate.'
            ]);
        }

        return back()->with('success', 'Stock berhasil diupdate.');
    }

    /**
     * Sync all products to warehouse.
     */
    public function sync()
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

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

        return back()->with('success', "{$count} produk berhasil disinkronkan.");
    }

    /**
     * Show stock history for a specific stock.
     */
    public function history(WarehouseStock $stock, Request $request)
    {
        $user = Auth::user();
        
        // Ensure user can only view their own warehouse stock history
        if ($stock->warehouse_id !== $user->warehouse_id) {
            abort(403, 'Unauthorized action.');
        }

        $stock->load(['product.brand', 'product.category', 'warehouse']);

        if ($request->ajax()) {
            $query = WarehouseStockHistory::with(['order', 'user'])
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('product_id', $stock->product_id)
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($history) {
                    if ($history->order) {
                        return '<strong>' . $history->order->order_number . '</strong><br><small class="text-muted">' . $history->order->created_at->format('d M Y') . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('user_info', function ($history) {
                    if ($history->user) {
                        return $history->user->name . '<br><small class="text-muted">' . $history->user->email . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('stock_change', function ($history) {
                    $html = '<div class="text-center">';
                    $html .= '<span class="text-muted">' . number_format($history->stock_before) . '</span>';
                    $html .= ' <i class="fa fa-arrow-right text-primary"></i> ';
                    $html .= '<strong class="text-success">' . number_format($history->stock_after) . '</strong>';
                    $html .= '<br><small class="text-info">+' . number_format($history->quantity_added) . ' unit</small>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('notes_display', function ($history) {
                    return $history->notes ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('created_at_formatted', function ($history) {
                    return $history->created_at->format('d M Y, H:i');
                })
                ->rawColumns(['order_info', 'user_info', 'stock_change', 'notes_display'])
                ->make(true);
        }

        return view('distributor.stock.history', compact('stock'));
    }
}

