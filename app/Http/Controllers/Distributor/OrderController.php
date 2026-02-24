<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // Shipping rates per province
    private $shippingRates = [
        '11' => 15000, '12' => 15000, '13' => 18000, '14' => 20000, '15' => 22000,
        '16' => 25000, '17' => 25000, '18' => 28000, '19' => 30000, '21' => 32000,
        '31' => 10000, '32' => 12000, '33' => 12000, '34' => 10000, '35' => 12000,
        '36' => 15000, '51' => 20000, '52' => 25000, '53' => 30000, '61' => 35000,
        '62' => 35000, '63' => 35000, '64' => 35000, '65' => 35000, '71' => 40000,
        '72' => 40000, '73' => 40000, '74' => 40000, '75' => 40000, '76' => 40000,
        '81' => 50000, '82' => 50000, '91' => 55000, '94' => 55000,
    ];

    /**
     * Display products catalog for distributor ordering.
     */
    public function products(Request $request)
    {
        $query = Product::where('status', 'active');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->paginate(12);

        return view('distributor.orders.products', compact('products'));
    }

    /**
     * Display distributor cart.
     */
    public function cart()
    {
        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_type', 'distributor')
            ->get();

        $user = Auth::user();
        $subtotal = $carts->sum(function ($cart) use ($user) {
            return $user->getProductPrice($cart->product) * $cart->quantity;
        });

        $totalItems = $carts->sum('quantity');
        $potentialPoints = $totalItems * Order::DISTRIBUTOR_POINTS_PER_ITEM;

        // Add price info to each cart item for display
        $carts->each(function ($cart) use ($user) {
            $cart->display_price = $user->getProductPrice($cart->product);
            $cart->display_subtotal = $cart->display_price * $cart->quantity;
        });

        return view('distributor.orders.cart', compact('carts', 'subtotal', 'totalItems', 'potentialPoints'));
    }

    /**
     * Add product to distributor cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('cart_type', 'distributor')
            ->first();

        if ($cart) {
            $cart->update(['quantity' => $cart->quantity + $request->quantity]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'cart_type' => 'distributor',
            ]);
        }

        $product = Product::find($request->product_id);
        return redirect()->route('distributor.orders.cart')
            ->with('success', 'Produk "' . ($product->display_name ?? 'Produk') . '" berhasil ditambahkan.');
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id() || $cart->cart_type !== 'distributor') {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Jumlah berhasil diperbarui.');
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Cart $cart)
    {
        if ($cart->user_id !== Auth::id() || $cart->cart_type !== 'distributor') {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Display checkout page.
     */
    public function checkout()
    {
        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_type', 'distributor')
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('distributor.orders.cart')
                ->with('error', 'Keranjang kosong.');
        }

        $user = Auth::user();
        $subtotal = $carts->sum(function ($cart) use ($user) {
            return $user->getProductPrice($cart->product) * $cart->quantity;
        });

        $totalItems = $carts->sum('quantity');
        $potentialPoints = $totalItems * Order::DISTRIBUTOR_POINTS_PER_ITEM;

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district'])
            ->orderByDesc('is_default')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $expeditions = Expedition::active()->get();
        $defaultExpedition = $expeditions->first();
        $defaultService = $defaultExpedition ? $defaultExpedition->services[0] : null;

        $shippingCost = 0;
        if ($defaultAddress && $defaultExpedition && $defaultService) {
            $shippingCost = $this->calculateShippingCost(
                $defaultAddress->province_id,
                $totalWeight,
                $defaultExpedition->base_cost,
                $defaultService['multiplier']
            );
        }

        $total = $subtotal + $shippingCost;

        $provinces = \App\Models\Province::orderBy('name')->get();

        // Get all active warehouses (hubs) for distributor to choose from
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->with(['province', 'regency'])
            ->orderBy('name')
            ->get();

        // Add price info to each cart item for display
        $carts->each(function ($cart) use ($user) {
            $cart->display_price = $user->getProductPrice($cart->product);
            $cart->display_subtotal = $cart->display_price * $cart->quantity;
        });

        return view('distributor.orders.checkout', compact(
            'carts',
            'subtotal',
            'shippingCost',
            'total',
            'addresses',
            'defaultAddress',
            'totalWeight',
            'expeditions',
            'defaultExpedition',
            'defaultService',
            'totalItems',
            'potentialPoints',
            'provinces',
            'warehouses'
        ));
    }

    /**
     * Calculate shipping cost via AJAX.
     */
    public function calculateShipping(Request $request)
    {
        $address = Address::find($request->address_id);

        if (!$address || $address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Alamat tidak valid'], 400);
        }

        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_type', 'distributor')
            ->get();

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $user = Auth::user();
        $subtotal = $carts->sum(function ($cart) use ($user) {
            return $user->getProductPrice($cart->product) * $cart->quantity;
        });

        $expedition = Expedition::find($request->expedition_id);
        $expeditionMultiplier = $expedition ? $expedition->base_cost : 1.0;

        $serviceMultiplier = 1.0;
        $serviceName = 'Reguler';
        $estDaysMin = $expedition->est_days_min ?? 2;
        $estDaysMax = $expedition->est_days_max ?? 4;

        if ($expedition && $request->service_code) {
            foreach ($expedition->services as $service) {
                if ($service['code'] === $request->service_code) {
                    $serviceMultiplier = $service['multiplier'];
                    $serviceName = $service['name'];
                    $estDaysMin = max(1, $expedition->est_days_min + $service['days_add']);
                    $estDaysMax = max(1, $expedition->est_days_max + $service['days_add']);
                    break;
                }
            }
        }

        $shippingCost = $this->calculateShippingCost(
            $address->province_id,
            $totalWeight,
            $expeditionMultiplier,
            $serviceMultiplier
        );

        return response()->json([
            'shipping_cost' => $shippingCost,
            'shipping_cost_formatted' => 'Rp ' . number_format($shippingCost, 0, ',', '.'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'total' => $subtotal + $shippingCost,
            'total_formatted' => 'Rp ' . number_format($subtotal + $shippingCost, 0, ',', '.'),
            'total_weight' => $totalWeight,
            'total_weight_formatted' => number_format($totalWeight / 1000, 1) . ' kg',
            'service_name' => $serviceName,
            'estimated_delivery' => $estDaysMin === $estDaysMax
                ? $estDaysMin . ' hari'
                : $estDaysMin . '-' . $estDaysMax . ' hari',
        ]);
    }

    /**
     * Get expedition services via AJAX.
     */
    public function getExpeditionServices(Request $request)
    {
        $expedition = Expedition::find($request->expedition_id);

        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak valid'], 400);
        }

        $address = Address::find($request->address_id);
        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_type', 'distributor')
            ->get();

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $provinceId = $address ? $address->province_id : '31';

        $services = [];
        foreach ($expedition->services as $service) {
            $cost = $this->calculateShippingCost(
                $provinceId,
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
            'expedition' => [
                'id' => $expedition->id,
                'code' => $expedition->code,
                'name' => $expedition->name,
            ],
            'services' => $services,
        ]);
    }

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
     * Process order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'expedition_id' => 'required|exists:expeditions,id',
            'expedition_service' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        $address = Address::where('id', $request->address_id)
            ->where('user_id', Auth::id())
            ->with(['province', 'regency', 'district'])
            ->first();

        if (!$address) {
            return back()->with('error', 'Alamat tidak valid.');
        }

        $expedition = Expedition::find($request->expedition_id);
        if (!$expedition) {
            return back()->with('error', 'Ekspedisi tidak valid.');
        }

        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_type', 'distributor')
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('distributor.orders.cart')
                ->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();
            $user = Auth::user();
            $subtotal = $carts->sum(function ($cart) use ($user) {
                return $user->getProductPrice($cart->product) * $cart->quantity;
            });

            $totalWeight = $carts->sum(function ($cart) {
                return ($cart->product->weight ?? 500) * $cart->quantity;
            });

            $totalItems = $carts->sum('quantity');
            $pointsEarned = $totalItems * Order::DISTRIBUTOR_POINTS_PER_ITEM;

            $serviceMultiplier = 1.0;
            foreach ($expedition->services as $service) {
                if ($service['code'] === $request->expedition_service) {
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

            $shippingAddressText = $address->recipient_name . "\n" .
                $address->phone . "\n" .
                $address->address_detail . "\n" .
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->regency ? $address->regency->name . ', ' : '') .
                ($address->province ? $address->province->name : '') .
                ($address->postal_code ? ' ' . $address->postal_code : '');

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'order_type' => Order::TYPE_DISTRIBUTOR,
                'address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'expedition_service' => $request->expedition_service,
                'source_warehouse_id' => $request->source_warehouse_id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'points_earned' => $pointsEarned,
                'points_credited' => false,
                'shipping_address' => $shippingAddressText,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($carts as $cart) {
                $productPrice = $user->getProductPrice($cart->product);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $productPrice,
                    'subtotal' => $productPrice * $cart->quantity,
                ]);
            }

            Cart::where('user_id', Auth::id())
                ->where('cart_type', 'distributor')
                ->delete();

            DB::commit();

            return redirect()->route('distributor.orders.success', $order)
                ->with('success', 'Pesanan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Order success page.
     */
    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->order_type !== Order::TYPE_DISTRIBUTOR) {
            abort(403);
        }

        $order->load('items.product', 'address', 'expedition');

        return view('distributor.orders.success', compact('order'));
    }

    /**
     * Order history.
     */
    public function history(Request $request)
    {
        $query = Order::where('user_id', Auth::id())
            ->where('order_type', Order::TYPE_DISTRIBUTOR)
            ->with(['items.product', 'expedition']);

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(10);

        return view('distributor.orders.history', compact('orders'));
    }

    /**
     * Order detail.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->order_type !== Order::TYPE_DISTRIBUTOR) {
            abort(403);
        }

        $order->load('items.product', 'address', 'expedition');
        $warehouse = Auth::user()->warehouse;

        return view('distributor.orders.show', compact('order', 'warehouse'));
    }

    /**
     * Convert order items to stock (for delivered/completed orders)
     */
    public function convertToStock(Order $order)
    {
        $user = Auth::user();
        
        // Verify the order belongs to user
        if ($order->user_id !== $user->id || $order->order_type !== Order::TYPE_DISTRIBUTOR) {
            abort(403, 'Akses ditolak.');
        }

        // Check if user has warehouse
        $warehouse = $user->warehouse;
        if (!$warehouse) {
            return back()->with('error', 'Anda tidak memiliki warehouse yang terkait.');
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
                    $skippedItems[] = $item->product->display_name . ' (quantity tidak valid)';
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
                    'product' => $item->product->display_name,
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

        return 'DST-' . $date . '-' . $newNumber;
    }
}

