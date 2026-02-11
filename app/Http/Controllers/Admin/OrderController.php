<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['user', 'expedition', 'sourceWarehouse.province', 'sourceWarehouse.regency']);

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('order_status', $request->status);
            }

            // Filter by order type
            if ($request->filled('order_type') && $request->order_type != '') {
                $query->where('order_type', $request->order_type);
            }

            // Filter by date range
            if ($request->filled('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Filter by source warehouse (hub)
            if ($request->filled('source_warehouse_id') && $request->source_warehouse_id != '') {
                $query->where('source_warehouse_id', $request->source_warehouse_id);
            }

            // Global search
            if ($request->filled('search') && $request->search['value'] != '') {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('order_number', 'like', "%{$searchValue}%")
                      ->orWhereHas('user', function ($u) use ($searchValue) {
                          $u->where('name', 'like', "%{$searchValue}%");
                      });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($order->order_type === 'pos') {
                        $html .= '<br><span class="label label-info" style="font-size: 10px;"><i class="fa fa-cash-register"></i> OFFLINE (POS)</span>';
                    } elseif ($order->order_type === 'distributor') {
                        $html .= '<br><span class="label label-warning" style="font-size: 10px;">DISTRIBUTOR</span>';
                    } else {
                        $html .= '<br><span class="label label-primary" style="font-size: 10px;"><i class="fa fa-globe"></i> ONLINE</span>';
                    }
                    $html .= '<br><small class="text-muted">' . $order->created_at->format('d M Y H:i') . '</small>';
                    return $html;
                })
                ->addColumn('buyer_info', function ($order) {
                    return $order->user->name . '<br><small class="text-muted">' . $order->user->email . '</small>';
                })
                ->addColumn('expedition_info', function ($order) {
                    if ($order->expedition) {
                        $html = $order->expedition->name;
                        if ($order->tracking_number) {
                            $html .= '<br><code style="font-size: 11px;">' . $order->tracking_number . '</code>';
                        } else {
                            $html .= '<br><small class="text-warning">Belum ada resi</small>';
                        }
                        return $html;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('hub_info', function ($order) {
                    if ($order->sourceWarehouse) {
                        $html = '<strong><i class="fa fa-building"></i> ' . $order->sourceWarehouse->name . '</strong>';
                        if ($order->sourceWarehouse->full_location) {
                            $html .= '<br><small class="text-muted">' . $order->sourceWarehouse->full_location . '</small>';
                        }
                        return $html;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('total_formatted', function ($order) {
                    return '<strong>Rp ' . number_format($order->total_amount, 0, ',', '.') . '</strong>';
                })
                ->addColumn('status_badge', function ($order) {
                    $statusClass = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    ][$order->order_status] ?? 'default';
                    return '<span class="label label-' . $statusClass . '">' . ucfirst($order->order_status) . '</span>';
                })
                ->addColumn('payment_badge', function ($order) {
                    $paymentClass = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    ][$order->payment_status] ?? 'default';
                    return '<span class="label label-' . $paymentClass . '">' . ucfirst($order->payment_status) . '</span>';
                })
                ->addColumn('action', function ($order) {
                    return '<a href="' . route('admin.orders.show', $order) . '" class="btn btn-info btn-xs">
                        <i class="fa fa-eye"></i> Detail
                    </a>';
                })
                ->rawColumns(['order_info', 'buyer_info', 'expedition_info', 'hub_info', 'total_formatted', 'status_badge', 'payment_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->make(true);
        }

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.orders.index', compact('warehouses'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'expedition', 'address', 'sourceWarehouse']);
        $expeditions = \App\Models\Expedition::where('is_active', true)->get();
        return view('admin.orders.show', compact('order', 'expeditions'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
        ]);

        $updateData = ['order_status' => $request->order_status];

        // If status changed to shipped and no shipped_at date, set it
        if ($request->order_status === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        }

        // If order is completed and it's a distributor order, credit points
        if ($request->order_status === 'completed') {
            // Load user relationship if not already loaded
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            
            // Credit points for distributor orders
            if ($order->order_type === Order::TYPE_DISTRIBUTOR && 
                !$order->points_credited && 
                $order->points_earned > 0) {
                
                $order->user->increment('points', $order->points_earned);
                $updateData['points_credited'] = true;
            }
        }

        $order->update($updateData);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        $updateData = [
            'tracking_number' => $request->tracking_number,
        ];

        // If tracking number is set and status is still pending/processing, change to shipped
        if (in_array($order->order_status, ['pending', 'processing'])) {
            $updateData['order_status'] = 'shipped';
            $updateData['shipped_at'] = now();
        }

        $order->update($updateData);

        return back()->with('success', 'Nomor resi berhasil disimpan.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $updateData = ['payment_status' => $request->payment_status];

        // If payment is marked as paid, record the timestamp
        if ($request->payment_status === 'paid' && !$order->paid_at) {
            $updateData['paid_at'] = now();
        }

        $order->update($updateData);

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    /**
     * Update order status, tracking number, and payment status in one request
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'expedition_id' => 'nullable|exists:expeditions,id',
        ]);

        $updateData = [];
        $messages = [];

        // Update expedition
        if ($request->filled('expedition_id')) {
            $updateData['expedition_id'] = $request->expedition_id;
            $messages[] = 'Ekspedisi';
        }

        // Update order status
        if ($request->filled('order_status')) {
            $updateData['order_status'] = $request->order_status;
            
            // If status changed to shipped and no shipped_at date, set it
            if ($request->order_status === 'shipped' && !$order->shipped_at) {
                $updateData['shipped_at'] = now();
            }

            // If order is completed, credit points for distributor or driippreneur
            if ($request->order_status === 'completed') {
                // Load user relationship if not already loaded
                if (!$order->relationLoaded('user')) {
                    $order->load('user');
                }
                
                // Credit points if not already credited and points_earned > 0
                if (!$order->points_credited && $order->points_earned > 0) {
                    // Credit points for distributor orders
                    if ($order->order_type === Order::TYPE_DISTRIBUTOR) {
                        $order->user->increment('points', $order->points_earned);
                        $updateData['points_credited'] = true;
                    }
                    
                    // Credit points for regular orders by approved DRiiPPreneur
                    if ($order->order_type === Order::TYPE_REGULAR && $order->user->isDriippreneurApproved()) {
                        $order->user->increment('points', $order->points_earned);
                        $updateData['points_credited'] = true;
                    }
                }
            }
            
            $messages[] = 'Status pesanan';
        }

        // Update tracking number (only if not empty)
        if ($request->filled('tracking_number') && trim($request->tracking_number) !== '') {
            $updateData['tracking_number'] = trim($request->tracking_number);
            
            // If tracking number is set and status is still pending/processing, change to shipped
            if (in_array($order->order_status, ['pending', 'processing']) && 
                (!isset($updateData['order_status']) || in_array($updateData['order_status'], ['pending', 'processing']))) {
                $updateData['order_status'] = 'shipped';
                if (!isset($updateData['shipped_at'])) {
                    $updateData['shipped_at'] = now();
                }
            }
            
            $messages[] = 'Nomor resi';
        }

        // Update payment status
        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->payment_status;

            // If payment is marked as paid, record the timestamp
            if ($request->payment_status === 'paid' && !$order->paid_at) {
                $updateData['paid_at'] = now();
            }
            
            $messages[] = 'Status pembayaran';
        }

        if (!empty($updateData)) {
            $order->update($updateData);
            $message = 'Berhasil memperbarui: ' . implode(', ', $messages);
            return back()->with('success', $message);
        }

        return back()->with('info', 'Tidak ada perubahan yang disimpan.');
    }

    public function trackOrder(Order $order)
    {
        if (!$order->tracking_number || !$order->expedition) {
            return response()->json(['success' => false, 'message' => 'Resi atau ekspedisi belum tersedia'], 400);
        }

        $rajaOngkir = new \App\Services\RajaOngkirService();
        $code = strtolower($order->expedition->code);
        
        // Map courier codes if necessary (e.g., if database has 'jne' but API needs 'jne')
        // RajaOngkir supports: jne, pos, tiki, wahana, jnt, rpx, sap, sicepat, pcp, jet, dse, first, ninja, lion, idl, rex, ide, sentral
        
        $result = $rajaOngkir->trackWaybill($order->tracking_number, $code);

        if ($result && isset($result['data']) && !is_null($result['data'])) {
            return response()->json(['success' => true, 'data' => $result['data']]);
        }
        
        // Check for specific error message from RajaOngkir/Komerce
        $errorMessage = 'Gagal melacak resi via RajaOngkir';
        if (isset($result['meta']['message'])) {
            $errorMessage .= ': ' . $result['meta']['message'];
        } elseif (isset($result['status']['description'])) {
            $errorMessage .= ': ' . $result['status']['description'];
        }

        return response()->json(['success' => false, 'message' => $errorMessage], 400);
    }
}
