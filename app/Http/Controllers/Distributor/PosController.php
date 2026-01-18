<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    /**
     * Display POS page.
     */
    public function index()
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return redirect()->route('distributor.dashboard')
                ->with('error', 'Anda tidak memiliki warehouse yang terkait.');
        }

        return view('distributor.pos.index', compact('warehouse'));
    }

    /**
     * Search products for POS.
     */
    public function searchProducts(Request $request)
    {
        try {
            $user = Auth::user();
            $warehouse = $user->warehouse;

            if (!$warehouse) {
                return response()->json(['error' => 'Warehouse tidak ditemukan'], 404);
            }

            $keyword = $request->get('q', '');
            $limit = $request->get('limit', 100); // Increase limit for initial load

            $query = Product::where('status', 'active')
                ->whereHas('warehouseStocks', function ($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id)
                      ->where('stock', '>', 0);
                })
                ->with(['brand', 'category']);

            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('commercial_name', 'like', "%{$keyword}%");
                });
            }

            // Order by name for better UX
            $query->orderBy('name');

            $products = $query->limit($limit)->get();

            $results = $products->map(function ($product) use ($warehouse) {
                $stock = WarehouseStock::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->first();

                return [
                    'id' => $product->id,
                    'name' => $product->display_name,
                    'code' => $product->code,
                    'price' => (float) $product->price,
                    'formatted_price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                    'image' => $product->image_url,
                    'stock' => $stock ? (int) $stock->stock : 0,
                    'brand' => $product->brand ? $product->brand->name : null,
                ];
            });

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('POS Search Products Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat mencari produk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add product to POS cart (session-based).
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse tidak ditemukan'], 404);
        }

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'active') {
            return response()->json(['error' => 'Produk tidak aktif'], 400);
        }

        // Check stock
        $stock = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $availableStock = $stock ? $stock->stock : 0;

        // Get current cart from session
        $cart = session()->get('pos_cart', []);
        $currentQuantity = isset($cart[$product->id]) ? $cart[$product->id]['quantity'] : 0;
        $newQuantity = $currentQuantity + $request->quantity;

        if ($newQuantity > $availableStock) {
            return response()->json([
                'error' => "Stock tidak mencukupi. Tersedia: {$availableStock} unit."
            ], 400);
        }

        // Add/update cart item
        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'price' => $product->price,
            'quantity' => $newQuantity,
            'subtotal' => $product->price * $newQuantity,
            'image' => $product->image_url,
            'stock' => $availableStock,
        ];

        session()->put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke cart',
            'cart' => $this->getCartData(),
        ]);
    }

    /**
     * Get cart data.
     */
    public function getCart()
    {
        return response()->json($this->getCartData());
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request, string $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse tidak ditemukan'], 404);
        }

        $cart = session()->get('pos_cart', []);

        if (!isset($cart[$productId])) {
            return response()->json(['error' => 'Item tidak ditemukan di cart'], 404);
        }

        if ($request->quantity == 0) {
            // Remove item
            unset($cart[$productId]);
        } else {
            // Check stock
            $stock = WarehouseStock::where('warehouse_id', $warehouse->id)
                ->where('product_id', $productId)
                ->first();

            $availableStock = $stock ? $stock->stock : 0;

            if ($request->quantity > $availableStock) {
                return response()->json([
                    'error' => "Stock tidak mencukupi. Tersedia: {$availableStock} unit."
                ], 400);
            }

            // Update quantity
            $cart[$productId]['quantity'] = $request->quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['price'] * $request->quantity;
        }

        session()->put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart diperbarui',
            'cart' => $this->getCartData(),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(string $productId)
    {
        $cart = session()->get('pos_cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('pos_cart', $cart);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item dihapus dari cart',
            'cart' => $this->getCartData(),
        ]);
    }

    /**
     * Clear cart.
     */
    public function clearCart()
    {
        session()->forget('pos_cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart dikosongkan',
            'cart' => $this->getCartData(),
        ]);
    }

    /**
     * Process checkout.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse tidak ditemukan'], 404);
        }

        $cart = session()->get('pos_cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Cart kosong'], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,transfer,qris,debit,credit',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['subtotal'];
            }

            $totalAmount = $subtotal; // No shipping cost for POS

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'order_type' => Order::TYPE_POS,
                'source_warehouse_id' => $warehouse->id,
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'total_amount' => $totalAmount,
                'shipping_address' => $request->customer_name ? 
                    ($request->customer_name . ($request->customer_phone ? ' - ' . $request->customer_phone : '')) : 
                    'Penjualan Offline',
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'paid_at' => now(),
                'order_status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Create order items and reduce stock
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Reduce stock
                $warehouseStock = WarehouseStock::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($warehouseStock) {
                    $stockBefore = $warehouseStock->stock;
                    $warehouseStock->decrement('stock', $item['quantity']);
                    $stockAfter = $warehouseStock->stock;

                    // Record stock history
                    WarehouseStockHistory::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'quantity_added' => -$item['quantity'], // Negative for reduction
                        'notes' => 'Penjualan POS - Order ' . $orderNumber,
                    ]);
                }
            }

            DB::commit();

            // Clear cart
            session()->forget('pos_cart');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Checkout Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'warehouse_id' => $warehouse->id,
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat proses checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart data formatted.
     */
    private function getCartData()
    {
        $cart = session()->get('pos_cart', []);
        $items = array_values($cart);
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $total = $subtotal; // No shipping for POS

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $total,
            'item_count' => count($items),
            'total_quantity' => array_sum(array_column($items, 'quantity')),
        ];
    }

    /**
     * Generate order number for POS.
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::where('order_type', Order::TYPE_POS)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'POS-' . $date . '-' . $newNumber;
    }
}
