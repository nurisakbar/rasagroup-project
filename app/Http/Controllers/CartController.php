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
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Cart>
     */
    protected function currentRegularCarts()
    {
        if (Auth::check()) {
            return Cart::with(['product', 'warehouse'])
                ->where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->get();
        }

        return Cart::with(['product', 'warehouse'])
            ->where('session_id', session()->getId())
            ->where('cart_type', 'regular')
            ->get();
    }

    protected function wantsJsonCartUpdate(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    public function index()
    {
        $carts = $this->currentRegularCarts();

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

        $cart->load('product');
        $product = $cart->product;
        $qtyInput = (int) $request->quantity;
        $newBaseQty = ($cart->showsLargeUnitInCart() && $product && $product->hasDualUnitOrdering())
            ? $product->orderedQuantityToBase($qtyInput, 'large')
            : $qtyInput;

        // Check stock availability if warehouse is set (stok selalu per satuan terkecil)
        if ($cart->warehouse_id) {
            $stock = WarehouseStock::where('warehouse_id', $cart->warehouse_id)
                ->where('product_id', $cart->product_id)
                ->first();

            $availableStock = $stock ? (int) $stock->stock : 0;

            if ($newBaseQty > $availableStock) {
                if ($cart->showsLargeUnitInCart() && $product && $product->hasDualUnitOrdering()) {
                    $per = max(1, $product->unitsPerLargeEffective());
                    $maxLarge = intdiv($availableStock, $per);
                    $msg = "Stock tidak mencukupi. Maksimal {$maxLarge} {$product->large_unit} (tersedia {$availableStock} {$product->unit}).";

                    if ($this->wantsJsonCartUpdate($request)) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }

                    return back()->with('error', $msg);
                }

                $msg = "Stock tidak mencukupi. Tersedia: {$availableStock} unit.";

                if ($this->wantsJsonCartUpdate($request)) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg);
            }
        }

        $cart->quantity = $newBaseQty;
        $cart->syncOrderedMetadataFromBaseQuantity();
        $cart->save();

        if ($this->wantsJsonCartUpdate($request)) {
            return $this->cartQuantityUpdateJsonResponse($cart);
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    protected function cartQuantityUpdateJsonResponse(Cart $cart): \Illuminate\Http\JsonResponse
    {
        $cart->refresh()->load('product');
        $carts = $this->currentRegularCarts();
        $total = $carts->sum(function ($c) {
            return $c->product->price * $c->quantity;
        });

        $product = $cart->product;
        $lineSubtotal = (float) ($product->price * $cart->quantity);
        $cartCountSum = (int) $carts->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Keranjang diperbarui.',
            'cart_count' => $cartCountSum,
            'line' => [
                'id' => $cart->id,
                'quantity_input' => $cart->cartQuantityInputValue(),
                'quantity_unit_label' => $cart->cartQuantityUnitLabel(),
                'display_unit_price' => (float) $cart->displayUnitPrice(),
                'display_unit_price_formatted' => 'Rp '.number_format($cart->displayUnitPrice(), 0, ',', '.'),
                'line_subtotal' => $lineSubtotal,
                'line_subtotal_formatted' => 'Rp '.number_format($lineSubtotal, 0, ',', '.'),
                'quantity_base' => (int) $cart->quantity,
                'shows_base_equiv' => $cart->showsLargeUnitInCart(),
                'base_equiv_formatted' => $cart->showsLargeUnitInCart() && $product
                    ? '(= '.number_format($cart->quantity).' '.$product->unit.')'
                    : null,
            ],
            'cart_total' => (float) $total,
            'cart_total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
            'mini_cart_html' => view('themes.nest.partials.mini-cart')->render(),
        ]);
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

        $merge = $this->mergeRegularCartLine(
            $product,
            $warehouse,
            $baseQuantity,
            false,
            $uom,
            (int) $request->quantity
        );
        if (! $merge['ok']) {
            $this->logCartStore('store: merge failed', [
                'error' => $merge['error'],
                'product_id' => $product->id,
            ]);

            return $request->ajax()
                ? response()->json(['error' => $merge['error']], 422)
                : back()->with('error', $merge['error']);
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
     * Gabungkan baris keranjang reguler (auth/guest) dengan aturan stok & satu warehouse.
     *
     * @param  bool  $capToStock  true: tambahkan min(requested, sisa stok); 0 stok = lewati tanpa error
     * @param  string|null  $orderUom  Satuan input: base|large (default base)
     * @param  int|null  $quantityOrdered  Jumlah menurut satuan input (increment baris ini); null = pakai konversi dari toAdd (basis)
     * @param  string|null  $cartOwnerUserId  Paksa keranjang user (mis. API); null = pakai Auth::id() / sesi
     * @param  string|null  $guestSessionId  Paksa session_id tamu; null = session saat ini
     * @return array{ok: bool, error: ?string, added: int, partial?: bool}
     */
    public function mergeRegularCartLine(
        Product $product,
        Warehouse $warehouse,
        int $requestedBaseQty,
        bool $capToStock = false,
        ?string $orderUom = null,
        ?int $quantityOrdered = null,
        ?string $cartOwnerUserId = null,
        ?string $guestSessionId = null
    ): array {
        $warehouseId = $warehouse->id;

        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->first();

        $availableStock = (int) ($stock ? $stock->stock : 0);
        $unitLabel = $product->unit ?: 'unit';

        $incomingUom = ($orderUom === 'large') ? 'large' : 'base';
        $effectiveUserId = $cartOwnerUserId ?? (Auth::check() ? Auth::id() : null);
        $effectiveSessionId = ($effectiveUserId === null)
            ? ($guestSessionId ?? session()->getId())
            : null;

        if ($effectiveUserId !== null) {
            $existingCart = Cart::with('warehouse')
                ->where('user_id', $effectiveUserId)
                ->where('cart_type', 'regular')
                ->whereNotNull('warehouse_id')
                ->where('warehouse_id', '!=', $warehouseId)
                ->first();

            if ($existingCart) {
                return [
                    'ok' => false,
                    'error' => 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.',
                    'added' => 0,
                ];
            }

            $cart = Cart::where('user_id', $effectiveUserId)
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();
        } else {
            $existingCart = Cart::with('warehouse')
                ->where('session_id', $effectiveSessionId)
                ->where('cart_type', 'regular')
                ->whereNotNull('warehouse_id')
                ->where('warehouse_id', '!=', $warehouseId)
                ->first();

            if ($existingCart) {
                return [
                    'ok' => false,
                    'error' => 'Keranjang Anda memiliki produk dari hub lain (' . $existingCart->warehouse->name . '). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama.',
                    'added' => 0,
                ];
            }

            $cart = Cart::where('session_id', $effectiveSessionId)
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('cart_type', 'regular')
                ->first();
        }

        $existingQty = $cart ? (int) $cart->quantity : 0;
        $room = max(0, $availableStock - $existingQty);
        $toAdd = $capToStock ? min($requestedBaseQty, $room) : $requestedBaseQty;

        if (! $capToStock) {
            if ($toAdd < 1) {
                return ['ok' => false, 'error' => 'Jumlah tidak valid.', 'added' => 0];
            }
            if ($toAdd > $room) {
                if ($existingQty > 0) {
                    return [
                        'ok' => false,
                        'error' => "Total quantity melebihi stock. Tersedia: {$availableStock} unit.",
                        'added' => 0,
                    ];
                }

                return [
                    'ok' => false,
                    'error' => "Stock tidak mencukupi di hub {$warehouse->name}. Tersedia: {$availableStock} {$unitLabel}.",
                    'added' => 0,
                ];
            }
        } elseif ($toAdd < 1) {
            return ['ok' => true, 'error' => null, 'added' => 0];
        }

        if ($cart) {
            [$newUom, $newOrd] = Cart::computeMergedOrderUom(
                $cart->order_uom,
                $cart->quantity_ordered,
                $existingQty,
                $incomingUom,
                $quantityOrdered,
                $toAdd
            );
            $cart->quantity = $existingQty + $toAdd;
            $cart->order_uom = $newUom;
            $cart->quantity_ordered = $newOrd;
            $cart->save();
        } else {
            [$newUom, $newOrd] = $this->initialCartOrderUomPair($incomingUom, $quantityOrdered, $toAdd);
            $payload = [
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'cart_type' => 'regular',
                'quantity' => $toAdd,
                'order_uom' => $newUom,
                'quantity_ordered' => $newOrd,
            ];
            if ($effectiveUserId !== null) {
                Cart::create(array_merge($payload, ['user_id' => $effectiveUserId]));
            } else {
                Cart::create(array_merge($payload, ['session_id' => $effectiveSessionId]));
            }
        }

        return [
            'ok' => true,
            'error' => null,
            'added' => $toAdd,
            'partial' => $capToStock && $toAdd < $requestedBaseQty,
        ];
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    private function initialCartOrderUomPair(string $incomingUom, ?int $quantityOrdered, int $toAddBase): array
    {
        if ($incomingUom === 'large') {
            if ($quantityOrdered === null || $quantityOrdered < 1) {
                return ['base', $toAddBase];
            }

            return ['large', $quantityOrdered];
        }

        return ['base', $quantityOrdered ?? $toAddBase];
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
