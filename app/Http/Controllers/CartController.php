<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Buyer\AddressController;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Support\ShopFulfillment;
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

    /**
     * @param  \Illuminate\Support\Collection<int, Cart>  $carts
     */
    protected function calculateCartsTotalWeightGrams($carts): int
    {
        return (int) $carts->sum(function (Cart $cart) {
            $weightPerUnit = (int) ($cart->product->weight ?? 500);

            return $weightPerUnit * (int) $cart->quantity;
        });
    }

    protected function formatTotalWeightGrams(int $grams): string
    {
        return number_format($grams / 1000, 1).' kg';
    }

    /**
     * Terapkan alamat belanja dari profil user jika ada (opsional, tidak memblokir belanja).
     */
    protected function tryApplyShoppingAddressFromUser(): void
    {
        if (! Auth::check()) {
            return;
        }

        if (session()->has('selected_shipping_address_id') && session()->has('selected_hub_id')) {
            return;
        }

        $address = null;
        if (session('selected_shipping_address_id')) {
            $address = Address::where('user_id', Auth::id())
                ->where('id', session('selected_shipping_address_id'))
                ->first();
        }
        if (! $address) {
            $address = Address::where('user_id', Auth::id())->where('is_default', true)->first();
        }
        if (! $address) {
            $address = Address::where('user_id', Auth::id())->first();
        }

        if ($address) {
            app(AddressController::class)->applyAddressForShopping($address);
        }
    }

    /**
     * Pastikan session punya hub pengirim; fallback ke hub aktif pertama jika belum ada alamat.
     */
    protected function ensureShoppingHubInSession(?string $warehouseIdFromRequest = null): void
    {
        if (session()->has('selected_hub_id')) {
            return;
        }

        if ($warehouseIdFromRequest) {
            $warehouse = Warehouse::query()
                ->where('is_active', true)
                ->where(function ($q) use ($warehouseIdFromRequest) {
                    $q->where('id', $warehouseIdFromRequest)
                        ->orWhere('slug', $warehouseIdFromRequest);
                })
                ->first();

            if ($warehouse) {
                session([
                    'selected_hub_id' => $warehouse->id,
                    'selected_hub_name' => $warehouse->name,
                    'selected_hub_slug' => $warehouse->slug,
                ]);

                return;
            }
        }

        $excludeOwn = Auth::user()?->distributorShoppingExcludedWarehouseId();
        $query = Warehouse::where('is_active', true)->orderBy('name');
        if ($excludeOwn) {
            $query->where('id', '!=', $excludeOwn);
        }

        $fallbackHub = $query->first();
        if ($fallbackHub) {
            session([
                'selected_hub_id' => $fallbackHub->id,
                'selected_hub_name' => $fallbackHub->name,
                'selected_hub_slug' => $fallbackHub->slug,
            ]);
        }
    }

    protected function wantsJsonCartUpdate(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    public function index()
    {
        $carts = $this->currentRegularCarts();

        // Get the warehouse for this cart (all items should be from same warehouse)
        $cartWarehouse = $carts->first()?->warehouse;

        if ($cartWarehouse) {
            $productIds = $carts->pluck('product_id')->toArray();
            $stocks = WarehouseStock::where('warehouse_id', $cartWarehouse->id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($carts as $cart) {
                $cart->setRelation('warehouseStock', $stocks->get($cart->product_id));
            }
        }

        $total = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $totalWeight = $this->calculateCartsTotalWeightGrams($carts);
        $totalWeightFormatted = $this->formatTotalWeightGrams($totalWeight);

        return view('cart.index', compact('carts', 'total', 'cartWarehouse', 'totalWeight', 'totalWeightFormatted'));
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

        // Disable stock check against a specific hub while shopping, will be checked during checkout

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

        $totalWeight = $this->calculateCartsTotalWeightGrams($carts);

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
            'total_weight' => $totalWeight,
            'total_weight_formatted' => $this->formatTotalWeightGrams($totalWeight),
            'mini_cart_html' => view('themes.nest.partials.mini-cart')->render(),
        ]);
    }

    protected function cartDeleteJsonResponse(string $message): \Illuminate\Http\JsonResponse
    {
        $carts = $this->currentRegularCarts();
        $total = $carts->sum(function ($c) {
            return $c->product->price * $c->quantity;
        });

        $totalWeight = $this->calculateCartsTotalWeightGrams($carts);
        $cartCountSum = (int) $carts->sum('quantity');
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_count' => $cartCountSum,
            'cart_total' => (float) $total,
            'cart_total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
            'total_weight' => $totalWeight,
            'total_weight_formatted' => $this->formatTotalWeightGrams($totalWeight),
            'mini_cart_html' => view('themes.nest.partials.mini-cart')->render(),
            'is_empty' => $carts->isEmpty(),
        ]);
    }

    public function destroy(Request $request, Cart $cart)
    {
        // Check ownership
        if (Auth::check() && $cart->user_id !== Auth::id()) {
            abort(403);
        }
        if (!Auth::check() && $cart->session_id !== session()->getId()) {
            abort(403);
        }

        $cart->delete();
        
        if ($this->wantsJsonCartUpdate($request)) {
            return $this->cartDeleteJsonResponse('Item dihapus dari keranjang.');
        }

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function bulkDelete(Request $request)
    {
        $cartIds = $request->input('cart_ids');

        if (empty($cartIds) || !is_array($cartIds)) {
            return back()->with('error', 'Tidak ada item yang dipilih untuk dihapus.');
        }

        $query = Cart::whereIn('id', $cartIds);
        
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $query->delete();

        if ($this->wantsJsonCartUpdate($request)) {
            return $this->cartDeleteJsonResponse('Item terpilih berhasil dihapus dari keranjang.');
        }

        return back()->with('success', 'Item terpilih berhasil dihapus dari keranjang.');
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
            'input' => $request->only(['quantity', 'uom']),
        ]);

        if (!Auth::check()) {
            return $request->ajax()
                ? response()->json(['error' => 'Silakan masuk terlebih dahulu untuk belanja.'], 401)
                : redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk belanja.');
        }

        $this->tryApplyShoppingAddressFromUser();
        $this->ensureShoppingHubInSession($request->input('warehouse_id'));

        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'uom' => ['nullable', Rule::in(['base', 'large'])],
            ]);
        } catch (ValidationException $e) {
            $this->logCartStore('store: validation failed', [
                'errors' => $e->errors(),
                'product_id' => $product->id,
            ]);
            throw $e;
        }

        if ((float) $product->price <= 0) {
            return $request->ajax()
                ? response()->json(['error' => 'Produk ini belum memiliki harga.'], 422)
                : back()->with('error', 'Produk ini belum memiliki harga.');
        }

        // Determine default UOM based on user role
        $isDistributor = auth()->check() && auth()->user()->isDistributor();
        $defaultUom = $isDistributor ? 'large' : 'base';
        $uom = $request->input('uom', $defaultUom);

        // Force distributors to buy in large unit if product supports it
        if ($isDistributor && $product->hasDualUnitOrdering()) {
            $uom = 'large';
        }

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

        $sessionWarehouseId = session('selected_hub_id');
        $warehouse = Warehouse::where('id', $sessionWarehouseId)
            ->orWhere('slug', $sessionWarehouseId)
            ->first();

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
            'warehouse_id' => $warehouse ? $warehouse->id : null,
            'response' => $request->ajax() ? 'json' : 'redirect_back',
        ]);

        if ($request->ajax()) {
            $cartCount = Auth::check() 
                ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->sum('quantity')
                : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity');

            $currentCart = Auth::check()
                ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->where('product_id', $product->id)->first()
                : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->where('product_id', $product->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Produk "' . $product->display_name . '" berhasil ditambahkan.',
                'cart_count' => $cartCount,
                'line' => [
                    'quantity_input' => $currentCart ? $currentCart->cartQuantityInputValue() : 1,
                ],
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
        ?Warehouse $warehouse,
        int $requestedBaseQty,
        bool $capToStock = false,
        ?string $orderUom = null,
        ?int $quantityOrdered = null,
        ?string $cartOwnerUserId = null,
        ?string $guestSessionId = null
    ): array {
        $warehouseId = $warehouse ? $warehouse->id : null;

        $incomingUom = ($orderUom === 'large') ? 'large' : 'base';
        $effectiveUserId = $cartOwnerUserId ?? (Auth::check() ? Auth::id() : null);
        $effectiveSessionId = ($effectiveUserId === null)
            ? ($guestSessionId ?? session()->getId())
            : null;

        if ($effectiveUserId !== null) {
            $cartQuery = Cart::where('user_id', $effectiveUserId)
                ->where('product_id', $product->id)
                ->where('cart_type', 'regular');
                
            if ($warehouseId) {
                $cartQuery->where('warehouse_id', $warehouseId);
            } else {
                $cartQuery->whereNull('warehouse_id');
            }
            $cart = $cartQuery->first();
        } else {
            $cartQuery = Cart::where('session_id', $effectiveSessionId)
                ->where('product_id', $product->id)
                ->where('cart_type', 'regular');
                
            if ($warehouseId) {
                $cartQuery->where('warehouse_id', $warehouseId);
            } else {
                $cartQuery->whereNull('warehouse_id');
            }
            $cart = $cartQuery->first();
        }

        $existingQty = $cart ? (int) $cart->quantity : 0;
        
        // Disable stock checking during add to cart (it will be checked at checkout)
        $availableStock = PHP_INT_MAX; 
        
        $room = max(0, $availableStock - $existingQty);
        $toAdd = $capToStock ? min($requestedBaseQty, $room) : $requestedBaseQty;

        if ($toAdd < 1) {
            return $capToStock
                ? ['ok' => true, 'error' => null, 'added' => 0]
                : ['ok' => false, 'error' => 'Jumlah tidak valid.', 'added' => 0];
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
    public function clear(Request $request)
    {
        if (Auth::check()) {
            $query = Cart::where('user_id', Auth::id())
                ->where('cart_type', 'regular');
        } else {
            $query = Cart::where('session_id', session()->getId())
                ->where('cart_type', 'regular');
        }
        $query->delete();

        if ($this->wantsJsonCartUpdate($request)) {
            return $this->cartDeleteJsonResponse('Keranjang belanja berhasil dikosongkan.');
        }

        return back()->with('success', 'Keranjang belanja berhasil dikosongkan.');
    }

    /**
     * Remove items that are out of stock in their respective warehouse
     */
    public function removeOutOfStock()
    {
        if (ShopFulfillment::assumeStockReady()) {
            return back()->with('success', 'Validasi stok dinonaktifkan; tidak ada item yang dihapus.');
        }

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

                $excludeOwn = Auth::user()?->distributorShoppingExcludedWarehouseId();
                if ($excludeOwn && (string) $warehouse->id === $excludeOwn) {
                    return response()->json([
                        'error' => 'Hub ini tidak tersedia untuk pembelian sebagai distributor.',
                        'stocks' => [],
                    ], 422);
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

        if ($excludeOwn = Auth::user()?->distributorShoppingExcludedWarehouseId()) {
            $stocks = $stocks->filter(fn ($s) => (string) $s->warehouse_id !== $excludeOwn)->values();
        }

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

    public function updateQuantityByProduct(Request $request, Product $product)
    {
        $action = $request->input('action');
        $warehouseId = session('selected_hub_id');

        $cart = Auth::check()
            ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->where('product_id', $product->id)->first()
            : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->where('product_id', $product->id)->first();

        if ($request->filled('quantity') && ! $request->has('action')) {
            $newOrd = max(0, (int) preg_replace('/\D+/', '', (string) $request->input('quantity')));

            if (! $cart) {
                if ($newOrd <= 0) {
                    return response()->json(['success' => false, 'message' => 'Produk tidak ada di keranjang.'], 404);
                }

                $request->merge(['quantity' => $newOrd]);

                return $this->store($request, $product);
            }
        } elseif ($action === 'plus') {
            if (! $cart) {
                return $this->store($request, $product);
            }

            $currentOrd = (int) ($cart->quantity_ordered ?? 1);
            $newOrd = $currentOrd + 1;
        } elseif ($action === 'minus') {
            if (! $cart) {
                return response()->json(['success' => false, 'message' => 'Produk tidak ada di keranjang.'], 404);
            }

            $currentOrd = (int) ($cart->quantity_ordered ?? 1);
            $newOrd = $currentOrd - 1;
        } else {
            return response()->json(['success' => false, 'message' => 'Aksi tidak valid.'], 422);
        }

        $uom = $cart->order_uom ?: 'base';

        if ($newOrd <= 0) {
            $cart->delete();
            $cartCount = Auth::check() 
                ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->sum('quantity')
                : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity');

            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang.',
                'cart_count' => (int) $cartCount,
                'line' => ['quantity_input' => 0],
                'mini_cart_html' => view('themes.nest.partials.mini-cart')->render()
            ]);
        }

        $baseNeeded = $product->orderedQuantityToBase($newOrd, $uom);

        if (! ShopFulfillment::assumeStockReady()) {
            $stock = $product->warehouseStocks()
                ->where('warehouse_id', $cart->warehouse_id)
                ->first();

            if (! $stock || $stock->stock < $baseNeeded) {
                return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi.'], 422);
            }
        }

        $cart->quantity = $baseNeeded;
        $cart->quantity_ordered = $newOrd;
        $cart->save();

        $cartCount = Auth::check() 
            ? Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->sum('quantity')
            : Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Keranjang diperbarui.',
            'cart_count' => (int) $cartCount,
            'line' => ['quantity_input' => $newOrd],
            'mini_cart_html' => view('themes.nest.partials.mini-cart')->render()
        ]);
    }
}
