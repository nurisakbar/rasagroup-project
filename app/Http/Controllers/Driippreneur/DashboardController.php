<?php

namespace App\Http\Controllers\Driippreneur;

use App\Http\Controllers\Controller;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the DRiiPPreneur dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get stats for this DRiiPPreneur's warehouse
        $warehouse = $user->warehouse;
        
        $totalProducts = 0;
        $totalStock = 0;
        $lowStockProducts = 0;
        $recentStocks = collect();
        
        if ($warehouse) {
            $totalProducts = WarehouseStock::where('warehouse_id', $warehouse->id)->count();
            $totalStock = WarehouseStock::where('warehouse_id', $warehouse->id)->sum('stock');
            $lowStockProducts = WarehouseStock::where('warehouse_id', $warehouse->id)
                ->where('stock', '<=', 10)
                ->count();
            $recentStocks = WarehouseStock::with('product')
                ->where('warehouse_id', $warehouse->id)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('driippreneur.dashboard', compact(
            'user',
            'warehouse',
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'recentStocks'
        ));
    }
}

