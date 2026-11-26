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
use Barryvdh\DomPDF\Facade\Pdf;

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
            $query = Order::with(['user', 'expedition', 'sourceWarehouse.wilayah'])
                ->whereIn('source_warehouse_id', $this->ownWarehouseIds())
                ->where('finance_approved', true);

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
                    if ($order->order_type === 'pos') {
                        $type = '<span class="mo-badge mo-tone-info">Offline (POS)</span>';
                    } elseif ($order->order_type === 'distributor') {
                        $type = '<span class="mo-badge mo-tone-warn">Distributor</span>';
                    } elseif ($order->fulfilled_by_distributor) {
                        $type = '<span class="mo-badge mo-tone-primary">Subdist</span>';
                    } else {
                        $type = '<span class="mo-badge mo-tone-primary">Online</span>';
                    }

                    return '<div class="mo-order"><strong>' . e($order->order_number) . '</strong>' . $type . '</div>'
                        . '<div class="mo-date">' . $order->created_at->format('d M Y, H:i') . '</div>';
                })
                ->addColumn('buyer_info', function ($order) {
                    return '<div class="mo-buyer"><strong>' . e($order->user->name) . '</strong>'
                        . '<span class="mo-email" title="' . e($order->user->email) . '">' . e($order->user->email) . '</span></div>';
                })
                ->addColumn('expedition_info', function ($order) {
                    if ($order->fulfillmentMode() === 'pickup') {
                        return '<span class="mo-exp"><i class="fi-rs-shop"></i> Ambil di tempat</span>';
                    }
                    if ($order->fulfillmentMode() === 'store_delivery') {
                        return '<span class="mo-exp"><i class="fi-rs-package"></i> Diantar Ketempat</span>';
                    }
                    if ($order->expedition) {
                        $html = '<span class="mo-exp">' . e($order->expedition->name) . '</span>';
                        $html .= $order->tracking_number
                            ? '<div class="mo-resi">' . e($order->tracking_number) . '</div>'
                            : '<div class="mo-resi mo-resi-none">Belum ada resi</div>';

                        return $html;
                    }

                    return '<span class="mo-muted">-</span>';
                })
                ->addColumn('total_formatted', function ($order) {
                    return '<span class="mo-total">Rp ' . number_format($order->total_amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('status_badge', function ($order) {
                    $tone = [
                        'pending' => 'warn', 'processing' => 'info', 'shipped' => 'primary',
                        'delivered' => 'ok', 'completed' => 'ok', 'cancelled' => 'bad',
                    ][$order->order_status] ?? 'muted';

                    return '<span class="mo-badge mo-status mo-tone-' . $tone . '">' . e($order->statusLabel()) . '</span>';
                })
                ->addColumn('payment_badge', function ($order) {
                    $tone = [
                        'paid' => 'ok', 'failed' => 'bad', 'refunded' => 'info',
                    ][$order->payment_status] ?? 'warn';

                    return '<span class="mo-badge mo-status mo-tone-' . $tone . '">' . e(Order::paymentStatusLabel($order->payment_status)) . '</span>';
                })
                ->addColumn('action', function ($order) {
                    return '<div class="mo-actions">'
                        . '<a href="' . route('distributor.manage-orders.show', $order) . '" class="mo-btn mo-btn-main">Detail</a>'
                        . '<a href="' . route('distributor.manage-orders.surat-jalan-pdf', $order) . '" target="_blank" class="mo-btn mo-btn-out mo-btn-icon" title="Cetak surat jalan"><i class="fi-rs-print"></i><span class="mo-btn-text">Cetak</span></a>'
                        . '</div>';
                })
                ->rawColumns(['order_info', 'buyer_info', 'expedition_info', 'total_formatted', 'status_badge', 'payment_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->make(true);
        }

        return view('buyer.distributor.manage-orders.index', compact('warehouse'));
    }

    /**
     * Show order detail.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if (! in_array($order->source_warehouse_id, $this->ownWarehouseIds(), true)) {
            abort(403, 'Akses ditolak.');
        }

        if (!$order->isReleasedToHub()) {
            abort(403, 'Pesanan belum disetujui finance / belum siap diproses.');
        }

        $order->load(['user', 'items.product.brand', 'items.product.category', 'address', 'sourceWarehouse', 'expedition']);

        return view('buyer.distributor.manage-orders.show', compact('warehouse', 'order'));
    }

    /**
     * Update order (status, tracking number, payment status)
     */
    public function update(Request $request, Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if (! in_array($order->source_warehouse_id, $this->ownWarehouseIds(), true)) {
            abort(403, 'Akses ditolak.');
        }

        if (!$order->isReleasedToHub()) {
            abort(403, 'Pesanan belum disetujui finance / belum siap diproses.');
        }

        $request->validate([
            'order_status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded,term_of_payment',
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

        // Order subdist: stok gudang distributor harus sinkron dengan status. Selain batal, pastikan
        // stok sudah terpotong (tidak ganda bila sudah terpotong saat order dibuat); batal mengembalikan stok.
        $stockNote = null;
        if (! empty($updateData['order_status']) && $order->fulfilled_by_distributor) {
            $stock = app(\App\Services\SubdistStockService::class);
            try {
                if ($updateData['order_status'] === 'cancelled') {
                    $stock->restore($order);
                    $stockNote = 'Stok gudang dikembalikan.';
                } else {
                    $label = $order->statusOptions()[$updateData['order_status']] ?? $updateData['order_status'];
                    $cut = $stock->ensureDeducted($order, 'status ' . $label);
                    $stockNote = $cut > 0 ? "Stok gudang dipotong {$cut} unit." : null;
                }
            } catch (\RuntimeException $e) {
                return back()->with('error', 'Status tidak diubah. ' . $e->getMessage());
            }
        }

        if (!empty($updateData)) {
            $oldStatus = $order->order_status;
            $order->update($updateData);

            if (isset($updateData['order_status']) && $updateData['order_status'] === 'shipped' && $oldStatus !== 'shipped') {
                if ($order->user) {
                    $order->user->notify(new \App\Notifications\Orders\OrderShippedNotification($order));
                }
            }

            $message = 'Berhasil memperbarui: ' . implode(', ', $messages) . ($stockNote ? '. ' . $stockNote : '');
            return back()->with('success', $message);
        }

        return back()->with('info', 'Tidak ada perubahan yang disimpan.');
    }

    /**
     * Generate PDF for Surat Jalan.
     */
    public function suratJalanPdf(Order $order)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        // Verify the order belongs to user's warehouse
        if (! in_array($order->source_warehouse_id, $this->ownWarehouseIds(), true)) {
            abort(403, 'Akses ditolak.');
        }

        if (!$order->isReleasedToHub()) {
            abort(403, 'Pesanan belum disetujui finance / belum siap diproses.');
        }

        $order->load(['user', 'items.product.brand', 'items.product.category', 'address', 'sourceWarehouse', 'expedition']);

        $pdf = Pdf::loadView('buyer.distributor.manage-orders.surat-jalan-pdf', compact('warehouse', 'order'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Jalan_' . $order->order_number . '.pdf');
    }


    /**
     * ID semua gudang milik distributor ini (utama + tambahan).
     *
     * @return array<int, string>
     */
    private function ownWarehouseIds(): array
    {
        return Auth::user()->distributorWarehouses()->pluck('id')->map(fn ($id) => (string) $id)->all();
    }
}

