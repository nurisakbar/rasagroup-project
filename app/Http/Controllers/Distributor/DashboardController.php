<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the Distributor dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

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

        // Sales revenue per day (last 30 days) - orders that come to this warehouse
        $salesRevenue = Order::where('source_warehouse_id', $warehouse->id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare chart data for last 30 days
        $chartLabels = [];
        $chartData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            
            $dayRevenue = $salesRevenue->firstWhere('date', $date);
            $chartData[] = $dayRevenue ? (float) $dayRevenue->revenue : 0;
        }

        // This month statistics
        $thisMonthRevenue = Order::where('source_warehouse_id', $warehouse->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $thisMonthOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('distributor.dashboard', compact(
            'user',
            'warehouse',
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'recentStocks',
            'chartLabels',
            'chartData',
            'thisMonthRevenue',
            'thisMonthOrders'
        ));
    }
}

