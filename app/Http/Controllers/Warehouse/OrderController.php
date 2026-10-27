<?php

namespace App\Http\Controllers\Warehouse;

use App\Exports\AdminOrdersExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendSalesOrderToWmsJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Notifications\Orders\OrderCompletedNotification;
use App\Notifications\Orders\OrderPickupReadyNotification;
use App\Notifications\Orders\OrderProcessingNotification;
use App\Notifications\Orders\OrderShippedNotification;
use App\Services\WmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
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

        if (!$order->isReleasedToHub()) {
            abort(403, 'Pesanan belum disetujui finance / belum siap diproses.');
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
            $query = Order::with(['user', 'address', 'expedition', 'items.product'])
                ->where('source_warehouse_id', $warehouse->id)
                ->where('finance_approved', true);

            // Filter by order status
            if ($request->filled('order_status') && $request->order_status != '') {
                $query->where('order_status', $request->order_status);
            }

            // Filter by date range
            if ($request->filled('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && $keyword = trim((string) $request->input('search.value'))) {
                        $q->where(function ($sub) use ($keyword) {
                            $sub->where('orders.order_number', 'like', "%{$keyword}%")
                                ->orWhere('orders.tracking_number', 'like', "%{$keyword}%")
                                ->orWhereHas('user', function ($uq) use ($keyword) {
                                    $uq->where('name', 'like', "%{$keyword}%")
                                       ->orWhere('email', 'like', "%{$keyword}%")
                                       ->orWhere('phone', 'like', "%{$keyword}%");
                                })
                                ->orWhereHas('address', function ($aq) use ($keyword) {
                                    $aq->where('store_name', 'like', "%{$keyword}%")
                                       ->orWhere('recipient_name', 'like', "%{$keyword}%")
                                       ->orWhere('label', 'like', "%{$keyword}%");
                                })
                                ->orWhereHas('expedition', function ($eq) use ($keyword) {
                                    $eq->where('name', 'like', "%{$keyword}%");
                                });
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('order_number_display', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($order->order_type === 'distributor') {
                        $html .= ' <span class="label label-warning" style="font-size: 10px;">DISTRIBUTOR</span>';
                    }
                    return $html;
                })
                ->addColumn('order_date', function ($order) {
                    return \App\Support\Wib::format($order->created_at, 'd/m/Y H:i');
                })
                ->addColumn('customer_info', function ($order) {
                    $html = '<strong>' . e($order->user->name) . '</strong>';

                    $storeName = trim((string) ($order->address?->store_name ?? ''));
                    $label = trim((string) ($order->address?->label ?? ''));

                    if ($storeName !== '' && $label !== '') {
                        $html .= '<br>' . e($storeName) . ' (' . e($label) . ')';
                    } elseif ($storeName !== '') {
                        $html .= '<br>' . e($storeName);
                    } elseif ($label !== '') {
                        $html .= '<br>(' . e($label) . ')';
                    }

                    $html .= '<br><small class="text-muted">' . e($order->user->email) . '</small>';
                    if ($order->user->phone) {
                        $html .= '<br><small class="text-muted">' . e($order->user->phone) . '</small>';
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
                    $printUrl = route('warehouse.orders.surat-jalan-pdf', $order);
                    $html = '<a href="' . $showUrl . '" class="btn btn-info btn-xs" title="Detail" style="margin-right: 4px;">
                        <i class="fa fa-eye"></i>
                    </a>';
                    $html .= '<a href="' . $printUrl . '" target="_blank" class="btn btn-primary btn-xs" title="Cetak Surat Jalan">
                        <i class="fa fa-print"></i>
                    </a>';
                    return $html;
                })
                ->rawColumns(['order_number_display', 'customer_info', 'order_type_badge', 'items_info', 'total_amount_formatted', 'order_status_badge', 'payment_status_badge', 'expedition_info', 'action'])
                ->make(true);
        }

        // Get statistics (Default to today's stats if no date filter is provided in the initial load)
        $today = now()->format('Y-m-d');
        
        $totalOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->where('finance_approved', true)
            ->whereDate('created_at', $today)
            ->count();
            
        $pendingOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->where('finance_approved', true)
            ->where('order_status', 'pending')
            ->whereDate('created_at', $today)
            ->count();
            
        $processingOrders = Order::where('source_warehouse_id', $warehouse->id)
            ->where('finance_approved', true)
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
     * Export warehouse orders to Excel with date and status filters.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;
        if (! $warehouse) {
            abort(403, 'Akses ditolak.');
        }

        $query = Order::query()
            ->select([
                'orders.id',
                'orders.order_number',
                'orders.qad_so_number',
                'orders.qid_sales_order_number',
                'orders.jubelio_salesorder_no',
                'orders.jubelio_salesorder_id',
                'orders.order_type',
                'orders.user_id',
                'orders.address_id',
                'orders.expedition_id',
                'orders.tracking_number',
                'orders.source_warehouse_id',
                'orders.total_amount',
                'orders.order_status',
                'orders.payment_method',
                'orders.payment_status',
                'orders.created_at',
            ])
            ->with(['user', 'address', 'expedition', 'sourceWarehouse'])
            ->where('orders.source_warehouse_id', $warehouse->id)
            ->where('orders.finance_approved', true)
            ->orderByDesc('created_at');

        if ($request->filled('date_from') && $request->date_from != '') {
            $query->whereDate('orders.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to') && $request->date_to != '') {
            $query->whereDate('orders.created_at', '<=', $request->date_to);
        }

        if ($request->filled('status') && $request->status != '') {
            $query->where('orders.order_status', $request->status);
        }

        $hubIdentifier = $warehouse->kode_hub ?: Str::slug($warehouse->name);
        $filenameParts = ['laporan-pesanan-hub', $hubIdentifier];
        if ($request->filled('status') && $request->status != '') {
            $filenameParts[] = $request->status;
        }
        if ($request->filled('date_from') && $request->date_from != '') {
            $filenameParts[] = 'dari-' . $request->date_from;
        }
        if ($request->filled('date_to') && $request->date_to != '') {
            $filenameParts[] = 'sd-' . $request->date_to;
        }
        $filenameParts[] = now()->timezone('Asia/Jakarta')->format('Ymd-His');
        $filename = implode('-', $filenameParts) . '.xlsx';

        return Excel::download(new AdminOrdersExport($query), $filename);
    }

    /**
     * Show order detail.
     */
    public function show(Order $order)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        $this->authorizeWarehouseOrder($order);

        $order->load(['user.categoryDiscounts', 'items.product.brand', 'items.product.category', 'expedition', 'address', 'sourceWarehouse', 'financeApprover']);

        // Refresh AR balance and credit limit if distributor order
        if ($order->order_type === 'distributor' && $order->user && $order->user->qad_customer_code) {
            $webhookUrl = config('services.n8n.cis_ar_balance_url') ?: env('N8N_CIS_AR_BALANCE_URL');
            if ($webhookUrl) {
                try {
                    $response = Http::timeout(3)->get($webhookUrl, [
                        'debtorcode' => $order->user->qad_customer_code,
                    ]);
                    if ($response->successful() && $response->json('success')) {
                        $data = $response->json('data');
                        $isUpdated = false;
                        if (isset($data['outstanding'])) {
                            $order->user->ar_outstanding = $data['outstanding'];
                            $isUpdated = true;
                        }
                        if (isset($data['credit_limit'])) {
                            $order->user->credit_limit = $data['credit_limit'];
                            $isUpdated = true;
                        }
                        if ($isUpdated) {
                            $order->user->ar_balance_last_sync_at = now();
                            $order->user->save();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed fetching AR balance on warehouse order show', [
                        'order_id' => $order->id,
                        'debtorcode' => $order->user->qad_customer_code,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $expeditions = \App\Models\Expedition::where('is_active', true)->get();

        return view('warehouse.orders.show', compact('warehouse', 'order', 'expeditions'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
        ]);

        $updateData = ['order_status' => $request->order_status];

        if ($request->order_status === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        }

        if (in_array($request->order_status, ['delivered', 'completed']) && !$order->received_at) {
            $updateData['received_at'] = now();
        }

        if ($request->order_status === 'completed') {
            $order->creditPoints();
        }

        $oldStatus = $order->order_status;
        $oldPickupReady = $order->pickup_ready_at;
        $oldShippedAt = $order->shipped_at;

        $order->update($updateData);

        $this->sendOrderTransitionNotifications($order, $oldStatus, $oldPickupReady, $oldShippedAt);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updateTracking(Request $request, Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        $updateData = [
            'tracking_number' => $request->tracking_number,
        ];

        if (in_array($order->order_status, ['pending', 'processing'])) {
            $updateData['order_status'] = 'shipped';
            $updateData['shipped_at'] = now();
        }

        $order->update($updateData);
        
        \App\Jobs\SendWhatsAppNotification::dispatch($order, 'tracking');

        return back()->with('success', 'Nomor resi berhasil disimpan.');
    }

    public function approveFinance(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if ($order->payment_method !== 'term_of_payment') {
            return back()->with('error', 'Pesanan ini bukan Term of Payment.');
        }

        $result = $order->approveFinanceBy(auth()->user());

        if (!$result['success']) {
            return back()->with('info', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function notifyEdms(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        try {
            \App\Jobs\NotifyExternalFinanceApprovalNeeded::dispatchSync($order, force: true);
            $order->refresh();

            if ($order->edms_notification_status === 'SUCCESS') {
                return back()->with('success', 'Notifikasi approval finance berhasil dikirim ke EDMS.');
            } else {
                $err = $order->edms_notification_error ?? 'Pengiriman ke EDMS belum berhasil.';
                return back()->with('error', 'Gagal mengirim notifikasi ke EDMS: ' . $err);
            }
        } catch (\Throwable $e) {
            Log::error('Warehouse OrderController Manual EDMS Notification Exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengirim ke EDMS: ' . $e->getMessage());
        }
    }

    /**
     * Update order status, tracking number, and payment status in one request
     */
    public function update(Request $request, Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $request->validate([
            'order_status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded,term_of_payment',
            'pickup_ready_at' => 'nullable|date',
            'shipped_at' => 'nullable|date',
            'pickup_note' => 'nullable|string|max:1000',
            'expedition_id' => 'nullable|exists:expeditions,id',
        ]);

        $oldStatus = $order->order_status;
        $oldPickupReady = $order->pickup_ready_at;
        $oldShippedAt = $order->shipped_at;

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
            
            if ($request->order_status === 'shipped' && !$order->shipped_at && !$request->has('shipped_at')) {
                $updateData['shipped_at'] = now();
            }

            if (in_array($request->order_status, ['delivered', 'completed']) && !$order->received_at) {
                $updateData['received_at'] = now();
            }

            if ($request->order_status === 'completed') {
                $order->creditPoints();
            }
            
            $messages[] = 'Status pesanan';
        }

        // Update tracking number
        if ($request->filled('tracking_number') && trim($request->tracking_number) !== '') {
            $updateData['tracking_number'] = trim($request->tracking_number);
            
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

            if ($request->payment_status === 'paid' && !$order->paid_at) {
                $updateData['paid_at'] = now();
                $order->creditPoints();
            }

            if ($request->payment_status === 'term_of_payment' && $order->payment_method === 'term_of_payment' && !$order->finance_approved) {
                $updateData['finance_approved'] = true;
                $updateData['finance_approved_at'] = now();
                $updateData['finance_approved_by'] = auth()->id();
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
            
            $this->sendOrderTransitionNotifications($order, $oldStatus, $oldPickupReady, $oldShippedAt);

            if (isset($updateData['tracking_number']) && $updateData['tracking_number'] !== null) {
                if ($order->wasChanged('tracking_number')) {
                    \App\Jobs\SendWhatsAppNotification::dispatch($order, 'tracking');
                }
            }

            if (isset($updateData['pickup_ready_at']) && $updateData['pickup_ready_at'] !== null) {
                if ($order->wasChanged('pickup_ready_at')) {
                    \App\Jobs\SendWhatsAppNotification::dispatch($order, 'pickup_ready');
                }
            }

            if (isset($updateData['shipped_at']) && $updateData['shipped_at'] !== null) {
                if ($order->wasChanged('shipped_at')) {
                    \App\Jobs\SendWhatsAppNotification::dispatch($order, 'pickup_handover');
                }
            }

            $message = 'Berhasil memperbarui: ' . implode(', ', $messages);

            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'thank_you');
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_notification');

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
        $this->authorizeWarehouseOrder($order);

        if (!$order->tracking_number || !$order->expedition) {
            return response()->json(['success' => false, 'message' => 'Resi atau ekspedisi belum tersedia'], 400);
        }

        $code = strtolower($order->expedition->code);

        $result = $this->ekspedisiku->track($order->tracking_number, $code);
        
        if ($result && isset($result['success']) && $result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? $result,
            ]);
        }
        
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

    /**
     * Antrekan sinkronisasi pesanan ke QID (QAD).
     */
    /**
     * Debug JSON: endpoint + payload + response create SO ke QID (dry run default).
     */
    public function debugQidSalesOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorizeWarehouseOrder($order);

        if (! \App\Support\QadIntegration::isConfigured()) {
            return response()->json([
                'ok' => false,
                'error' => 'QID API belum dikonfigurasi (QIDAPI_* di .env).',
            ], 422);
        }

        $qad = app(\App\Services\QadService::class);
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
            \App\Support\SalesOrderSyncDispatcher::dispatch($order);

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

        if (! $order->expedition || ! in_array($order->expedition->code, ['lion_parcel', 'lalamove', 'jne', 'jnt', 'sicepat', 'sap', 'ninja', 'idexpress', 'borzo'], true)) {
            return back()->with('error', 'Ekspedisi tidak didukung untuk booking EkspedisiKu.');
        }

        try {
            Log::info('Warehouse OrderController: Manual booking requested', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'carrier' => $order->expedition->code,
            ]);

            $job = new \App\Jobs\CreateShipmentBooking($order);
            $job->handle(app(\App\Services\EkspedisiKuService::class));

            $order->refresh();

            if ($order->ekspedisiku_booking_status === 'success' && ($order->tracking_number || $order->ekspedisiku_shipment_id)) {
                \App\Support\SalesOrderSyncDispatcher::dispatch($order);
                $ref = $order->tracking_number ?: $order->ekspedisiku_shipment_id;

                return back()->with('success', 'Booking berhasil! Referensi / resi: ' . $ref . '. Sinkronisasi sales order dijadwalkan.');
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

        if (! $order->expedition || ! in_array($order->expedition->code, ['lion_parcel', 'lalamove', 'sicepat'], true)) {
            return back()->with('error', 'Ekspedisi tidak didukung untuk reset booking EkspedisiKu.');
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
            return back()->with('error', 'Belum ada shipment_id. Buat booking Ekspedisiku terlebih dahulu.');
        }

        $supportsPickup = $order->expedition && in_array($order->expedition->code, ['lion_parcel', 'jne', 'jnt', 'sicepat', 'sap', 'ninja', 'idexpress'], true);
        if (!$supportsPickup) {
            return back()->with('error', 'Ekspedisi ini tidak mendukung request pickup via EkspedisiKu.');
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

    /**
     * Generate PDF for Surat Jalan.
     */
    public function suratJalanPdf(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $order->load(['user', 'items.product.brand', 'items.product.category', 'address', 'sourceWarehouse', 'expedition']);
        
        $warehouse = auth()->user()->warehouse;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('buyer.distributor.manage-orders.surat-jalan-pdf', compact('warehouse', 'order'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Jalan_' . $order->order_number . '.pdf');
    }

    public function downloadInvoice(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $order->load(\App\Support\ProformaInvoice::relations());
        $invoice = \App\Support\ProformaInvoice::fromOrder($order);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('buyer.orders.invoice', compact('order', 'invoice'));
        $pdf->setPaper('a4', 'portrait');

        $filename = ($order->isPaidOrTopApproved() ? 'invoice-' : 'proforma-invoice-') . $order->order_number . '.pdf';

        return $pdf->download($filename);
    }

    public function checkQadStatus(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        try {
            $qadService = app(\App\Services\QadService::class);
            $soNumber = $order->qid_sales_order_number ?? $order->order_number;
            $response = $qadService->getSalesOrder($soNumber);

            $status = 'failed';
            $isFound = false;

            if (is_array($response) && !($response['error']['isError'] ?? false)) {
                if (isset($response['data']) && !empty($response['data'])) {
                    $status = 'success';
                    $isFound = true;
                }
            }

            $history = is_array($order->qad_sync_history) ? $order->qad_sync_history : [];
            $history[] = [
                'timestamp' => now()->toIso8601String(),
                'attempt' => 'manual_check',
                'status' => $status,
                'response' => $isFound ? ['message' => 'Sales Order found in QAD', 'data' => $response['data']] : $response,
            ];
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }

            $order->qad_sync_history = $history;
            
            if ($isFound) {
                $qadSoNumber = $response['data']['salesOrderNumber'] ?? $response['data']['salesOrderCode'] ?? null;
                if ($qadSoNumber) {
                    $order->qad_so_number = $qadSoNumber;
                }
            }

            $order->save();

            return back()->with('success', 'Status QAD berhasil dicek dan riwayat diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengecek status QAD: ' . $e->getMessage());
        }
    }

    public function syncJubelio(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        try {
            \App\Jobs\SyncOrderToJubelio::dispatchSync($order->fresh());
            $order->refresh();

            if ($order->jubelio_salesorder_id) {
                $msg = 'Sales order berhasil dikirim ke Jubelio (No. SO: ' . ($order->jubelio_salesorder_no ?: ('ID ' . $order->jubelio_salesorder_id)) . ')';
                if ($order->zoho_salesorder_id) {
                    $msg .= ' dan langsung dikirim ke Zoho (SO ID: ' . $order->zoho_salesorder_id . ')';
                }
                $msg .= '.';

                return back()->with('success', $msg);
            }

            return back()->with('error', 'Sinkronisasi Jubelio selesai tetapi belum menghasilkan ID/No. SO. Silakan cek log Jubelio.');
        } catch (\Throwable $e) {
            Log::error('Warehouse OrderController: Sync Jubelio error', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi Jubelio: ' . $e->getMessage());
        }
    }

    public function syncZoho(Order $order, \App\Services\ZohoService $zohoService)
    {
        $this->authorizeWarehouseOrder($order);

        if (!\App\Services\ZohoService::isConfigured()) {
            return back()->with('error', 'Konfigurasi Zoho Books belum lengkap di server (.env).');
        }

        try {
            $zohoService->createOrderToPaymentFlow($order);
            $order->refresh();

            $msg = 'Pesanan ' . $order->order_number . ' berhasil dikirim ke Zoho Books.';
            $details = [];
            if ($order->zoho_salesorder_id) {
                $details[] = 'SO: ' . $order->zoho_salesorder_id;
            }
            if ($order->zoho_invoice_id) {
                $details[] = 'Inv: ' . $order->zoho_invoice_id;
            }
            if ($order->zoho_payment_id) {
                $details[] = 'Payment: ' . $order->zoho_payment_id;
            }
            if (!empty($details)) {
                $msg .= ' (' . implode(', ', $details) . ')';
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Warehouse OrderController: Sync Zoho error', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi Zoho: ' . $e->getMessage());
        }
    }

    public function checkWmsStatus(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        $apiUrl = config('services.wms.api_url');
        $apiKey = config('services.wms.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            return back()->with('error', 'WMS API is not configured.');
        }

        try {
            $url = rtrim($apiUrl, '/') . '/sales-orders/' . $order->order_number . '/status';
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $responseData = $response->json();
                $status = $responseData['data']['status'] ?? 'QUEUED';
                
                $order->wms_so_status = $status;
                $order->wms_so_failure_reason = $responseData['data']['failure_reason'] ?? null;
                $order->save();

                return back()->with('success', 'Status WMS berhasil diperbarui menjadi: ' . $status);
            } else {
                return back()->with('error', 'Gagal mengecek status WMS: ' . $response->body());
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengecek status WMS: ' . $e->getMessage());
        }
    }

    public function syncWms(Order $order)
    {
        $this->authorizeWarehouseOrder($order);

        if (empty(config('services.wms.api_url')) || empty(config('services.wms.api_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'WMS API belum dikonfigurasi.',
            ], 400);
        }

        try {
            SendSalesOrderToWmsJob::dispatchSync($order);
            $order->refresh();

            $failed = strtoupper((string) $order->wms_so_status) === 'FAILED';

            return response()->json([
                'success' => ! $failed,
                'message' => $failed
                    ? ('Gagal kirim ke WMS: '.($order->wms_so_failure_reason ?: 'lihat log'))
                    : ('Sales order terkirim ke WMS. Status: '.($order->wms_so_status ?: 'OK')),
                'status' => $order->wms_so_status,
                'failure_reason' => $order->wms_so_failure_reason,
            ], $failed ? 422 : 200);
        } catch (\Throwable $e) {
            $order->refresh();

            return response()->json([
                'success' => false,
                'message' => 'Gagal kirim ke WMS: '.$e->getMessage(),
                'status' => $order->wms_so_status,
                'failure_reason' => $order->wms_so_failure_reason,
            ], 500);
        }
    }

    public function getItemBatches(Order $order, OrderItem $item, WmsService $wms)
    {
        $this->authorizeWarehouseOrder($order);

        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak sesuai dengan pesanan.',
            ], 400);
        }

        $order->loadMissing(['sourceWarehouse', 'user']);
        $item->loadMissing('product');

        $warehouse = $order->sourceWarehouse ?? ($order->source_qad_location_code ? Warehouse::where('qad_location_code', $order->source_qad_location_code)->first() : null);

        if (! $warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Gudang asal (source warehouse) tidak ditemukan pada pesanan ini.',
            ], 422);
        }

        $product = $item->product;
        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan pada item pesanan ini.',
            ], 422);
        }

        $productCodes = $product->getAllCodes();
        $availableBatches = $wms->batchesForWarehouseItem($warehouse, $productCodes, 0);

        return response()->json([
            'success' => true,
            'warehouse' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'location_code' => $warehouse->qad_location_code ?: $order->source_qad_location_code,
            ],
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->display_name ?? $product->name,
                'unit' => $product->unit ?: 'PCS',
            ],
            'item_id' => $item->id,
            'required_qty' => (int) $item->quantity,
            'current_batches' => is_array($item->allocated_batches) ? array_values($item->allocated_batches) : [],
            'available_batches' => array_values($availableBatches),
        ]);
    }

    public function updateItemBatches(Request $request, Order $order, OrderItem $item)
    {
        $this->authorizeWarehouseOrder($order);

        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak sesuai dengan pesanan.',
            ], 400);
        }

        $validated = $request->validate([
            'batches' => ['present', 'array'],
            'batches.*.lot_serial' => ['required', 'string', 'max:100'],
            'batches.*.qty' => ['required', 'numeric', 'min:0.0001'],
            'batches.*.expired' => ['nullable', 'string', 'max:50'],
            'batches.*.item_code' => ['nullable', 'string', 'max:100'],
        ]);

        $item->loadMissing('product');
        $requiredQty = (float) $item->quantity;
        $defaultCode = $item->product?->code ?? '';
        $incomingBatches = [];
        $totalQty = 0;

        foreach ($validated['batches'] as $b) {
            $qty = (float) ($b['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $totalQty += $qty;
            $incomingBatches[] = [
                'lot_serial' => trim((string) $b['lot_serial']),
                'qty' => $qty,
                'expired' => !empty($b['expired']) ? trim((string) $b['expired']) : null,
                'item_code' => !empty($b['item_code']) ? trim((string) $b['item_code']) : $defaultCode,
            ];
        }

        if (count($incomingBatches) > 0 && abs($totalQty - $requiredQty) > 0.0001) {
            return response()->json([
                'success' => false,
                'message' => 'Total kuantitas batch (' . $totalQty . ') harus sama dengan jumlah yang dipesan (' . $requiredQty . ').',
            ], 422);
        }

        $item->allocated_batches = $incomingBatches;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Batch berhasil diperbarui.',
            'batches' => $incomingBatches,
        ]);
    }

    protected function sendOrderTransitionNotifications(Order $order, $oldStatus, $oldPickupReady, $oldShippedAt = null)
    {
        if (!$order->user) {
            return;
        }

        if ($oldStatus !== 'processing' && $order->order_status === 'processing') {
            $order->user->notify(new OrderProcessingNotification($order));
        }

        if ($order->pickup_ready_at && (!$oldPickupReady || $order->pickup_ready_at->toDateTimeString() !== $oldPickupReady->toDateTimeString())) {
            $order->user->notify(new OrderPickupReadyNotification($order));
        }

        if (($oldStatus !== 'shipped' && $order->order_status === 'shipped') || 
            ($order->shipped_at && (!$oldShippedAt || $order->shipped_at->toDateTimeString() !== $oldShippedAt->toDateTimeString()))) {
            $order->user->notify(new OrderShippedNotification($order));
        }

        if ($oldStatus !== 'completed' && $order->order_status === 'completed') {
            $order->user->notify(new OrderCompletedNotification($order));
        }
    }
}


