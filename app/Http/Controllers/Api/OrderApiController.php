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
use App\Services\XenditService;
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
            'payment_method' => 'required|in:xendit,manual_transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = $this->getUserId($request) ?? $validated['user_id'];

        // Verify address belongs to user
        $address = Address::where('user_id', $userId)
            ->where('id', $validated['address_id'])
            ->with(['province', 'regency', 'district'])
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

        if (!empty($stockErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Stock tidak mencukupi di hub ' . $sourceWarehouse->name . ":\n" . implode("\n", $stockErrors),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();
            $subtotal = $carts->sum(function ($cart) {
                return $cart->product->price * $cart->quantity;
            });

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
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->regency ? $address->regency->name . ', ' : '') .
                ($address->province ? $address->province->name : '') .
                ($address->postal_code ? ' ' . $address->postal_code : '');

            // Calculate points for DRiiPPreneur
            $pointsEarned = 0;
            if ($user->isDriippreneurApproved()) {
                $pointRate = \App\Models\Setting::get('driippreneur_point_rate', 1000);
                $totalItems = $carts->sum('quantity');
                $pointsEarned = (int)$pointRate * $totalItems;
            }

            // Prepare items for Xendit invoice
            $xenditItems = [];
            foreach ($carts as $cart) {
                $xenditItems[] = [
                    'name' => $cart->product->display_name,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->price,
                ];
            }

            // Add shipping as item
            if ($shippingCost > 0) {
                $xenditItems[] = [
                    'name' => 'Ongkos Kirim',
                    'quantity' => 1,
                    'price' => $shippingCost,
                ];
            }

            $order = Order::create([
                'order_type' => Order::TYPE_REGULAR,
                'order_number' => $orderNumber,
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
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'points_earned' => $pointsEarned,
                'points_credited' => false,
            ]);

            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->price,
                    'subtotal' => $cart->product->price * $cart->quantity,
                ]);

                // Reduce stock from warehouse
                $stock = WarehouseStock::where('warehouse_id', $sourceWarehouse->id)
                    ->where('product_id', $cart->product_id)
                    ->first();

                if ($stock) {
                    $stock->decrement('stock', $cart->quantity);
                }
            }

            Cart::where('user_id', $userId)->where('cart_type', 'regular')->delete();

            // Handle Xendit payment first (to get invoice URL)
            $xenditInvoiceUrl = null;
            $xenditInvoiceId = null;

            if ($validated['payment_method'] === 'xendit') {
                $xenditService = new XenditService();
                $customer = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $address->phone,
                ];

                $invoice = $xenditService->createInvoice($order, $customer, $xenditItems);

                if ($invoice && isset($invoice['id'])) {
                    $xenditInvoiceId = $invoice['id'];
                    $xenditInvoiceUrl = $invoice['invoice_url'] ?? null;
                    
                    // If invoice_url is not in the response, try to get it by fetching the invoice
                    if (empty($xenditInvoiceUrl)) {
                        Log::warning('Xendit invoice_url not in response, fetching invoice', [
                            'invoice_id' => $xenditInvoiceId,
                            'order_id' => $order->id,
                        ]);
                        
                        // Try to get invoice details
                        $invoiceDetails = $xenditService->getInvoice($xenditInvoiceId);
                        if ($invoiceDetails && isset($invoiceDetails['invoice_url'])) {
                            $xenditInvoiceUrl = $invoiceDetails['invoice_url'];
                            Log::info('Xendit invoice_url retrieved from getInvoice', [
                                'invoice_id' => $xenditInvoiceId,
                                'invoice_url' => $xenditInvoiceUrl,
                            ]);
                        }
                    }

                    // Ensure invoice_url is available from response
                    if (empty($xenditInvoiceUrl) && isset($invoice['invoice_url'])) {
                        $xenditInvoiceUrl = $invoice['invoice_url'];
                    }
                    
                    // Update order with invoice information
                    $order->xendit_invoice_id = $xenditInvoiceId;
                    $order->xendit_invoice_url = $xenditInvoiceUrl;
                    $order->save();
                    
                    // Refresh order to get updated xendit_invoice_url
                    $order->refresh();
                    
                    // Log for debugging
                    Log::info('Xendit invoice saved to order', [
                        'order_id' => $order->id,
                        'invoice_id' => $xenditInvoiceId,
                        'invoice_url' => $order->xendit_invoice_url,
                        'has_url' => !empty($order->xendit_invoice_url),
                    ]);
                    
                    // If invoice URL is still not available, log warning
                    if (empty($order->xendit_invoice_url)) {
                        Log::warning('Xendit invoice URL still not available after save', [
                            'order_id' => $order->id,
                            'invoice_id' => $xenditInvoiceId,
                            'invoice_response' => $invoice,
                            'xenditInvoiceUrl_var' => $xenditInvoiceUrl,
                        ]);
                    }
                } else {
                    DB::rollBack();
                    Log::error('Failed to create Xendit invoice', [
                        'order_id' => $order->id,
                        'invoice_response' => $invoice,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal membuat invoice pembayaran. Silakan coba lagi atau pilih metode pembayaran lain.',
                    ], 500);
                }
            }

            DB::commit();

            // Load relationships for notification (after commit to ensure data is saved)
            if (!$order->relationLoaded('address') || !$order->relationLoaded('items') || !$order->relationLoaded('expedition')) {
                $order->refresh();
                $order->load(['address.district', 'address.regency', 'address.province', 'items.product', 'expedition']);
            } else {
                // Ensure address relationships are loaded
                if ($order->address && (!$order->address->relationLoaded('district') || !$order->address->relationLoaded('regency') || !$order->address->relationLoaded('province'))) {
                    $order->address->load(['district', 'regency', 'province']);
                }
            }

            // Send WhatsApp payment notification ONLY after Xendit link is ready (for xendit) or immediately (for manual transfer)
            if ($validated['payment_method'] === 'xendit') {
                // Always try to send payment notification with Xendit link
                // If URL is not available, it will be handled in the helper
                try {
                    \App\Helpers\WACloudHelper::sendPaymentNotification($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the checkout process
                    \Log::error('Failed to send WhatsApp payment notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // For manual transfer, send notification immediately
                try {
                    \App\Helpers\WACloudHelper::sendPaymentNotification($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the checkout process
                    \Log::error('Failed to send WhatsApp payment notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Note: Thank you notification will be sent after payment is successful via Xendit webhook

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
                    'xendit_invoice_url' => $xenditInvoiceUrl,
                    'xendit_invoice_id' => $xenditInvoiceId,
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
                'xendit_invoice_url' => $order->xendit_invoice_url,
                'xendit_invoice_id' => $order->xendit_invoice_id,
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
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())->latest()->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'ORD-' . $date . '-' . $newNumber;
    }
}

