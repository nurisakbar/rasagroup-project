<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    protected $ekspedisiku;

    public function __construct(\App\Services\EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }

    /**
     * Pastikan pesanan berasal dari warehouse user yang sedang login.
     */
    private function authorizeWarehouseOrder(Order $order): void
    {
        $warehouse = auth()->user()->warehouse;
        if (!$warehouse || $order->source_warehouse_id !== $warehouse->id) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Display orders list for warehouse.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if ($request->ajax()) {
            $query = Order::with(['user', 'expedition', 'items.product'])
                ->where('source_warehouse_id', $warehouse->id);

            // Filter by order status
            if ($request->filled('order_status') && $request->order_status != '') {
                $query->where('order_status', $request->order_status);
            }

            // Filter by payment status
            if ($request->filled('payment_status') && $request->payment_status != '') {
                $query->where('payment_status', $request->payment_status);
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
                ->addColumn('order_number_display', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($order->order_type === 'distributor') {
                        $html .= ' <span class="label label-warning" style="font-size: 10px;">DISTRIBUTOR</span>';
                    }
                    return $html;
                })
                ->addColumn('order_date', function ($order) {
                    return $order->created_at->format('d M Y H:i');
                })
                ->addColumn('customer_info', function ($order) {
                    $html = '<strong>' . $order->user->name . '</strong>';
                    $html .= '<br><small class="text-muted">' . $order->user->email . '</small>';
                    if ($order->user->phone) {
                        $html .= '<br><small class="text-muted"><i class="fa fa-phone"></i> ' . $order->user->phone . '</small>';
                    }
                    return $html;
                })
                ->addColumn('order_type_badge', function ($order) {
                    if ($order->order_type === 'distributor') {
                        return '<span class="label label-warning">Distributor</span>';
                    }
                    return '<span class="label label-info">Regular</span>';
                })
                ->addColumn('items_info', function ($order) {
                    $itemsCount = $order->items->count();
                    $totalQty = $order->items->sum('quantity');
                    $html = '<strong>' . $itemsCount . ' produk</strong>';
                    $html .= '<br><small class="text-muted">' . $totalQty . ' item</small>';
                    return $html;
                })
                ->addColumn('total_amount_formatted', function ($order) {
                    return '<strong>Rp ' . number_format($order->total_amount, 0, ',', '.') . '</strong>';
                })
                ->addColumn('order_status_badge', function ($order) {
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
                ->addColumn('payment_status_badge', function ($order) {
                    $paymentClass = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    ][$order->payment_status] ?? 'default';
                    return '<span class="label label-' . $paymentClass . '">' . ucfirst($order->payment_status) . '</span>';
                })
                ->addColumn('expedition_info', function ($order) {
                    if ($order->expedition) {
                        $html = '<strong>' . $order->expedition->name . '</strong>';
                        if ($order->expedition_service) {
                            $html .= '<br><small class="text-muted">' . $order->expedition_service . '</small>';
                        }
                        if ($order->tracking_number) {
                            $html .= '<br><code style="font-size: 11px;">Resi: ' . $order->tracking_number . '</code>';
                        }
                        return $html;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('action', function ($order) {
                    $showUrl = route('warehouse.orders.show', $order);
                    return '<a href="' . $showUrl . '" class="btn btn-info btn-xs" title="Detail">
                        <i class="fa fa-eye"></i>
                    </a>';
                })
                ->rawColumns(['order_number_display', 'customer_info', 'order_type_badge', 'items_info', 'total_amount_formatted', 'order_status_badge', 'payment_status_badge', 'expedition_info', 'action'])
                ->make(true);
        }

        // Get statistics (Default to today's stats if no date filter is provided in the initial load)
        $today = now()->format('Y-m-d');
        
        $totalOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->whereDate('created_at', $today)
            ->count();
            
        $pendingOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->where('order_status', 'pending')
            ->whereDate('created_at', $today)
            ->count();
            
        $processingOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->where('order_status', 'processing')
            ->whereDate('created_at', $today)
            ->count();
            
        $totalRevenue = Order::where('source_warehouse_id', $warehouse->id)
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        return view('warehouse.orders.index', compact(
            'warehouse',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'totalRevenue'
        ));
    }

    /**
     * Show order detail.
     */
    public function show(Order $order)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $this->authorizeWarehouseOrder($order);

        $order->load(['user', 'expedition', 'items.product.brand', 'items.product.category', 'address', 'sourceWarehouse']);

        return view('warehouse.orders.show', compact('warehouse', 'order'));
    }

    /**
     * Update order (status, tracking number, payment status)
     */
    public function update(Request $request, Order $order)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $this->authorizeWarehouseOrder($order);

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

            // If order is completed, credit points for distributor
            if ($request->order_status === 'completed') {
                if (!$order->relationLoaded('user')) {
                    $order->load('user');
                }
                
                // Credit points if not already credited and points_earned > 0
                if (!$order->points_credited && $order->points_earned > 0) {
                    if ($order->order_type === Order::TYPE_DISTRIBUTOR) {
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

    /**
     * Antrekan sinkronisasi pesanan ke QID (QAD).
     */
    /**
     * Debug JSON: endpoint + payload + response create SO ke QID (dry run default).
     */
    public function debugQidSalesOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorizeWarehouseOrder($order);

        $qad = app(\App\Services\QadService::class);
        if (! $qad->isConfigured()) {
            return response()->json([
                'ok' => false,
                'error' => 'QID API belum dikonfigurasi (QIDAPI_* di .env).',
            ], 422);
        }

        $execute = $request->boolean('execute', false);
        $job = new \App\Jobs\SyncOrderToQad($order);
        $payload = $job->debugSalesOrderToQid($qad, $execute);

        return response()->json($payload);
    }

    /**
     * Debug JSON: endpoint + payload + response request pickup EkspedisiKu (dry run default).
     */
    public function debugRequestPickup(Request $request, Order $order): JsonResponse
    {
        $this->authorizeWarehouseOrder($order);

        if (! $order->ekspedisiku_shipment_id) {
            return response()->json([
                'ok' => false,
                'error' => 'Belum ada shipment_id. Buat booking Lion terlebih dahulu.',
            ], 422);
        }

        if (! $order->expedition || $order->expedition->code !== 'lion_parcel') {
            return response()->json([
                'ok' => false,
                'error' => 'Ekspedisi bukan Lion Parcel.',
            ], 422);
        }

        $execute = $request->boolean('execute', false);
        $startAt = now()->addMinutes(30)->toIso8601String();
        $endAt = now()->addMinutes(30)->addHours(4)->toIso8601String();

        $trace = $this->ekspedisiku->requestPickupDebug(
            [(string) $order->ekspedisiku_shipment_id],
            $startAt,
            $endAt,
            $execute
        );

        return response()->json(array_merge($trace, [
            'pickup_window' => ['start_at' => $startAt, 'end_at' => $endAt],
        ]));
    }

    public function syncQad(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        try {
            \App\Jobs\SyncOrderToQad::dispatch($order);

            return back()->with('success', 'Permintaan sinkronisasi telah dikirim ke sistem (antrian). Silakan refresh halaman dalam beberapa saat untuk melihat hasilnya.');
        } catch (\Exception $e) {
            Log::error('Warehouse OrderController: Sync QAD error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memulai sinkronisasi: ' . $e->getMessage());
        }
    }

    public function createEkspedisikuBooking(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if ($order->tracking_number) {
            return back()->with('error', 'Pesanan sudah memiliki nomor resi.');
        }

        if (!$order->expedition || $order->expedition->code !== 'lion_parcel') {
            return back()->with('error', 'Ekspedisi bukan Lion Parcel.');
        }

        try {
            Log::info('Warehouse OrderController: Manual booking requested', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            $job = new \App\Jobs\CreateShipmentBooking($order);
            $job->handle(app(\App\Services\EkspedisiKuService::class));

            $order->refresh();

            if ($order->ekspedisiku_booking_status === 'success' && ($order->tracking_number || $order->ekspedisiku_shipment_id)) {
                \App\Jobs\SyncOrderToQad::dispatch($order);
                $ref = $order->tracking_number ?: $order->ekspedisiku_shipment_id;

                return back()->with('success', 'Booking berhasil! Referensi / resi: ' . $ref . '. Sinkronisasi QID dijadwalkan.');
            }

            return back()->with('error', 'Gagal membuat booking. Cek log EkspedisiKu / CreateShipmentBooking.');
        } catch (\Exception $e) {
            Log::error('Warehouse OrderController: Manual booking error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetEkspedisikuBooking(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if (!$order->expedition || $order->expedition->code !== 'lion_parcel') {
            return back()->with('error', 'Ekspedisi bukan Lion Parcel.');
        }

        $order->update([
            'tracking_number' => null,
            'ekspedisiku_shipment_id' => null,
            'shipped_at' => null,
            'order_status' => 'pending',
            'ekspedisiku_booking_status' => null,
            'ekspedisiku_booking_last_error' => null,
            'ekspedisiku_pickup_requested_at' => null,
            'ekspedisiku_pickup_status' => null,
            'ekspedisiku_pickup_last_error' => null,
        ]);

        return back()->with('success', 'Booking Lion Parcel di-reset. Silakan buat booking ulang.');
    }

    public function requestPickup(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if (!$order->ekspedisiku_shipment_id) {
            return back()->with('error', 'Belum ada shipment_id. Buat booking Lion Parcel terlebih dahulu.');
        }

        if (!$order->expedition || $order->expedition->code !== 'lion_parcel') {
            return back()->with('error', 'Ekspedisi bukan Lion Parcel.');
        }

        $startAt = now()->addMinutes(30)->toIso8601String();
        $endAt = now()->addMinutes(30)->addHours(4)->toIso8601String();

        $result = $this->ekspedisiku->requestPickup([$order->ekspedisiku_shipment_id], $startAt, $endAt);

        if ($result && isset($result['success']) && $result['success']) {
            $order->update([
                'ekspedisiku_pickup_requested_at' => now(),
                'ekspedisiku_pickup_status' => 'success',
                'ekspedisiku_pickup_last_error' => null,
            ]);

            return back()->with('success', 'Request pickup berhasil dikirim.');
        }

        $error = $result['message'] ?? 'Gagal request pickup.';
        if (isset($result['error'])) {
            $error .= ' (' . ($result['error']['message'] ?? json_encode($result['error'])) . ')';
        }

        $order->update([
            'ekspedisiku_pickup_requested_at' => now(),
            'ekspedisiku_pickup_status' => 'failed',
            'ekspedisiku_pickup_last_error' => $error,
        ]);

        return back()->with('error', $error);
    }

    public function cancelPickup(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if (!$order->ekspedisiku_shipment_id) {
            return back()->with('error', 'Belum ada shipment_id.');
        }

        if (!$order->expedition || $order->expedition->code !== 'lion_parcel') {
            return back()->with('error', 'Ekspedisi bukan Lion Parcel.');
        }

        $result = $this->ekspedisiku->cancelPickup((string) $order->ekspedisiku_shipment_id);

        if ($result && isset($result['success']) && $result['success']) {
            $order->update([
                'ekspedisiku_pickup_requested_at' => now(),
                'ekspedisiku_pickup_status' => 'cancelled',
                'ekspedisiku_pickup_last_error' => null,
            ]);

            return back()->with('success', 'Cancel pickup berhasil.');
        }

        $error = $result['message'] ?? 'Gagal cancel pickup.';
        if (isset($result['error'])) {
            $error .= ' (' . ($result['error']['message'] ?? json_encode($result['error'])) . ')';
        }

        $order->update([
            'ekspedisiku_pickup_requested_at' => now(),
            'ekspedisiku_pickup_status' => 'cancel_failed',
            'ekspedisiku_pickup_last_error' => $error,
        ]);

        return back()->with('error', $error);
    }
}

