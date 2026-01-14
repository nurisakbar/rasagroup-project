<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ManageOrderController extends Controller
{
    /**
     * Display orders list for distributor warehouse.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if ($request->ajax()) {
            $query = Order::with(['user', 'expedition', 'sourceWarehouse.province', 'sourceWarehouse.regency'])
                ->where('source_warehouse_id', $warehouse->id);

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
                    return '<a href="' . route('distributor.manage-orders.show', $order) . '" class="btn btn-info btn-xs">
                        <i class="fa fa-eye"></i> Detail
                    </a>';
                })
                ->rawColumns(['order_info', 'buyer_info', 'expedition_info', 'total_formatted', 'status_badge', 'payment_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->make(true);
        }

        return view('distributor.manage-orders.index', compact('warehouse'));
    }

    /**
     * Show order detail.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if ($order->source_warehouse_id !== $warehouse->id) {
            abort(403, 'Akses ditolak.');
        }

        $order->load(['user', 'items.product.brand', 'items.product.category', 'address', 'sourceWarehouse', 'expedition']);

        return view('distributor.manage-orders.show', compact('warehouse', 'order'));
    }

    /**
     * Update order (status, tracking number, payment status)
     */
    public function update(Request $request, Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if ($order->source_warehouse_id !== $warehouse->id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'order_status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
        ]);

        $updateData = [];
        $messages = [];

        // Update order status
        if ($request->filled('order_status')) {
            $updateData['order_status'] = $request->order_status;
            
            // If status changed to shipped and no shipped_at date, set it
            if ($request->order_status === 'shipped' && !$order->shipped_at) {
                $updateData['shipped_at'] = now();
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

    /**
     * Convert order items to stock (for delivered/completed orders)
     */
    public function convertToStock(Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if ($order->source_warehouse_id !== $warehouse->id) {
            abort(403, 'Akses ditolak.');
        }

        // Check if order is delivered or completed
        if (!in_array($order->order_status, ['delivered', 'completed'])) {
            return back()->with('error', 'Pesanan harus berstatus Delivered atau Completed untuk dikonversi ke stock.');
        }

        // Load order items with products
        $order->load('items.product');

        if ($order->items->isEmpty()) {
            return back()->with('error', 'Pesanan tidak memiliki item.');
        }

        DB::beginTransaction();
        try {
            $convertedItems = [];
            $totalQuantity = 0;
            $skippedItems = [];
            
            foreach ($order->items as $item) {
                if (!$item->product) {
                    $skippedItems[] = 'Item dengan ID ' . $item->id . ' (produk tidak ditemukan)';
                    continue;
                }
                
                if ($item->quantity <= 0) {
                    $skippedItems[] = $item->product->name . ' (quantity tidak valid)';
                    continue;
                }

                // Get or create warehouse stock
                $warehouseStock = WarehouseStock::firstOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'stock' => 0,
                    ]
                );

                // Record stock before
                $stockBefore = $warehouseStock->stock;
                
                // Add quantity to stock
                $warehouseStock->increment('stock', $item->quantity);
                
                // Record stock after
                $stockAfter = $warehouseStock->stock;
                
                // Create stock history record
                WarehouseStockHistory::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $item->product_id,
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'quantity_added' => $item->quantity,
                    'notes' => 'Konversi dari order ' . $order->order_number,
                ]);
                
                $convertedItems[] = [
                    'product' => $item->product->name,
                    'quantity' => $item->quantity,
                    'old_stock' => $stockBefore,
                    'new_stock' => $stockAfter,
                ];
                
                $totalQuantity += $item->quantity;
            }

            if (empty($convertedItems)) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada item yang valid untuk dikonversi.');
            }

            DB::commit();

            $itemsCount = count($convertedItems);
            $message = "Berhasil mengkonversi {$itemsCount} produk ({$totalQuantity} unit) dari pesanan ke stock warehouse.";
            
            if (!empty($skippedItems)) {
                $message .= " Item yang dilewati: " . implode(', ', $skippedItems);
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error converting order to stock: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengkonversi pesanan ke stock: ' . $e->getMessage());
        }
    }
}

