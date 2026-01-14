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
        $totalOrders = Order::where('user_id', Auth::id())->count();
        $pendingOrders = Order::where('user_id', Auth::id())->where('order_status', 'pending')->count();

        if ($request->ajax()) {
            $query = Order::where('user_id', Auth::id())
                ->with(['expedition', 'sourceWarehouse']);

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('order_status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($order->order_type === 'distributor') {
                        $html .= '<br><span class="badge bg-warning" style="font-size: 10px;">DISTRIBUTOR</span>';
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
                ->addColumn('action', function ($order) {
                    return '<a href="' . route('buyer.orders.show', $order) . '" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> Detail
                    </a>';
                })
                ->rawColumns(['order_info', 'status_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->make(true);
        }

        return view('buyer.dashboard', compact('totalOrders', 'pendingOrders'));
    }
}
