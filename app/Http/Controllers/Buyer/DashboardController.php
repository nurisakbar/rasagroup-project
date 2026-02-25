<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('order_status', 'pending')->count();

        // Distributor specific data
        $totalIncomingOrders = 0;
        $pendingIncomingOrders = 0;
        $isDistributor = $user->isDistributor();
        $warehouse = null;

        if ($isDistributor) {
            $warehouse = $user->warehouse;
            if ($warehouse) {
                $totalIncomingOrders = Order::where('source_warehouse_id', $warehouse->id)->count();
                $pendingIncomingOrders = Order::where('source_warehouse_id', $warehouse->id)
                    ->where('order_status', 'pending')
                    ->count();
            }
        }

        if ($request->ajax()) {
            $type = $request->get('type', 'own'); // 'own' or 'incoming'
            
            if ($type === 'incoming' && $isDistributor && $warehouse) {
                $query = Order::where('source_warehouse_id', $warehouse->id)
                    ->with(['user', 'expedition']);
            } else {
                $query = Order::where('user_id', $user->id)
                    ->with(['expedition', 'sourceWarehouse']);
            }

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('order_status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($order) use ($type) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($type === 'own' && $order->order_type === 'distributor') {
                        $html .= '<br><span class="badge bg-warning" style="font-size: 10px;">DISTRIBUTOR</span>';
                    }
                    if ($type === 'incoming') {
                        $html .= '<br><small class="text-muted">' . $order->user->name . '</small>';
                    }
                    return $html;
                })
                ->addColumn('date_formatted', function ($order) {
                    return $order->created_at->format('d M Y');
                })
                ->addColumn('total_formatted', function ($order) {
                    return 'Rp ' . number_format($order->total_amount, 0, ',', '.');
                })
                ->addColumn('status_badge', function ($order) {
                    $statusClass = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    ][$order->order_status] ?? 'secondary';

                    $statusText = [
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ][$order->order_status] ?? ucfirst($order->order_status);

                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($order) use ($type) {
                    if ($type === 'incoming') {
                        return '<a href="' . route('distributor.manage-orders.show', $order) . '" class="btn btn-sm btn-info text-white">
                            <i class="fi-rs-shopping-bag mr-5"></i> Kelola
                        </a>';
                    }
                    
                    $route = $order->order_type === 'distributor' ? 'distributor.orders.show' : 'buyer.orders.show';
                    
                    return '<a href="' . route($route, $order) . '" class="btn btn-sm btn-primary">
                        <i class="fi-rs-eye mr-5"></i> Detail
                    </a>';
                })
                ->rawColumns(['order_info', 'status_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->make(true);
        }

        return view('buyer.dashboard', compact(
            'totalOrders', 
            'pendingOrders', 
            'totalIncomingOrders', 
            'pendingIncomingOrders',
            'isDistributor'
        ));
    }
}
