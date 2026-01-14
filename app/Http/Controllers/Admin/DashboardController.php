<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $totalBuyers = User::where('role', 'buyer')->count();
        $totalResellers = User::where('role', 'reseller')->count();
        $totalDistributors = User::where('role', User::ROLE_DISTRIBUTOR)->count();
        $totalDriippreneurs = User::where('role', User::ROLE_DRIIPPRENEUR)->count();
        
        // This month statistics
        $thisMonthRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $thisMonthOrders = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Online vs Offline statistics
        $thisMonthOnlineRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->where('order_type', 'regular')
            ->sum('total_amount');
        
        $thisMonthOfflineRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->where('order_type', 'pos')
            ->sum('total_amount');
        
        $thisMonthOnlineOrders = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('order_type', 'regular')
            ->count();
        
        $thisMonthOfflineOrders = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('order_type', 'pos')
            ->count();
        
        // Recent orders
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        
        // Sales statistics (last 7 days)
        $salesLast7Days = Order::where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Order count per date (last 30 days)
        $orderCounts = Order::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare chart data
        $chartLabels = [];
        $chartData = [];
        
        // Fill in all dates for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            
            $orderCount = $orderCounts->firstWhere('date', $date);
            $chartData[] = $orderCount ? $orderCount->count : 0;
        }

        // Orders by warehouse statistics
        $ordersByWarehouse = Order::select('source_warehouse_id', DB::raw('COUNT(*) as order_count'))
            ->whereNotNull('source_warehouse_id')
            ->groupBy('source_warehouse_id')
            ->orderByDesc('order_count')
            ->get();

        $warehouseLabels = [];
        $warehouseData = [];
        
        foreach ($ordersByWarehouse as $warehouseOrder) {
            $warehouse = Warehouse::find($warehouseOrder->source_warehouse_id);
            if ($warehouse) {
                $warehouseLabels[] = $warehouse->name;
                $warehouseData[] = $warehouseOrder->order_count;
            }
        }

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'pendingOrders',
            'totalBuyers',
            'totalResellers',
            'totalDistributors',
            'totalDriippreneurs',
            'thisMonthRevenue',
            'thisMonthOrders',
            'thisMonthOnlineRevenue',
            'thisMonthOfflineRevenue',
            'thisMonthOnlineOrders',
            'thisMonthOfflineOrders',
            'recentOrders',
            'salesLast7Days',
            'chartLabels',
            'chartData',
            'warehouseLabels',
            'warehouseData'
        ));
    }
}
