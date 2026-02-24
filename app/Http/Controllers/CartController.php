<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $carts = Cart::with(['product', 'warehouse'])
                ->where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->get();
        } else {
            $sessionId = session()->getId();
            $carts = Cart::with(['product', 'warehouse'])
                ->where('session_id', $sessionId)
                ->where('cart_type', 'regular')
                ->get();
        }

        $total = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        // Get the warehouse for this cart (all items should be from same warehouse)
        $cartWarehouse = $carts->first()?->warehouse;

        return view('cart.index', compact('carts', 'total', 'cartWarehouse'));
    }

    public function update(Request $request, Cart $cart)
    {
        // Check ownership
        if (Auth::check() && $cart->user_id !== Auth::id()) {
            abort(403);
        }
        if (!Auth::check() && $cart->session_id !== session()->getId()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock availability if warehouse is set
        if ($cart->warehouse_id) {
            $stock = WarehouseStock::where('warehouse_id', $cart->warehouse_id)
                ->where('product_id', $cart->product_id)
                ->first();
            
            $availableStock = $stock ? $stock->stock : 0;
            
            if ($request->quantity > $availableStock) {
                return back()->with('error', "Stock tidak mencukupi. Tersedia: {$availableStock} unit.");
            }
        }

        $cart->quantity = $request->quantity;
        $cart->save();

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Cart $cart)
    {
        // Check ownership
        if (Auth::check() && $cart->user_id !== Auth::id()) {
            abort(403);
        }
        if (!Auth::check() && $cart->session_id !== session()->getId()) {
            abort(403);
        }

        $cart->delete();
        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function store(Request $request, Product $product)
    {

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'warehouse_id' => 'required',
        ], [
            'warehouse_id.required' => 'Pilih hub pengirim terlebih dahulu.',
        ]);

        $warehouse = Warehouse::where('id', $request->warehouse_id)
            ->orWhere('slug', $request->warehouse_id)
            ->first();

        if (!$warehouse || !$warehouse->is_active) {
            return back()->with('error', 'Hub tidak valid atau tidak tersedia.');
        }

        $warehouseId = $warehouse->id;


        // Check stock availability
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->first();

        $availableStock = $stock ? $stock->stock : 0;

        if ($request->quantity > $availableStock) {
            return back()->with('error', "Stock tidak mencukupi di hub {$warehouse->name}. Tersedia: {$availableStock} unit.");
        }

        if (Auth::check()) {
            // Check if cart has items from different warehouse
            $existingCart = Cart::where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->whereNotNull('warehouse_id')
                ->where('warehouse_id', '!=', $warehouseId)
                ->first();

            if ($existingCart) {
                return back()->with('error', 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.');
            }

            // Check if same product from same warehouse exists
            $cart = Cart::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();

            if ($cart) {
                $newQuantity = $cart->quantity + $request->quantity;
                if ($newQuantity > $availableStock) {
                    return back()->with('error', "Total quantity melebihi stock. Tersedia: {$availableStock} unit.");
                }
                $cart->quantity = $newQuantity;
                $cart->save();
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'cart_type' => 'regular',
                    'quantity' => $request->quantity,
                ]);
            }
        } else {
            $sessionId = session()->getId();
            
            // Check if cart has items from different warehouse
            $existingCart = Cart::where('session_id', $sessionId)
                ->where('cart_type', 'regular')
                ->whereNotNull('warehouse_id')
                ->where('warehouse_id', '!=', $warehouseId)
                ->first();

            if ($existingCart) {
                return back()->with('error', 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.');
            }

            $cart = Cart::where('session_id', $sessionId)
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();

            if ($cart) {
                $newQuantity = $cart->quantity + $request->quantity;
                if ($newQuantity > $availableStock) {
                    return back()->with('error', "Total quantity melebihi stock. Tersedia: {$availableStock} unit.");
                }
                $cart->quantity = $newQuantity;
                $cart->save();
            } else {
                Cart::create([
                    'session_id' => $sessionId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'cart_type' => 'regular',
                    'quantity' => $request->quantity,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk "' . $product->display_name . '" berhasil ditambahkan.',
                'cart_count' => Auth::check() 
                    ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->sum('quantity')
                    : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity')
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Produk "' . $product->display_name . '" berhasil ditambahkan.');
    }

    /**
     * Clear all items from cart
     */
    public function clear()
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->delete();
        } else {
            Cart::where('session_id', session()->getId())
                ->where('cart_type', 'regular')
                ->delete();
        }

        return back()->with('success', 'Keranjang dikosongkan.');
    }

    /**
     * Get stock info for a product in different warehouses (AJAX)
     */
    public function getProductStock(Request $request, Product $product)
    {
        $warehouseId = $request->query('warehouse_id');
        
        // If specific warehouse_id is requested, return that warehouse's stock (even if 0)
        if ($warehouseId) {
            try {
                // First check if warehouse exists (by ID or Slug) and is active
                $warehouse = Warehouse::with(['province', 'regency'])
                    ->where(function($q) use ($warehouseId) {
                        $q->where('id', $warehouseId)
                          ->orWhere('slug', $warehouseId);
                    })
                    ->where('is_active', true)
                    ->first();
                
                if (!$warehouse) {
                    return response()->json([
                        'error' => 'Warehouse not found',
                        'stocks' => []
                    ], 404);
                }
                
                // Then check for stock record
                $stock = WarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                
                $location = $warehouse->full_location;
                if (!$location || $location === '-') {
                    $locationParts = [];
                    if ($warehouse->regency) {
                        $locationParts[] = $warehouse->regency->name;
                    }
                    if ($warehouse->province) {
                        $locationParts[] = $warehouse->province->name;
                    }
                    $location = !empty($locationParts) ? implode(', ', $locationParts) : '-';
                }
                
                return response()->json([
                    'stocks' => [[
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'warehouse_location' => $location,
                        'stock' => $stock ? (int)$stock->stock : 0,
                    ]]
                ]);
            } catch (\Exception $e) {
                \Log::error('Error fetching warehouse stock: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Server error',
                    'stocks' => []
                ], 500);
            }
        }
        
        // Otherwise, return all warehouses with stock > 0
        $stocks = WarehouseStock::with(['warehouse.province', 'warehouse.regency'])
            ->where('product_id', $product->id)
            ->where('stock', '>', 0)
            ->whereHas('warehouse', function ($q) {
                $q->where('is_active', true);
            })
            ->get();

        return response()->json([
            'stocks' => $stocks->map(function ($stock) {
                return [
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name,
                    'warehouse_location' => $stock->warehouse->full_location,
                    'stock' => $stock->stock,
                ];
            }),
        ]);
    }
}
