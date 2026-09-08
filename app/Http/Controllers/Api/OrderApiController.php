<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\FaspayService;
use App\Support\QadWsOrderNumberGenerator;
use App\Support\ShopFulfillment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OrderApiController extends Controller
{
    /**
     * Get user ID from request or auth
     */
    private function getUserId(Request $request): ?string
    {
        if (Auth::check()) {
            return Auth::id();
        }
        
        return $request->input('user_id') ?? $request->query('user_id');
    }

    // Shipping rates per province (in Rupiah per kg)
    private $shippingRates = [
        '11' => 15000, '12' => 15000, '13' => 18000, '14' => 20000, '15' => 22000,
        '16' => 25000, '17' => 25000, '18' => 28000, '19' => 30000, '21' => 32000,
        '31' => 10000, '32' => 12000, '33' => 12000, '34' => 10000, '35' => 12000,
        '36' => 15000, '51' => 20000, '52' => 25000, '53' => 30000,
        '61' => 35000, '62' => 35000, '63' => 35000, '64' => 35000, '65' => 35000,
        '71' => 40000, '72' => 40000, '73' => 40000, '74' => 40000, '75' => 40000,
        '76' => 40000, '81' => 50000, '82' => 50000, '91' => 55000, '94' => 55000,
    ];

    /**
     * Get list of expeditions
     * 
     * @return JsonResponse
     */
    public function getExpeditions(): JsonResponse
    {
        return Cache::remember('api_expeditions', 86400, function () {
            $expeditions = Expedition::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'logo', 'description', 'base_cost', 'est_days_min', 'est_days_max']);

            return response()->json([
                'success' => true,
                'data' => $expeditions,
            ]);
        });
    }

    /**
     * Get expedition services with shipping cost calculation
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getExpeditionServices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
            'expedition_id' => 'required|exists:expeditions,id',
            'address_id' => 'required|exists:addresses,id',
        ]);

        $userId = $this->getUserId($request) ?? $validated['user_id'];
        $expedition = Expedition::findOrFail($validated['expedition_id']);
        $address = Address::where('user_id', $userId)
            ->where('id', $validated['address_id'])
            ->firstOrFail();

        $carts = Cart::with('product')
            ->where('user_id', $userId)
            ->where('cart_type', 'regular')
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.',
            ], 400);
        }

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $services = [];
        foreach ($expedition->services as $service) {
            $cost = $this->calculateShippingCost(
                $address->province_id,
                $totalWeight,
                $expedition->base_cost,
                $service['multiplier']
            );

            $estDaysMin = max(1, $expedition->est_days_min + $service['days_add']);
            $estDaysMax = max(1, $expedition->est_days_max + $service['days_add']);

            $services[] = [
                'code' => $service['code'],
                'name' => $service['name'],
                'cost' => $cost,
                'cost_formatted' => 'Rp ' . number_format($cost, 0, ',', '.'),
                'estimated_days' => $estDaysMin === $estDaysMax
                    ? $estDaysMin . ' hari'
                    : $estDaysMin . '-' . $estDaysMax . ' hari',
            ];
        }

        return response()->json([
            'success' => true,
            'expedition' => [
                'id' => $expedition->id,
                'code' => $expedition->code,
                'name' => $expedition->name,
            ],
            'services' => $services,
            'total_weight' => $totalWeight,
            'total_weight_formatted' => number_format($totalWeight / 1000, 1) . ' kg',
        ]);
    }

    /**
     * Create order with Xendit payment integration
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
            'address_id' => 'required|exists:addresses,id',
            'expedition_id' => 'required|exists:expeditions,id',
            'expedition_service' => 'required|string',
            'payment_method' => 'required|in:faspay,manual_transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = $this->getUserId($request) ?? $validated['user_id'];

        // Verify address belongs to user
        $address = Address::where('user_id', $userId)
            ->where('id', $validated['address_id'])
            ->with(['wilayah'])
            ->firstOrFail();

        $expedition = Expedition::findOrFail($validated['expedition_id']);

        $carts = Cart::with(['product', 'warehouse'])
            ->where('user_id', $userId)
            ->where('cart_type', 'regular')
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.',
            ], 400);
        }

        // Get source warehouse from cart
        $sourceWarehouse = $carts->first()->warehouse;

        if (!$sourceWarehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Hub pengirim tidak ditemukan.',
            ], 400);
        }

        // Verify all items are from same warehouse
        $differentWarehouse = $carts->first(function ($cart) use ($sourceWarehouse) {
            return $cart->warehouse_id !== $sourceWarehouse->id;
        });

        if ($differentWarehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang memiliki produk dari hub yang berbeda.',
            ], 400);
        }

        // Check stock availability
        if (! ShopFulfillment::assumeStockReady()) {
            $stockErrors = [];
            foreach ($carts as $cart) {
                $stock = WarehouseStock::where('warehouse_id', $sourceWarehouse->id)
                    ->where('product_id', $cart->product_id)
                    ->first();

                $availableStock = $stock ? $stock->stock : 0;

                if ($cart->quantity > $availableStock) {
                    $stockErrors[] = "{$cart->product->display_name}: dipesan {$cart->quantity}, tersedia {$availableStock}";
                }
            }

            if (! empty($stockErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock tidak mencukupi di hub ' . $sourceWarehouse->name . ":\n" . implode("\n", $stockErrors),
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();
            
            $discountService = new \App\Services\DiscountService();
            $user = Auth::user();

            $retailSubtotal = (float) $carts->sum(function ($cart) use ($user) {
                return $user->getProductPrice($cart->product) * (int) $cart->quantity;
            });

            $discountData = $discountService->calculateCartDiscount($carts, $user);
            $tieredDiscountAmount = $discountData['total_discount_amount'];
            $subtotal = $retailSubtotal - $tieredDiscountAmount;

            $totalWeight = $carts->sum(function ($cart) {
                return ($cart->product->weight ?? 500) * $cart->quantity;
            });

            // Find service multiplier
            $serviceMultiplier = 1.0;
            foreach ($expedition->services as $service) {
                if ($service['code'] === $validated['expedition_service']) {
                    $serviceMultiplier = $service['multiplier'];
                    break;
                }
            }

            $shippingCost = $this->calculateShippingCost(
                $address->province_id,
                $totalWeight,
                $expedition->base_cost,
                $serviceMultiplier
            );
            $total = $subtotal + $shippingCost;

            // Build full shipping address string for record
            $shippingAddressText = $address->recipient_name . "\n" .
                $address->phone . "\n" .
                $address->address_detail . "\n" .
                ($address->district_name ? 'Kec. ' . $address->district_name . ', ' : '') .
                ($address->regency_name ? $address->regency_name . ', ' : '') .
                ($address->province_name ?? '') .
                ($address->postal_code ? ' ' . $address->postal_code : '');

            // Calculate points for DRiiPPreneur
            $pointsEarned = 0;
            if ($user->isDriippreneurApproved()) {
                $pointRate = \App\Models\Setting::get('driippreneur_point_rate', 1000);
                $totalItems = $carts->sum('quantity');
                $pointsEarned = (int)$pointRate * $totalItems;
            }


            $order = Order::create([
                'order_type' => Order::TYPE_REGULAR,
                'order_number' => $orderNumber,
                'qid_sales_order_number' => $orderNumber,
                'user_id' => $userId,
                'address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'expedition_service' => $validated['expedition_service'],
                'source_warehouse_id' => $sourceWarehouse->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'shipping_address' => $shippingAddressText,
                'payment_method' => $validated['payment_method'],
                'company' => $user->getFaspayCompany(),
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'points_earned' => $pointsEarned,
                'points_credited' => false,
            ]);

            foreach ($carts as $cart) {
                $price = $user->getProductPrice($cart->product);
                $itemSubtotal = $price * $cart->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'order_uom' => $cart->order_uom,
                    'quantity_ordered' => $cart->quantity_ordered,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ]);

                if (! ShopFulfillment::assumeStockReady()) {
                    $stock = WarehouseStock::where('warehouse_id', $sourceWarehouse->id)
                        ->where('product_id', $cart->product_id)
                        ->first();

                    if ($stock) {
                        $stock->decrement('stock', $cart->quantity);
                    }
                }
            }

            Cart::where('user_id', $userId)->where('cart_type', 'regular')->delete();

            // Handle Faspay payment first (to get invoice URL)
            $faspayInvoiceUrl = null;
            $faspayBillNo = null;

            if ($validated['payment_method'] === 'faspay') {
                $faspayService = new \App\Services\FaspayService($order->company);
                
                $invoice = $faspayService->createBill($order, $user);

                if ($invoice && isset($invoice['redirect_url'])) {
                    $faspayBillNo = $invoice['bill_no'] ?? $order->order_number;
                    $faspayInvoiceUrl = $invoice['redirect_url'];
                    
                    // Update order with invoice information
                    $order->faspay_bill_no = $faspayBillNo;
                    $order->faspay_redirect_url = $faspayInvoiceUrl;
                    $order->save();
                    
                    // Log for debugging
                    Log::info('Faspay invoice saved to order', [
                        'order_id' => $order->id,
                        'bill_no' => $order->faspay_bill_no,
                        'redirect_url' => $order->faspay_redirect_url,
                        'virtual_account_no' => $order->virtual_account_no,
                    ]);
                } else {
                    DB::rollBack();
                    Log::error('Failed to create Faspay invoice', ['order_id' => $order->id]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal membuat invoice pembayaran. Silakan coba lagi.'
                    ], 500);
                }
            }

            DB::commit();

            // Load relationships for notification (after commit to ensure data is saved)
            if (!$order->relationLoaded('address') || !$order->relationLoaded('items') || !$order->relationLoaded('expedition')) {
                $order->refresh();
                $order->load([
                    'user',
                    'address.village',
                    'address.district',
                    'address.regency',
                    'address.province',
                    'items.product',
                    'expedition',
                    'sourceWarehouse',
                ]);
            } else {
                // Ensure address relationships are loaded
                if ($order->address && (! $order->address->relationLoaded('village') || ! $order->address->relationLoaded('district') || ! $order->address->relationLoaded('regency') || ! $order->address->relationLoaded('wilayah'))) {
                    $order->address->load(['village', 'district', 'regency', 'wilayah']);
                }
                $order->loadMissing(['user', 'sourceWarehouse']);
            }

            // Dispatch background job to send payment notification
            \App\Jobs\SendWhatsAppNotification::dispatch($order, 'payment');

            // TOP: notifikasi hub dikirim setelah finance approve; non-TOP pakai warehouse_new_order setelah bayar
            if ($order->payment_method !== 'term_of_payment') {
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_new_order');
            }
            
            // Note: Thank you notification will be sent after payment is successful via webhook

            $order->load(['items.product', 'address', 'expedition', 'sourceWarehouse']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => (float) $order->subtotal,
                    'shipping_cost' => (float) $order->shipping_cost,
                    'total_amount' => (float) $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'faspay_bill_no' => $faspayBillNo,
                    'faspay_invoice_url' => $faspayInvoiceUrl ?? null,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->display_name,
                                'code' => $item->product->code,
                            ],
                            'quantity' => $item->quantity,
                            'price' => (float) $item->price,
                            'subtotal' => (float) $item->subtotal,
                        ];
                    }),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of orders with filters (Admin / User)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        
        $query = Order::with([
            'items.product.brand', 
            'items.product.category', 
            'address.village', 
            'address.district', 
            'address.regency', 
            'address.province', 
            'expedition', 
            'sourceWarehouse', 
            'user'
        ]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        $data = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_amount' => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'faspay_redirect_url' => $order->faspay_redirect_url,
                'notes' => $order->notes,
                'points_earned' => $order->points_earned,
                'created_at' => $order->created_at->toISOString(),
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone,
                ] : null,
                'address' => $order->address ? [
                    'id' => $order->address->id,
                    'label' => $order->address->label,
                    'recipient_name' => $order->address->recipient_name,
                    'phone' => $order->address->phone,
                    'full_address' => $order->address->full_address,
                    'village' => $order->address->village->name ?? null,
                    'district' => $order->address->district->name ?? null,
                    'regency' => $order->address->regency->name ?? null,
                    'province' => $order->address->province->name ?? null,
                    'postal_code' => $order->address->postal_code,
                ] : null,
                'expedition' => $order->expedition ? [
                    'id' => $order->expedition->id,
                    'name' => $order->expedition->name,
                    'service' => $order->expedition_service,
                ] : null,
                'warehouse' => $order->sourceWarehouse ? [
                    'id' => $order->sourceWarehouse->id,
                    'name' => $order->sourceWarehouse->name,
                ] : null,
                'items' => $order->items->map(function ($item) {
                    $imageUrl = $item->product->image_url ?? null;
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'code' => $item->product->code,
                            'name' => $item->product->name,
                            'price' => (float) $item->product->price,
                            'image' => $imageUrl,
                            'brand' => $item->product->brand->name ?? null,
                            'category' => $item->product->category->name ?? null,
                        ],
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Get order details
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $userId = $this->getUserId($request);
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'user_id diperlukan. Kirim sebagai query parameter atau body, atau login terlebih dahulu.',
            ], 400);
        }
        
        $order = Order::with(['items.product.brand', 'items.product.category', 'address', 'expedition', 'sourceWarehouse'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_amount' => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'faspay_redirect_url' => $order->faspay_redirect_url,
                'notes' => $order->notes,
                'points_earned' => $order->points_earned,
                'created_at' => $order->created_at->toISOString(),
                'address' => $order->address ? [
                    'id' => $order->address->id,
                    'label' => $order->address->label,
                    'recipient_name' => $order->address->recipient_name,
                    'phone' => $order->address->phone,
                    'full_address' => $order->address->full_address,
                ] : null,
                'expedition' => $order->expedition ? [
                    'id' => $order->expedition->id,
                    'name' => $order->expedition->name,
                    'service' => $order->expedition_service,
                ] : null,
                'warehouse' => $order->sourceWarehouse ? [
                    'id' => $order->sourceWarehouse->id,
                    'name' => $order->sourceWarehouse->name,
                ] : null,
                'items' => $order->items->map(function ($item) {
                    // Build full image URL using Product accessor
                    $imageUrl = $item->product->image_url;

                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'code' => $item->product->code,
                            'name' => $item->product->name,
                            'price' => (float) $item->product->price,
                            'image' => $imageUrl,
                        ],
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update finance approval status.
     *
     * POST /api/orders/finance-approval
     * Body: user_id, order_id (atau transaksi_id)
     */
    public function updateFinanceApproval(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|string|exists:users,id',
            'order_id' => 'required_without:transaksi_id|nullable|string',
            'transaksi_id' => 'required_without:order_id|nullable|string',
            'finance_approved' => 'nullable|in:0,1,true,false',
        ], [
            'user_id.required' => 'user_id wajib diisi',
            'user_id.exists' => 'user_id tidak ditemukan',
            'order_id.required_without' => 'order_id atau transaksi_id wajib diisi',
            'transaksi_id.required_without' => 'order_id atau transaksi_id wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = \App\Models\User::find($request->user_id);
        if (!$user || !in_array($user->role, [\App\Models\User::ROLE_FINANCE, \App\Models\User::ROLE_SUPER_ADMIN], true)) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki akses finance approval.',
            ], 403);
        }

        $orderKey = $request->input('order_id') ?: $request->input('transaksi_id');
        $order = Order::where('id', $orderKey)
            ->orWhere('order_number', $orderKey)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Default: approve (1). Bisa kirim finance_approved=0 untuk revoke.
        $approve = true;
        if ($request->has('finance_approved')) {
            $approve = filter_var($request->input('finance_approved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($approve === null) {
                $approve = in_array((string) $request->input('finance_approved'), ['1', 'true'], true);
            }
        }

        if ($approve) {
            $result = $order->approveFinanceBy($user);

            $order->refresh();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'finance_approved' => (int) $order->finance_approved,
                    'finance_approved_at' => $order->finance_approved_at?->toDateTimeString(),
                    'finance_approved_by' => $order->finance_approved_by,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                ],
            ], $result['success'] ? 200 : 409);
        }

        // Revoke approval (set ke 0) — hanya jika belum diproses lebih lanjut
        if (in_array($order->order_status, ['shipped', 'delivered', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan approval: pesanan sudah diproses lebih lanjut.',
            ], 422);
        }

        $order->update([
            'finance_approved' => false,
            'finance_approved_at' => null,
            'finance_approved_by' => null,
            'payment_status' => $order->payment_method === 'term_of_payment' ? 'pending' : $order->payment_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Finance approval dibatalkan (0).',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'finance_approved' => 0,
                'finance_approved_at' => null,
                'finance_approved_by' => null,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
            ],
        ]);
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShippingCost($provinceId, $totalWeight, $expeditionMultiplier = 1.0, $serviceMultiplier = 1.0)
    {
        $baseRate = $this->shippingRates[$provinceId] ?? 35000;
        $perKgCost = 5000;
        $weightInKg = $totalWeight / 1000;
        $additionalKg = max(0, ceil($weightInKg) - 1);
        $baseCost = $baseRate + ($additionalKg * $perKgCost);
        return round($baseCost * $expeditionMultiplier * $serviceMultiplier);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        return QadWsOrderNumberGenerator::generate();
    }
}

