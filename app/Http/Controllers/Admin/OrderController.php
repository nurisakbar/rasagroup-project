<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\Orders\OrderProcessingNotification;
use App\Notifications\Orders\OrderPickupReadyNotification;
use App\Notifications\Orders\OrderShippedNotification;
use App\Notifications\Orders\OrderCompletedNotification;

class OrderController extends Controller
{
    protected $ekspedisiku;

    public function __construct(\App\Services\EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['user', 'expedition', 'sourceWarehouse.wilayah']);

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

            $baseQuery = clone $query;

            if ($request->filled('tab_status') && $request->tab_status != '') {
                if ($request->tab_status === 'menunggu_pembayaran') {
                    $query->where('payment_status', 'pending')->whereNull('payment_proof');
                } elseif ($request->tab_status === 'menunggu_konfirmasi') {
                    $query->where('payment_status', 'pending')->whereNotNull('payment_proof');
                } elseif ($request->tab_status === 'sedang_diproses') {
                    $query->where('payment_status', 'paid')->whereIn('order_status', ['pending', 'processing']);
                } elseif ($request->tab_status === 'dikirim') {
                    $query->where('order_status', 'shipped');
                } elseif ($request->tab_status === 'selesai') {
                    $query->whereIn('order_status', ['delivered', 'completed']);
                }
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
                    if ($order->payment_status === 'pending' && $order->payment_proof) {
                        return '<span class="label label-warning" style="background-color: #ff851b !important; font-size: 11px;"><i class="fa fa-bell"></i> PERLU KONFIRMASI</span>';
                    }
                    $paymentClass = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    ][$order->payment_status] ?? 'default';
                    return '<span class="label label-' . $paymentClass . '">' . ucfirst($order->payment_status) . '</span>';
                })
                ->addColumn('action', function ($order) {
                    $btn = '<a href="' . route('admin.orders.show', $order) . '" class="btn btn-info btn-xs" style="margin-right: 3px;">
                        <i class="fa fa-eye"></i> Detail
                    </a>';
                    if ($order->payment_status === 'pending' && $order->payment_proof) {
                        $btn .= '<a href="' . route('admin.orders.show', $order) . '" class="btn btn-warning btn-xs" title="Verifikasi Bukti Bayar" style="background-color: #ff851b; border-color: #ff851b;">
                            <i class="fa fa-check-square-o"></i> Verifikasi
                        </a>';
                    }
                    return $btn;
                })
                ->rawColumns(['order_info', 'buyer_info', 'expedition_info', 'hub_info', 'total_formatted', 'status_badge', 'payment_badge', 'action'])
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->with([
                    'counts' => [
                        'semua' => (clone $baseQuery)->count(),
                        'menunggu_pembayaran' => (clone $baseQuery)->where('payment_status', 'pending')->whereNull('payment_proof')->count(),
                        'menunggu_konfirmasi' => (clone $baseQuery)->where('payment_status', 'pending')->whereNotNull('payment_proof')->count(),
                        'sedang_diproses' => (clone $baseQuery)->where('payment_status', 'paid')->whereIn('order_status', ['pending', 'processing'])->count(),
                        'dikirim' => (clone $baseQuery)->where('order_status', 'shipped')->count(),
                        'selesai' => (clone $baseQuery)->whereIn('order_status', ['delivered', 'completed'])->count(),
                    ]
                ])
                ->make(true);
        }

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $countSemua = Order::count();
        $countMenungguPembayaran = Order::where('payment_status', 'pending')->whereNull('payment_proof')->count();
        $countMenungguKonfirmasi = Order::where('payment_status', 'pending')->whereNotNull('payment_proof')->count();
        $countSedangDiproses = Order::where('payment_status', 'paid')->whereIn('order_status', ['pending', 'processing'])->count();
        $countDikirim = Order::where('order_status', 'shipped')->count();
        $countSelesai = Order::whereIn('order_status', ['delivered', 'completed'])->count();

        return view('admin.orders.index', compact('warehouses', 'countSemua', 'countMenungguPembayaran', 'countMenungguKonfirmasi', 'countSedangDiproses', 'countDikirim', 'countSelesai'));
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

        if (in_array($request->order_status, ['delivered', 'completed']) && !$order->received_at) {
            $updateData['received_at'] = now();
        }

        // If order is completed, credit points
        if ($request->order_status === 'completed') {
            $order->creditPoints();
        }

        $oldStatus = $order->order_status;
        $oldPickupReady = $order->pickup_ready_at;

        $order->update($updateData);

        $this->sendOrderTransitionNotifications($order, $oldStatus, $oldPickupReady);

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
        
        // Dispatch background job for tracking notification
        \App\Jobs\SendWhatsAppNotification::dispatch($order, 'tracking');

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
            // Credit points when marked as paid
            $order->creditPoints();
        }

        $order->update($updateData);

        // Dispatch background jobs for notifications
        if ($request->payment_status === 'paid') {
            \App\Jobs\SendWhatsAppNotification::dispatch($order, 'thank_you');
            \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_notification');
            
            // Sync to QAD
            \App\Support\SalesOrderSyncDispatcher::dispatch($order);
        }

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
            'pickup_ready_at' => 'nullable|date',
            'shipped_at' => 'nullable|date',
            'pickup_note' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->order_status;
        $oldPickupReady = $order->pickup_ready_at;

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
            if ($request->order_status === 'shipped' && !$order->shipped_at && !$request->has('shipped_at')) {
                $updateData['shipped_at'] = now();
            }

            if (in_array($request->order_status, ['delivered', 'completed']) && !$order->received_at) {
                $updateData['received_at'] = now();
            }

            // If order is completed, credit points
            if ($request->order_status === 'completed') {
                $order->creditPoints();
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
                $order->creditPoints();
            }
            
            $messages[] = 'Status pembayaran';
        }

        // Update pickup fields
        if ($request->has('pickup_ready_at')) {
            $updateData['pickup_ready_at'] = $request->filled('pickup_ready_at') ? \Carbon\Carbon::parse($request->pickup_ready_at) : null;
            $messages[] = 'Jadwal pengambilan';
        }
        if ($request->has('shipped_at')) {
            $updateData['shipped_at'] = $request->filled('shipped_at') ? \Carbon\Carbon::parse($request->shipped_at) : null;
            $messages[] = 'Waktu penyerahan/diambil';
            
            // If handover timestamp is entered and status is still pending/processing, move to shipped (or completed)
            if ($request->filled('shipped_at') && in_array($order->order_status, ['pending', 'processing']) && 
                (!isset($updateData['order_status']) || in_array($updateData['order_status'], ['pending', 'processing']))) {
                $updateData['order_status'] = 'shipped';
            }
        }
        if ($request->has('pickup_note')) {
            $updateData['pickup_note'] = $request->pickup_note;
            $messages[] = 'Catatan pengambilan';
        }

        if (!empty($updateData)) {
            $order->update($updateData);
            
            $this->sendOrderTransitionNotifications($order, $oldStatus, $oldPickupReady);

            // Dispatch background job for tracking notification
            if (isset($updateData['tracking_number'])) {
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'tracking');
            }

            $message = 'Berhasil memperbarui: ' . implode(', ', $messages);
            // Dispatch background jobs for notifications
            if (isset($updateData['payment_status']) && $updateData['payment_status'] === 'paid') {
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'thank_you');
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_notification');

                // Sync to QAD
                \App\Support\SalesOrderSyncDispatcher::dispatch($order);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);
        }

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan yang disimpan.']);
        }
        return back()->with('info', 'Tidak ada perubahan yang disimpan.');
    }

    public function trackOrder(Order $order)
    {
        if (!$order->tracking_number || !$order->expedition) {
            return response()->json(['success' => false, 'message' => 'Resi atau ekspedisi belum tersedia'], 400);
        }

        $code = strtolower($order->expedition->code);

        if ($code === 'lion_parcel' || $code === 'lalamove') {
            $result = $this->ekspedisiku->track($order->tracking_number, $code);
            
            if ($result && isset($result['success']) && $result['success']) {
                // EkspedisiKu track endpoint returns normalized payload (e.g. {success,resi,carriers,...}).
                // Return the full payload unless it already contains a 'data' wrapper.
                return response()->json([
                    'success' => true,
                    'data' => $result['data'] ?? $result,
                ]);
            }
        } else {
            $rajaOngkir = new \App\Services\RajaOngkirService();
            $result = $rajaOngkir->trackWaybill($order->tracking_number, $code);

            if ($result && isset($result['data']) && !is_null($result['data'])) {
                return response()->json(['success' => true, 'data' => $result['data']]);
            }
        }
        
        // Check for specific error message
        $errorMessage = 'Gagal melacak resi';
        if (!isset($result) || !is_array($result)) {
            return response()->json(['success' => false, 'message' => $errorMessage], 400);
        }
        if (isset($result['meta']['message'])) {
            $errorMessage .= ': ' . $result['meta']['message'];
        } elseif (isset($result['status']['description'])) {
            $errorMessage .= ': ' . $result['status']['description'];
        } elseif (isset($result['message'])) {
            $errorMessage .= ': ' . $result['message'];
        }

        return response()->json(['success' => false, 'message' => $errorMessage], 400);
    }

    public function createEkspedisikuBooking(Order $order)
    {
        if ($order->tracking_number) {
            return back()->with('error', 'Pesanan sudah memiliki nomor resi.');
        }

        if (! $order->expedition || ! in_array($order->expedition->code, ['lion_parcel', 'lalamove', 'jne', 'jnt', 'sicepat', 'sap', 'ninja', 'idexpress', 'borzo'], true)) {
            return back()->with('error', 'Ekspedisi tidak didukung untuk booking EkspedisiKu.');
        }

        try {
            \Log::info('Admin OrderController: Manual booking requested', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'carrier' => $order->expedition->code,
                'existing_tracking_number' => $order->tracking_number,
                'existing_shipment_id' => $order->ekspedisiku_shipment_id,
            ]);
            // Dispatch the job synchronously to get immediate result
            $job = new \App\Jobs\CreateShipmentBooking($order);
            $job->handle(app(\App\Services\EkspedisiKuService::class));

            // Refresh order to get updated data
            $order->refresh();

            \Log::debug('Admin OrderController: Manual booking finished', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'booking_status' => $order->ekspedisiku_booking_status,
                'shipment_id' => $order->ekspedisiku_shipment_id,
                'tracking_number' => $order->tracking_number,
                'booking_reference' => $order->ekspedisiku_booking_reference,
                'booking_attempt' => $order->ekspedisiku_booking_attempt,
            ]);

            if ($order->ekspedisiku_booking_status === 'success' && ($order->tracking_number || $order->ekspedisiku_shipment_id)) {
                // Also trigger QAD sync automatically
                \App\Support\SalesOrderSyncDispatcher::dispatch($order);

                $ref = $order->tracking_number ?: $order->ekspedisiku_shipment_id;

                return back()->with('success', 'Booking berhasil! Nomor referensi / resi: ' . $ref . '. Sinkronisasi sales order dijadwalkan.');
            }

            return back()->with('error', 'Gagal membuat booking. Cek log `CreateShipmentBooking` / `EkspedisiKuService:createBooking` untuk detail error dari kurir.');
        } catch (\Exception $e) {
            \Log::error('Admin OrderController: Manual booking error', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetEkspedisikuBooking(Order $order)
    {
        if (! $order->expedition || ! in_array($order->expedition->code, ['lion_parcel', 'lalamove'], true)) {
            return back()->with('error', 'Ekspedisi tidak didukung untuk reset booking EkspedisiKu.');
        }

        \Log::warning('Admin OrderController: Reset booking requested', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'tracking_number' => $order->tracking_number,
            'shipment_id' => $order->ekspedisiku_shipment_id,
            'booking_reference' => $order->ekspedisiku_booking_reference,
            'booking_attempt' => $order->ekspedisiku_booking_attempt,
        ]);

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

        return back()->with('success', 'Booking Lion Parcel berhasil di-reset. Silakan buat booking ulang.');
    }

    public function requestPickup(Order $order)
    {
        if (!$order->ekspedisiku_shipment_id) {
            return back()->with('error', 'Pesanan belum memiliki shipment_id (EkspedisiKu). Buat booking dulu sampai sukses.');
        }

        $supportsPickup = $order->expedition && in_array($order->expedition->code, ['lion_parcel', 'jne', 'jnt', 'sicepat', 'sap', 'ninja', 'idexpress'], true);
        if (!$supportsPickup) {
            return back()->with('error', 'Ekspedisi ini tidak mendukung request pickup via EkspedisiKu.');
        }

        // Lion rejects pickup windows that are <= "now" on their side.
        // Use a small buffer so it's safely in the future.
        $startAt = now()->addMinutes(30)->toIso8601String();
        $endAt = now()->addMinutes(30)->addHours(4)->toIso8601String();

        \Log::info('Admin OrderController: Request pickup requested', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'shipment_id' => $order->ekspedisiku_shipment_id,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $result = $this->ekspedisiku->requestPickup([$order->ekspedisiku_shipment_id], $startAt, $endAt);

        if ($result && isset($result['success']) && $result['success']) {
            $order->update([
                'ekspedisiku_pickup_requested_at' => now(),
                'ekspedisiku_pickup_status' => 'success',
                'ekspedisiku_pickup_last_error' => null,
            ]);
            return back()->with('success', 'Request pickup berhasil dikirim!');
        }

        $error = $result['message'] ?? 'Gagal melakukan request pickup.';
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
        if (!$order->ekspedisiku_shipment_id) {
            return back()->with('error', 'Pesanan belum memiliki shipment_id (EkspedisiKu).');
        }

        if (!$order->expedition || $order->expedition->code !== 'lion_parcel') {
            return back()->with('error', 'Ekspedisi bukan Lion Parcel.');
        }

        \Log::info('Admin OrderController: Cancel pickup requested', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'shipment_id' => $order->ekspedisiku_shipment_id,
        ]);

        $result = $this->ekspedisiku->cancelPickup((string) $order->ekspedisiku_shipment_id);

        if ($result && isset($result['success']) && $result['success']) {
            $order->update([
                'ekspedisiku_pickup_requested_at' => now(),
                'ekspedisiku_pickup_status' => 'cancelled',
                'ekspedisiku_pickup_last_error' => null,
            ]);

            return back()->with('success', 'Cancel pickup berhasil dikirim!');
        }

        $error = $result['message'] ?? 'Gagal melakukan cancel pickup.';
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

    public function syncQad(Order $order)
    {
        try {
            // Dispatch the job to the background queue to avoid timeouts
            \App\Support\SalesOrderSyncDispatcher::dispatch($order);
            
            return back()->with('success', 'Permintaan sinkronisasi telah dikirim ke sistem (antrian). Silakan refresh halaman dalam beberapa saat untuk melihat hasilnya.');
        } catch (\Exception $e) {
            \Log::error('Admin OrderController: Sync QAD error', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memulai sinkronisasi: ' . $e->getMessage());
        }
    }

    protected function sendOrderTransitionNotifications(Order $order, $oldStatus, $oldPickupReady)
    {
        if (!$order->user) {
            return;
        }

        // Status processing
        if ($oldStatus !== 'processing' && $order->order_status === 'processing') {
            $order->user->notify(new OrderProcessingNotification($order));
        }

        // Pickup ready
        if ($order->pickup_ready_at && (!$oldPickupReady || $order->pickup_ready_at->toDateTimeString() !== $oldPickupReady->toDateTimeString())) {
            $order->user->notify(new OrderPickupReadyNotification($order));
        }

        // Status shipped
        if ($oldStatus !== 'shipped' && $order->order_status === 'shipped') {
            $order->user->notify(new OrderShippedNotification($order));
        }

        // Status completed
        if ($oldStatus !== 'completed' && $order->order_status === 'completed') {
            $order->user->notify(new OrderCompletedNotification($order));
        }
    }
}
