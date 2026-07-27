<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display warehouse dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        // Get statistics
        $totalProducts = $warehouse->stocks()->whereHas('product')->count();
        $totalStock = $warehouse->stocks()->whereHas('product')->sum('stock');
        $lowStockProducts = $warehouse->stocks()->whereHas('product')->where('stock', '<=', 10)->count();
        
        // Get recent stock updates
        $recentStocks = WarehouseStock::with('product')
            ->whereHas('product')
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('warehouse.dashboard', compact(
            'warehouse',
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'recentStocks'
        ));
    }
}

