<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $this->logCartStore('store: begin', [
            'product_id' => $product->id,
            'product_slug' => $product->slug,
            'ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'auth_id' => Auth::id(),
            'session_prefix' => substr((string) session()->getId(), 0, 10),
            'selected_hub_session' => session('selected_hub_id'),
            'input' => $request->only(['quantity', 'warehouse_id', 'uom']),
        ]);

        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'warehouse_id' => 'required',
                'uom' => ['nullable', Rule::in(['base', 'large'])],
            ], [
                'warehouse_id.required' => 'Pilih hub pengirim terlebih dahulu.',
            ]);
        } catch (ValidationException $e) {
            $this->logCartStore('store: validation failed', [
                'errors' => $e->errors(),
                'product_id' => $product->id,
            ]);
            throw $e;
        }

        $uom = $request->input('uom', 'base');
        if ($uom === 'large' && ! $product->hasDualUnitOrdering()) {
            $uom = 'base';
            $this->logCartStore('store: uom forced to base (no dual unit)', ['product_id' => $product->id]);
        }

        $baseQuantity = $product->orderedQuantityToBase((int) $request->quantity, $uom);
        $this->logCartStore('store: quantity computed', [
            'requested_qty' => (int) $request->quantity,
            'uom' => $uom,
            'base_quantity' => $baseQuantity,
        ]);

        if ($baseQuantity < 1) {
            $this->logCartStore('store: abort invalid base quantity', ['base_quantity' => $baseQuantity]);

            return $request->ajax()
                ? response()->json(['error' => 'Jumlah tidak valid.'], 422)
                : back()->with('error', 'Jumlah tidak valid.');
        }

        $warehouse = Warehouse::where('id', $request->warehouse_id)
            ->orWhere('slug', $request->warehouse_id)
            ->first();

        if (!$warehouse || !$warehouse->is_active) {
            $this->logCartStore('store: abort warehouse missing or inactive', [
                'warehouse_id_input' => $request->warehouse_id,
                'resolved_id' => $warehouse?->id,
                'is_active' => $warehouse?->is_active,
            ]);

            return back()->with('error', 'Hub tidak valid atau tidak tersedia.');
        }

        $warehouseId = $warehouse->id;

        $this->logCartStore('store: warehouse resolved', [
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name,
        ]);

        // Check stock availability
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->first();

        $availableStock = $stock ? $stock->stock : 0;

        if ($baseQuantity > $availableStock) {
            $unitLabel = $product->unit ?: 'unit';
            $this->logCartStore('store: abort insufficient stock', [
                'base_quantity' => $baseQuantity,
                'available_stock' => $availableStock,
                'warehouse_id' => $warehouseId,
            ]);

            return $request->ajax()
                ? response()->json([
                    'error' => "Stock tidak mencukupi di hub {$warehouse->name}. Tersedia: {$availableStock} {$unitLabel}.",
                ], 422)
                : back()->with('error', "Stock tidak mencukupi di hub {$warehouse->name}. Tersedia: {$availableStock} {$unitLabel}.");
        }

        if (Auth::check()) {
            // Check if cart has items from different warehouse
            $existingCart = Cart::where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->whereNotNull('warehouse_id')
                ->where('warehouse_id', '!=', $warehouseId)
                ->first();

            if ($existingCart) {
                $this->logCartStore('store: abort mixed warehouse (auth)', [
                    'existing_warehouse_id' => $existingCart->warehouse_id,
                    'requested_warehouse_id' => $warehouseId,
                ]);

                return back()->with('error', 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.');
            }

            // Check if same product from same warehouse exists
            $cart = Cart::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();

            if ($cart) {
                $newQuantity = $cart->quantity + $baseQuantity;
                if ($newQuantity > $availableStock) {
                    $this->logCartStore('store: abort merge exceeds stock (auth)', [
                        'cart_id' => $cart->id,
                        'new_quantity' => $newQuantity,
                        'available_stock' => $availableStock,
                    ]);

                    return $request->ajax()
                        ? response()->json(['error' => "Total quantity melebihi stock. Tersedia: {$availableStock} unit."], 422)
                        : back()->with('error', "Total quantity melebihi stock. Tersedia: {$availableStock} unit.");
                }
                $cart->quantity = $newQuantity;
                $cart->save();
                $this->logCartStore('store: updated line (auth)', [
                    'cart_id' => $cart->id,
                    'new_quantity' => $newQuantity,
                ]);
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'cart_type' => 'regular',
                    'quantity' => $baseQuantity,
                ]);
                $this->logCartStore('store: created line (auth)', [
                    'warehouse_id' => $warehouseId,
                    'quantity' => $baseQuantity,
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
                $this->logCartStore('store: abort mixed warehouse (guest)', [
                    'existing_warehouse_id' => $existingCart->warehouse_id,
                    'requested_warehouse_id' => $warehouseId,
                ]);

                return back()->with('error', 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.');
            }

            $cart = Cart::where('session_id', $sessionId)
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();

            if ($cart) {
                $newQuantity = $cart->quantity + $baseQuantity;
                if ($newQuantity > $availableStock) {
                    $this->logCartStore('store: abort merge exceeds stock (guest)', [
                        'cart_id' => $cart->id,
                        'new_quantity' => $newQuantity,
                        'available_stock' => $availableStock,
                    ]);

                    return $request->ajax()
                        ? response()->json(['error' => "Total quantity melebihi stock. Tersedia: {$availableStock} unit."], 422)
                        : back()->with('error', "Total quantity melebihi stock. Tersedia: {$availableStock} unit.");
                }
                $cart->quantity = $newQuantity;
                $cart->save();
                $this->logCartStore('store: updated line (guest)', [
                    'cart_id' => $cart->id,
                    'new_quantity' => $newQuantity,
                ]);
            } else {
                Cart::create([
                    'session_id' => $sessionId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'cart_type' => 'regular',
                    'quantity' => $baseQuantity,
                ]);
                $this->logCartStore('store: created line (guest)', [
                    'warehouse_id' => $warehouseId,
                    'quantity' => $baseQuantity,
                ]);
            }
        }

        $this->logCartStore('store: success', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'response' => $request->ajax() ? 'json' : 'redirect_back',
        ]);

        if ($request->ajax()) {
            $cartCount = Auth::check() 
                ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->sum('quantity')
                : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity');

            return response()->json([
                'success' => true,
                'message' => 'Produk "' . $product->display_name . '" berhasil ditambahkan.',
                'cart_count' => $cartCount,
                'mini_cart_html' => view('themes.nest.partials.mini-cart')->render()
            ]);
        }

        return back()->with('success', 'Produk "' . $product->display_name . '" berhasil ditambahkan.');
    }

    /**
     * Log debug alur tambah ke keranjang. File: storage/logs/cart-debug.log
     * Aktif jika LOG_CART_DEBUG=true atau APP_ENV=local.
     */
    private function logCartStore(string $message, array $context = []): void
    {
        $flag = env('LOG_CART_DEBUG');
        if ($flag === null) {
            $enabled = app()->environment('local');
        } else {
            $enabled = filter_var($flag, FILTER_VALIDATE_BOOLEAN);
        }
        if (! $enabled) {
            return;
        }

        Log::channel('cart_debug')->info($message, $context);
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
     * Remove items that are out of stock in their respective warehouse
     */
    public function removeOutOfStock()
    {
        if (Auth::check()) {
            $carts = Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->get();
        } else {
            $carts = Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->get();
        }

        $removedCount = 0;
        foreach ($carts as $cart) {
            if ($cart->warehouse_id) {
                $stock = WarehouseStock::where('warehouse_id', $cart->warehouse_id)
                    ->where('product_id', $cart->product_id)
                    ->first();
                
                $availableStock = $stock ? $stock->stock : 0;
                
                if ($cart->quantity > $availableStock) {
                    $cart->delete();
                    $removedCount++;
                }
            }
        }

        return back()->with('success', "$removedCount item yang stoknya tidak mencukupi telah dihapus dari keranjang.");
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
        
        // Semua hub aktif yang punya baris stok untuk produk ini (termasuk stok 0)
        $stocks = WarehouseStock::with(['warehouse.province', 'warehouse.regency'])
            ->where('product_id', $product->id)
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
