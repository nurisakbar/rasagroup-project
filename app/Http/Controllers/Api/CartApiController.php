<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CartApiController extends Controller
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

    /**
     * Get user's cart items
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'user_id diperlukan. Kirim sebagai query parameter atau body, atau login terlebih dahulu.',
            ], 400);
        }
        
        $carts = Cart::with(['product.brand', 'product.category', 'warehouse'])
            ->where('user_id', $userId)
            ->where('cart_type', 'regular')
            ->get();

        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $items = $carts->map(function ($cart) {
            // Build full image URL using Product accessor
            $imageUrl = $cart->product->image_url;

            return [
                'id' => $cart->id,
                'product' => [
                    'id' => $cart->product->id,
                    'code' => $cart->product->code,
                    'name' => $cart->product->display_name,
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
                'order_uom' => $cart->order_uom,
                'quantity_ordered' => $cart->quantity_ordered,
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
            'user_id' => 'required|string|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'uom' => ['nullable', Rule::in(['base', 'large'])],
        ]);

        $userId = $this->getUserId($request) ?? $validated['user_id'];
        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $uom = $validated['uom'] ?? 'base';
        if ($uom === 'large' && ! $product->hasDualUnitOrdering()) {
            $uom = 'base';
        }

        $baseQty = $product->orderedQuantityToBase((int) $validated['quantity'], $uom);
        $merge = app(CartController::class)->mergeRegularCartLine(
            $product,
            $warehouse,
            $baseQty,
            false,
            $uom,
            (int) $validated['quantity'],
            $userId,
            null
        );

        if (! $merge['ok']) {
            return response()->json([
                'success' => false,
                'message' => $merge['error'],
            ], 400);
        }

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('cart_type', 'regular')
            ->firstOrFail();

        $cart->load(['product.brand', 'product.category', 'warehouse']);

        return response()->json([
            'success' => true,
            'message' => 'Produk "' . $product->display_name . '" berhasil ditambahkan.',
            'data' => [
                'id' => $cart->id,
                'product' => [
                    'id' => $cart->product->id,
                    'name' => $cart->product->display_name,
                    'price' => (float) $cart->product->price,
                ],
                'warehouse' => [
                    'id' => $cart->warehouse->id,
                    'name' => $cart->warehouse->name,
                ],
                'quantity' => $cart->quantity,
                'order_uom' => $cart->order_uom,
                'quantity_ordered' => $cart->quantity_ordered,
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
            'user_id' => 'required|string|exists:users,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $this->getUserId($request) ?? $validated['user_id'];
        $cart = Cart::where('user_id', $userId)
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

        $cart->quantity = (int) $validated['quantity'];
        $cart->syncOrderedMetadataFromBaseQuantity();
        $cart->save();
        $cart->load(['product', 'warehouse']);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui.',
            'data' => [
                'id' => $cart->id,
                'quantity' => $cart->quantity,
                'order_uom' => $cart->order_uom,
                'quantity_ordered' => $cart->quantity_ordered,
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
    public function destroy(Request $request, string $id): JsonResponse
    {
        $userId = $this->getUserId($request);
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'user_id diperlukan. Kirim sebagai query parameter atau body, atau login terlebih dahulu.',
            ], 400);
        }
        
        $cart = Cart::where('user_id', $userId)
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
    public function clear(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'user_id diperlukan. Kirim sebagai query parameter atau body, atau login terlebih dahulu.',
            ], 400);
        }
        
        Cart::where('user_id', $userId)
            ->where('cart_type', 'regular')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan.',
        ]);
    }
}

