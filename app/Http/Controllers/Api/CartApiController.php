<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartApiController extends Controller
{
    /**
     * Get user's cart items
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $carts = Cart::with(['product.brand', 'product.category', 'warehouse'])
            ->where('user_id', $user->id)
            ->where('cart_type', 'regular')
            ->get();

        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $items = $carts->map(function ($cart) {
            // Build full image URL
            $imageUrl = null;
            if ($cart->product->image) {
                if (filter_var($cart->product->image, FILTER_VALIDATE_URL)) {
                    $imageUrl = $cart->product->image;
                } else {
                    $imagePath = ltrim($cart->product->image, '/');
                    $imageUrl = asset('images/' . $imagePath);
                }
            }

            return [
                'id' => $cart->id,
                'product' => [
                    'id' => $cart->product->id,
                    'code' => $cart->product->code,
                    'name' => $cart->product->name,
                    'price' => (float) $cart->product->price,
                    'unit' => $cart->product->unit,
                    'image' => $imageUrl,
                    'brand' => $cart->product->brand ? [
                        'id' => $cart->product->brand->id,
                        'name' => $cart->product->brand->name,
                    ] : null,
                    'category' => $cart->product->category ? [
                        'id' => $cart->product->category->id,
                        'name' => $cart->product->category->name,
                    ] : null,
                ],
                'warehouse' => $cart->warehouse ? [
                    'id' => $cart->warehouse->id,
                    'name' => $cart->warehouse->name,
                ] : null,
                'quantity' => $cart->quantity,
                'subtotal' => (float) ($cart->product->price * $cart->quantity),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'summary' => [
                'total_items' => $carts->sum('quantity'),
                'total_products' => $carts->count(),
                'subtotal' => (float) $subtotal,
            ],
        ]);
    }

    /**
     * Add product to cart
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        // Check stock availability
        $stock = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
        
        $availableStock = $stock ? $stock->stock : 0;
        
        if ($validated['quantity'] > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Stock tidak mencukupi. Tersedia: {$availableStock} unit.",
            ], 400);
        }

        // Check if cart has items from different warehouse
        $existingCart = Cart::where('user_id', $user->id)
            ->where('cart_type', 'regular')
            ->whereNotNull('warehouse_id')
            ->where('warehouse_id', '!=', $warehouse->id)
            ->first();

        if ($existingCart) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.',
            ], 400);
        }

        // Check if same product from same warehouse exists
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('cart_type', 'regular')
            ->first();

        if ($cart) {
            $newQuantity = $cart->quantity + $validated['quantity'];
            if ($newQuantity > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Total quantity melebihi stock. Tersedia: {$availableStock} unit.",
                ], 400);
            }
            $cart->quantity = $newQuantity;
            $cart->save();
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'cart_type' => 'regular',
                'quantity' => $validated['quantity'],
            ]);
        }

        $cart->load(['product.brand', 'product.category', 'warehouse']);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'data' => [
                'id' => $cart->id,
                'product' => [
                    'id' => $cart->product->id,
                    'name' => $cart->product->name,
                    'price' => (float) $cart->product->price,
                ],
                'warehouse' => [
                    'id' => $cart->warehouse->id,
                    'name' => $cart->warehouse->name,
                ],
                'quantity' => $cart->quantity,
                'subtotal' => (float) ($cart->product->price * $cart->quantity),
            ],
        ], 201);
    }

    /**
     * Update cart item quantity
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->where('id', $id)
            ->where('cart_type', 'regular')
            ->firstOrFail();

        // Check stock availability
        if ($cart->warehouse_id) {
            $stock = WarehouseStock::where('warehouse_id', $cart->warehouse_id)
                ->where('product_id', $cart->product_id)
                ->first();
            
            $availableStock = $stock ? $stock->stock : 0;
            
            if ($validated['quantity'] > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock tidak mencukupi. Tersedia: {$availableStock} unit.",
                ], 400);
            }
        }

        $cart->quantity = $validated['quantity'];
        $cart->save();
        $cart->load(['product', 'warehouse']);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui.',
            'data' => [
                'id' => $cart->id,
                'quantity' => $cart->quantity,
                'subtotal' => (float) ($cart->product->price * $cart->quantity),
            ],
        ]);
    }

    /**
     * Remove item from cart
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->where('id', $id)
            ->where('cart_type', 'regular')
            ->firstOrFail();

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang.',
        ]);
    }

    /**
     * Clear all items from cart
     * 
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        $user = Auth::user();
        Cart::where('user_id', $user->id)
            ->where('cart_type', 'regular')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan.',
        ]);
    }
}

