<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Jobs\ProcessCheckoutSuccessJob;
use App\Services\XenditService;
use App\Support\QadWsOrderNumberGenerator;
use App\Support\ShopFulfillment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    protected $rajaOngkir;
    protected $ekspedisiku;

    public function __construct(\App\Services\RajaOngkirService $rajaOngkir, \App\Services\EkspedisiKuService $ekspedisiku)
    {
        $this->rajaOngkir = $rajaOngkir;
        $this->ekspedisiku = $ekspedisiku;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        Auth::user()->loadMissing('priceLevel');

        $carts = Cart::with(['product', 'warehouse.province', 'warehouse.regency'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        // Get the source warehouse from cart (all items should be from same warehouse)
        $sourceWarehouse = $carts->first()->warehouse;
        
        // Verify all items are from same warehouse
        $differentWarehouse = $carts->first(function ($cart) use ($sourceWarehouse) {
            return $cart->warehouse_id !== $sourceWarehouse?->id;
        });
        
        if ($differentWarehouse) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang memiliki produk dari hub yang berbeda. Silakan kosongkan keranjang dan pilih produk dari hub yang sama.');
        }

        if (!$sourceWarehouse) {
            return redirect()->route('cart.index')
                ->with('error', 'Hub pengirim tidak ditemukan. Silakan tambahkan produk ke keranjang kembali.');
        }

        // Check if warehouse has complete location data
        if (!$sourceWarehouse->district_id) {
            return redirect()->route('cart.index')
                ->with('error', 'Data lokasi hub pengirim belum lengkap. Silakan hubungi administrator untuk melengkapi data lokasi hub (Provinsi, Kota, dan Kecamatan).');
        }

        // Only show expeditions that are active in DB AND active in EkspedisiKu API
        $apiCourierCodes = $this->activeApiCourierCodes();

        $expeditions = Expedition::where('is_active', true)
            ->whereIn('code', $apiCourierCodes)
            ->get();
        $defaultExpedition = $expeditions->firstWhere('code', 'lion_parcel') ?? $expeditions->first();

        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district'])
            ->orderByDesc('is_default')
            ->get();

        if ($addresses->isEmpty()) {
            session(['checkout_return_after_address' => true]);

            return redirect()->route('buyer.addresses.create', ['origin' => 'checkout'])
                ->with('info', 'Tambahkan alamat pengiriman terlebih dahulu untuk melanjutkan checkout.');
        }

        $defaultAddress = $addresses->where('id', request('address_id'))->first()
            ?? $addresses->firstWhere('is_default', true) 
            ?? $addresses->first();

        // Re-detect best Hub based on default address
        if ($defaultAddress) {
            $syncResult = $this->syncWarehouseByAddress($defaultAddress);
            if ($syncResult && $syncResult['hub_changed']) {
                $sourceWarehouse = $syncResult['warehouse'];
                // Refresh carts
                $carts = Cart::with(['product', 'warehouse.province', 'warehouse.regency'])
                    ->where('user_id', Auth::id())
                    ->where('cart_type', 'regular')
                    ->get();
            }
        }

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $pricing = $this->cartPricingBreakdown(Auth::user(), $carts);
        $retailSubtotal = $pricing['retail_subtotal'];
        $distributorPriceDiscount = $pricing['distributor_price_discount'];
        $subtotal = $pricing['subtotal_after_distributor'];
        $priceLevelName = $pricing['price_level_name'];
        $showDistributorPricing = $pricing['show_distributor_pricing'];

        // Check if default address has district_id
        if ($defaultAddress && !$defaultAddress->district_id) {
            return redirect()->route('buyer.addresses.index')
                ->with('error', 'Alamat pengiriman belum memiliki data kecamatan. Silakan lengkapi data alamat Anda terlebih dahulu.');
        }
    
    // Calculate shipping cost based on default address and expedition
    $shippingCost = 0;
    $allShippingServices = [];
    $defaultService = null;

    // Debug logging
    /*
    \Log::info('=== CHECKOUT DEBUG ===');
    \Log::info('Source Warehouse:', [
        'id' => $sourceWarehouse->id,
        'name' => $sourceWarehouse->name,
        'district_id' => $sourceWarehouse->district_id,
        'province_id' => $sourceWarehouse->province_id,
        'regency_id' => $sourceWarehouse->regency_id,
    ]);
    
    if ($defaultAddress) {
        \Log::info('Default Address:', [
            'id' => $defaultAddress->id,
            'recipient' => $defaultAddress->recipient_name,
            'district_id' => $defaultAddress->district_id,
            'province_id' => $defaultAddress->province_id,
            'regency_id' => $defaultAddress->regency_id,
        ]);
    } else {
        \Log::warning('No default address found');
    }
    
    \Log::info('Total Weight:', ['weight' => $totalWeight]);
    \Log::info('Expeditions:', ['count' => $expeditions->count(), 'codes' => $expeditions->pluck('code')->toArray()]);
    */

    if ($defaultAddress && $sourceWarehouse && $sourceWarehouse->district_id && $expeditions->count() > 0) {
        $courierCodes = $expeditions->pluck('code')->implode(':');
        
        \Log::info('Calling Shipping Service API:', [
            'origin' => $sourceWarehouse->district_id,
            'destination' => $defaultAddress->district_id,
            'weight' => $totalWeight,
            'courier' => $defaultExpedition->code,
        ]);
        
        $costResult = $this->resolveShippingCost(
            $defaultExpedition,
            $sourceWarehouse,
            $defaultAddress,
            $totalWeight
        );

        \Log::info('Shipping Response:', [
            'courier' => $defaultExpedition->code,
            'has_data' => isset($costResult['data']),
            'data_count' => isset($costResult['data']) ? count($costResult['data']) : 0,
            'full_response' => $costResult,
        ]);

        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array of services, filter by first expedition code
            $firstExpCode = $defaultExpedition->code;
            \Log::info('Looking for services for expedition:', ['code' => $firstExpCode]);
            
            $matchCount = 0;
            foreach ($costResult['data'] as $service) {
                \Log::info('Checking service:', [
                    'code' => $service['code'],
                    'name' => $service['name'] ?? 'N/A',
                    'service' => $service['service'] ?? 'N/A',
                    'cost' => $service['cost'] ?? 'N/A',
                ]);
                
                // Match by courier code
                if ($service['code'] === $firstExpCode) {
                    \Log::info('Found matching service for expedition');
                    
                    $item = [
                        'code' => $service['service'],
                        'name' => $service['description'],
                        'cost' => $service['cost'],
                        'cost_formatted' => 'Rp ' . number_format($service['cost'], 0, ',', '.'),
                        'estimated_days' => $this->formatEstimatedDelivery($service['etd'] ?? null, $firstExpCode)
                    ];
                    $allShippingServices[] = $item;
                    
                    \Log::info('Added service:', $item);
                    
                    // Set first matching service as default
                    if ($matchCount === 0) {
                        $defaultService = $item;
                        $shippingCost = $item['cost'];
                        \Log::info('Set as default service');
                    }
                    $matchCount++;
                }
            }
            
            \Log::info('Total services found:', ['count' => count($allShippingServices)]);
        } else {
            \Log::warning('No cost data returned from RajaOngkir');
        }
    } else {
        \Log::warning('Skipping shipping calculation:', [
            'has_address' => (bool)$defaultAddress,
            'has_warehouse' => (bool)$sourceWarehouse,
            'warehouse_has_district' => $sourceWarehouse ? (bool)$sourceWarehouse->district_id : false,
            'expeditions_count' => $expeditions->count(),
        ]);
    }

    // \Log::info('=== END CHECKOUT DEBUG ===');
    
    // Calculate discount
    $totalQuantity = $carts->sum('quantity');
    $applicableDiscount = \App\Models\DiscountTier::getApplicableDiscount($totalQuantity);
    $discountAmount = 0;
    $discountPercent = 0;
    if ($applicableDiscount) {
        $discountAmount = ($subtotal * $applicableDiscount->discount_percent) / 100;
        $discountPercent = $applicableDiscount->discount_percent;
    }

    $total = $subtotal - $discountAmount + $shippingCost;

    // Track affiliate
    $affiliate = null;
    $affiliateId = session('affiliate_id');
    if ($affiliateId && $affiliateId !== Auth::id()) {
        $affiliate = User::find($affiliateId);
    }

    return view('checkout.index', compact(
        'carts', 
        'subtotal', 
        'retailSubtotal',
        'distributorPriceDiscount',
        'priceLevelName',
        'showDistributorPricing',
        'shippingCost', 
        'discountAmount',
        'discountPercent',
        'total', 
        'addresses', 
        'defaultAddress',
        'totalWeight',
        'expeditions',
        'defaultExpedition',
        'defaultService',
        'allShippingServices',
        'sourceWarehouse',
        'affiliate'
    ));
}

    public function calculateShipping(Request $request)
    {
        $address = Address::with(['district', 'regency', 'province'])->find($request->address_id);
        
        if (!$address || $address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Alamat tidak valid'], 400);
        }

        $carts = Cart::with(['product', 'warehouse.district', 'warehouse.regency', 'warehouse.province'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();

        // Re-detect best Hub based on selected address
        $syncResult = $this->syncWarehouseByAddress($address) ?? [];
        if (!empty($syncResult['hub_changed'])) {
            $carts = Cart::with(['product', 'warehouse.district', 'warehouse.regency', 'warehouse.province'])
                ->where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->get();
        }

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        Auth::user()->loadMissing('priceLevel');
        $pricing = $this->cartPricingBreakdown(Auth::user(), $carts);
        $retailSubtotal = $pricing['retail_subtotal'];
        $distributorPriceDiscount = $pricing['distributor_price_discount'];
        $subtotal = $pricing['subtotal_after_distributor'];
        $showDistributorPricing = $pricing['show_distributor_pricing'];
        $priceLevelName = $pricing['price_level_name'];

        $sourceWarehouse = $syncResult['warehouse'] ?? $carts->first()?->warehouse;
        $hubChanged = $syncResult['hub_changed'] ?? false;
        $stockWarnings = $syncResult['stock_warnings'] ?? [];

        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
            return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }

        $expedition = $this->findAvailableExpedition($request->expedition_id);
        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak ditemukan atau tidak tersedia'], 400);
        }

        $costResult = $this->resolveShippingCost(
            $expedition,
            $sourceWarehouse,
            $address,
            $totalWeight
        );

        $shippingCost = 0;
        $serviceName = $request->service_code;
        $estimatedDelivery = '-';

        // \Log::info('=== CALCULATE SHIPPING DEBUG ===');
        \Log::info('Request params:', [
            'expedition_id' => $request->expedition_id,
            'expedition_code' => $expedition->code,
            'service_code' => $request->service_code,
        ]);
        \Log::info('Shipping response:', [
            'expedition' => $expedition->code,
            'has_data' => isset($costResult['data']),
            'data_count' => isset($costResult['data']) ? count($costResult['data']) : 0,
            'data' => $costResult['data'] ?? [],
        ]);

        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array, find matching service
            foreach ($costResult['data'] as $service) {
                \Log::info('Checking service:', [
                    'service_code' => $service['service'] ?? null,
                    'service_name' => $service['description'] ?? null,
                    'matches_expedition' => ($service['code'] ?? '') === $expedition->code,
                    'matches_service' => ($service['service'] ?? '') === $request->service_code,
                ]);
                
                if (($service['code'] ?? '') === $expedition->code && ($service['service'] ?? '') === $request->service_code) {
                    $shippingCost = $service['cost'];
                    $serviceName = $service['description'];
                    $estimatedDelivery = $this->formatEstimatedDelivery($service['etd'] ?? null, $expedition->code);
                    \Log::info('MATCH FOUND!', [
                        'cost' => $shippingCost,
                        'name' => $serviceName,
                        'etd' => $estimatedDelivery,
                    ]);
                    break;
                }
            }
        }

        // No fallback for shipping cost anymore, we want real API data
        
        \Log::info('Final shipping cost:', ['cost' => $shippingCost]);

        // Calculate discount
        $totalQuantity = $carts->sum('quantity');
        $applicableDiscount = \App\Models\DiscountTier::getApplicableDiscount($totalQuantity);
        $discountAmount = 0;
        $discountPercent = 0;
        if ($applicableDiscount) {
            $discountAmount = ($subtotal * $applicableDiscount->discount_percent) / 100;
            $discountPercent = $applicableDiscount->discount_percent;
        }

        $total = $subtotal - $discountAmount + $shippingCost;

        return response()->json([
            'shipping_cost' => $shippingCost,
            'shipping_cost_formatted' => 'Rp ' . number_format($shippingCost, 0, ',', '.'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'retail_subtotal' => $retailSubtotal,
            'retail_subtotal_formatted' => 'Rp ' . number_format($retailSubtotal, 0, ',', '.'),
            'distributor_price_discount' => $distributorPriceDiscount,
            'distributor_price_discount_formatted' => 'Rp ' . number_format($distributorPriceDiscount, 0, ',', '.'),
            'show_distributor_pricing' => $showDistributorPricing,
            'price_level_name' => $priceLevelName,
            'discount_amount' => $discountAmount,
            'discount_amount_formatted' => 'Rp ' . number_format($discountAmount, 0, ',', '.'),
            'discount_percent' => $discountPercent,
            'total' => $total,
            'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            'total_weight' => $totalWeight,
            'total_weight_formatted' => number_format($totalWeight / 1000, 1) . ' kg',
            'service_name' => $serviceName,
            'estimated_delivery' => $estimatedDelivery,
            'hub_changed' => $hubChanged,
            'stock_warnings' => $stockWarnings,
            'address' => [
                'id' => $address->id,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'full_address' => $address->full_address,
            ],
            'warehouse' => $sourceWarehouse ? [
                'id' => $sourceWarehouse->id,
                'name' => $sourceWarehouse->name,
                'location' => $sourceWarehouse->full_location,
            ] : null,
        ]);
    }

    public function getExpeditionServices(Request $request)
    {
        Log::debug('[checkout.expedition-services] request', [
            'user_id' => Auth::id(),
            'expedition_id' => $request->expedition_id,
            'address_id' => $request->address_id,
            'query' => $request->query(),
        ]);

        $expedition = $this->findAvailableExpedition($request->expedition_id);

        if (!$expedition) {
            Log::debug('[checkout.expedition-services] expedition not found', [
                'expedition_id' => $request->expedition_id,
                'active_api_couriers' => $this->activeApiCourierCodes(),
            ]);

            return response()->json(['error' => 'Ekspedisi tidak valid atau tidak tersedia'], 400);
        }

        $address = Address::with(['district', 'regency', 'province'])->find($request->address_id);
        if (!$address) {
            Log::debug('[checkout.expedition-services] address not found', [
                'address_id' => $request->address_id,
            ]);

            return response()->json(['error' => 'Alamat tidak ditemukan'], 400);
        }

        $carts = Cart::with(['product', 'warehouse.district', 'warehouse.regency', 'warehouse.province'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        // Re-detect best Hub based on selected address
        $syncResult = $this->syncWarehouseByAddress($address);
        $sourceWarehouse = $syncResult['warehouse'] ?? $carts->first()?->warehouse;

        Log::debug('[checkout.expedition-services] context', [
            'expedition' => [
                'id' => $expedition->id,
                'code' => $expedition->code,
                'name' => $expedition->name,
            ],
            'address' => [
                'id' => $address->id,
                'district_id' => $address->district_id,
                'regency_id' => $address->regency_id,
                'province_id' => $address->province_id,
                'district' => $address->district?->name,
                'regency' => $address->regency?->name,
            ],
            'warehouse' => $sourceWarehouse ? [
                'id' => $sourceWarehouse->id,
                'name' => $sourceWarehouse->name,
                'district_id' => $sourceWarehouse->district_id,
                'regency_id' => $sourceWarehouse->regency_id,
                'province_id' => $sourceWarehouse->province_id,
            ] : null,
            'total_weight_grams' => $totalWeight,
            'total_weight_kg' => round($totalWeight / 1000, 3),
            'cart_items' => $carts->count(),
            'uses_ekspedisiku' => $this->usesEkspedisiKuRates($expedition->code),
        ]);

        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
            Log::debug('[checkout.expedition-services] warehouse incomplete', [
                'warehouse_id' => $sourceWarehouse?->id,
                'district_id' => $sourceWarehouse?->district_id,
            ]);

             return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }
        
        $costResult = $this->resolveShippingCost(
            $expedition,
            $sourceWarehouse,
            $address,
            $totalWeight
        );

        $services = [];
        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array, filter by expedition code
            foreach ($costResult['data'] as $service) {
                Log::debug('[checkout.expedition-services] checking service row', [
                    'service_code' => $service['code'] ?? null,
                    'service' => $service['service'] ?? null,
                    'description' => $service['description'] ?? null,
                    'cost' => $service['cost'] ?? null,
                    'matches_expedition' => ($service['code'] ?? '') === $expedition->code,
                ]);

                if (($service['code'] ?? '') === $expedition->code) {
                    $services[] = [
                        'code' => $service['service'],
                        'name' => $service['description'],
                        'cost' => $service['cost'],
                        'cost_formatted' => 'Rp ' . number_format($service['cost'], 0, ',', '.'),
                        'estimated_days' => $this->formatEstimatedDelivery($service['etd'] ?? null, $expedition->code),
                    ];
                }
            }
        }

        $responsePayload = [
            'expedition' => [
                'id' => $expedition->id,
                'code' => $expedition->code,
                'name' => $expedition->name,
            ],
            'services' => $services,
            'hub_changed' => $syncResult['hub_changed'] ?? false,
            'stock_warnings' => $syncResult['stock_warnings'] ?? [],
            'warehouse' => $sourceWarehouse ? [
                'id' => $sourceWarehouse->id,
                'name' => $sourceWarehouse->name,
                'location' => $sourceWarehouse->full_location,
            ] : null,
        ];

        Log::debug('[checkout.expedition-services] response', [
            'services_count' => count($services),
            'services' => $services,
            'hub_changed' => $responsePayload['hub_changed'],
            'stock_warnings' => $responsePayload['stock_warnings'],
        ]);

        // Removed dummy services fallback to ensure API data is the only source

        return response()->json($responsePayload);
    }

    // Hardcoded logic removed in favor of RajaOngkirService

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowedPayments = ['xendit', 'faspay', 'manual_transfer'];
        if ($user->isDistributor() && (int) ($user->term_of_payment ?? 0) > 0) {
            $allowedPayments[] = 'term_of_payment';
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'expedition_id' => 'required|exists:expeditions,id',
            'expedition_service' => 'required|string',
            'payment_method' => ['required', 'string', Rule::in($allowedPayments)],
        ]);

        // Verify address belongs to user
        $address = Address::where('id', $request->address_id)
            ->where('user_id', Auth::id())
            ->with(['province', 'regency', 'district'])
            ->first();

        if (!$address) {
            return back()->with('error', 'Alamat tidak valid.');
        }

        // Final sync of hub before order
        $this->syncWarehouseByAddress($address);

        $expedition = $this->findAvailableExpedition($request->expedition_id);
        if (!$expedition) {
            return back()->with('error', 'Ekspedisi tidak valid atau sudah tidak tersedia.');
        }

        $carts = Cart::with(['product', 'warehouse.district', 'warehouse.regency', 'warehouse.province'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        // Get source warehouse from cart
        $sourceWarehouse = $carts->first()->warehouse;
        
        if (!$sourceWarehouse) {
            return back()->with('error', 'Hub pengirim tidak ditemukan.');
        }

        // Verify all items are from same warehouse
        $differentWarehouse = $carts->first(function ($cart) use ($sourceWarehouse) {
            return $cart->warehouse_id !== $sourceWarehouse->id;
        });
        
        if ($differentWarehouse) {
            return back()->with('error', 'Keranjang memiliki produk dari hub yang berbeda.');
        }

        // Check stock availability
        if (! ShopFulfillment::assumeStockReady()) {
            $stockErrors = [];
            
            $usesJubelio = is_array($sourceWarehouse->sync_sources) && in_array('jubelio', $sourceWarehouse->sync_sources);
            $jubelioItems = null;

            if ($usesJubelio && $sourceWarehouse->kode_hub) {
                try {
                    $jubelio = app(\App\Services\JubelioService::class);
                    $token = $jubelio->token();
                    $locationId = $jubelio->findLocationIdByCode($token, $sourceWarehouse->kode_hub);
                    if ($locationId) {
                        $jubelioItems = collect($jubelio->fetchItemsToSell($token, $locationId));
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Checkout stock validation fallback to local', ['error' => $e->getMessage()]);
                }
            }

            foreach ($carts as $cart) {
                $productName = $cart->product->display_name;
                $qty = $cart->quantity;
                $productCode = $cart->product->code;

                if ($usesJubelio && $jubelioItems !== null) {
                    $needle = strtoupper(trim((string) $productCode));
                    $jItem = $jubelioItems->first(function ($item) use ($needle) {
                        return strtoupper(trim((string) ($item['item_code'] ?? ''))) === $needle;
                    });

                    if (!$jItem) {
                        $stockErrors[] = "{$productName}: Produk tidak tersedia/belum di-assign di gudang Jubelio.";
                        continue;
                    }

                    $availableStock = (int) ($jItem['available_qty'] ?? 0);
                    if ($qty > $availableStock) {
                        $stockErrors[] = "{$productName}: Dipesan {$qty}, tersedia {$availableStock} (Jubelio).";
                    }
                } else {
                    $stock = WarehouseStock::where('warehouse_id', $sourceWarehouse->id)
                        ->where('product_id', $cart->product_id)
                        ->first();

                    $availableStock = $stock ? $stock->stock : 0;

                    if ($qty > $availableStock) {
                        $stockErrors[] = "{$productName}: Dipesan {$qty}, tersedia {$availableStock}.";
                    }
                }
            }

            if (! empty($stockErrors)) {
                return back()->with('error', 'Stock tidak mencukupi di hub ' . $sourceWarehouse->name . ":\n" . implode("\n", $stockErrors));
            }
        }

        DB::beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();
            $user->loadMissing('priceLevel');
            $pricing = $this->cartPricingBreakdown($user, $carts);
            $subtotal = $pricing['subtotal_after_distributor'];

            $totalWeight = $carts->sum(function ($cart) {
                return ($cart->product->weight ?? 500) * $cart->quantity;
            });

            $shippingCost = 0;
            \Log::info('Store: calculating shipping cost', [
                'origin' => $sourceWarehouse->district_id,
                'destination' => $address->district_id,
                'weight' => $totalWeight,
                'courier_code' => $expedition->code,
                'service_requested' => $request->expedition_service
            ]);
            $costResult = $this->resolveShippingCost(
                $expedition,
                $sourceWarehouse,
                $address,
                $totalWeight
            );

            $shippingCost = 0;
            $availableServices = [];
            if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
                foreach ($costResult['data'] as $service) {
                    if (($service['code'] ?? '') === $expedition->code) {
                        $availableServices[] = $service['service'];
                        if (($service['service'] ?? '') === $request->expedition_service) {
                            $shippingCost = (float) $service['cost'];
                        }
                    }
                }
            }

            if ($shippingCost <= 0) {
                \Log::error('Store: Gagal menghitung ongkos kirim.', [
                    'expedition' => $expedition->code,
                    'service_requested' => $request->expedition_service,
                    'available_services' => $availableServices,
                ]);

                DB::rollBack();

                $message = 'Gagal menghitung ongkos kirim. Silakan pilih ekspedisi dan layanan pengiriman kembali.';
                if ($availableServices !== []) {
                    $message .= ' Layanan tersedia untuk ' . $expedition->name . ': ' . implode(', ', $availableServices) . '.';
                }

                return back()->withInput()->with('error', $message);
            }
            
            // Calculate discount
            $totalQuantity = $carts->sum('quantity');
            $applicableDiscount = \App\Models\DiscountTier::getApplicableDiscount($totalQuantity);
            $discountAmount = 0;
            $discountPercent = 0;
            if ($applicableDiscount) {
                $discountAmount = ($subtotal * $applicableDiscount->discount_percent) / 100;
                $discountPercent = $applicableDiscount->discount_percent;
            }

            $total = $subtotal - $discountAmount + $shippingCost;

            // Build full shipping address string for record
            $shippingAddressText = $address->recipient_name . "\n" .
                $address->phone . "\n" .
                $address->address_detail . "\n" .
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->regency ? $address->regency->name . ', ' : '') .
                ($address->province ? $address->province->name : '') .
                ($address->postal_code ? ' ' . $address->postal_code : '');

            // Calculate points for DRiiPPreneur based on product reseller_point
            $pointsEarned = 0;
            if ($user->isDriippreneurApproved()) {
                foreach ($carts as $cart) {
                    $pointsEarned += ($cart->product->reseller_point ?? 0) * $cart->quantity;
                }
            }

            // Track affiliate referral
            $affiliateId = session('affiliate_id');
            $affiliatePoints = 0;
            
            if ($affiliateId && $affiliateId !== Auth::id()) {
                $affiliateExists = User::where('id', $affiliateId)->exists();
                
                if ($affiliateExists) {
                    // Calculate 1 point per item purchased
                    $totalItems = $carts->sum('quantity');
                    $affiliatePoints = $totalItems;
                }
            }

            // Prepare items for Xendit invoice
            $xenditItems = [];
            foreach ($carts as $cart) {
                $lineUnit = $user->getProductPrice($cart->product);
                $xenditItems[] = [
                    'name' => $cart->product->display_name,
                    'quantity' => $cart->quantity,
                    'price' => $lineUnit,
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

            // Add discount as item (if any)
            if ($discountAmount > 0) {
                $xenditItems[] = [
                    'name' => 'Potongan Harga (' . $discountPercent . '%)',
                    'quantity' => 1,
                    'price' => -$discountAmount, // Negative amount for discount
                ];
            }

            $orderNotes = $request->notes;
            if ($request->payment_method === 'term_of_payment' && $user->term_of_payment) {
                $totPrefix = 'TOT: pembayaran tempo ' . (int) $user->term_of_payment . ' hari.';
                $orderNotes = trim($totPrefix . (filled($orderNotes) ? ' | ' . $orderNotes : ''));
            }

            $orderType = $user->isDistributor() ? Order::TYPE_DISTRIBUTOR : Order::TYPE_REGULAR;

            $order = Order::create([
                'order_type' => $orderType, // Determine by role
                'order_number' => $orderNumber,
                'qid_sales_order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'expedition_service' => $request->expedition_service,
                'source_warehouse_id' => $sourceWarehouse->id,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'shipping_address' => $shippingAddressText,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $orderNotes,
                'points_earned' => $pointsEarned,
                'points_credited' => false,
                'affiliate_id' => $affiliateId,
                'affiliate_points' => $affiliatePoints,
            ]);
            
            // Load relationships for notification
            $order->load('address');

            foreach ($carts as $cart) {
                $lineUnit = $user->getProductPrice($cart->product);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'order_uom' => $cart->order_uom,
                    'quantity_ordered' => $cart->quantity_ordered,
                    'price' => $lineUnit,
                    'subtotal' => $lineUnit * $cart->quantity,
                ]);

                // Stok lokal tidak dikurangi — asumsi ready; fulfillment via Jubelio/QAD
                if (! ShopFulfillment::assumeStockReady()) {
                    $stock = WarehouseStock::where('warehouse_id', $sourceWarehouse->id)
                        ->where('product_id', $cart->product_id)
                        ->first();

                    if ($stock) {
                        $stock->decrement('stock', $cart->quantity);
                    }
                }
            }

            Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->delete();

            // Handle Faspay/Xendit payment (to get invoice URL)
            $faspayInvoiceUrl = null;
            $xenditInvoiceUrl = null;
            
            Log::info('--- CHECKOUT DEBUG ---', [
                'order_id' => $order->id,
                'request_payment_method' => $request->payment_method,
                'config_active_gateway' => config('services.active_payment_gateway')
            ]);

            if ($request->payment_method === 'term_of_payment') {
                // TOT/TOP: tidak membuat invoice Faspay; pesanan menunggu pembayaran sesuai tempo
                Log::info('Checkout Debug: TOP selected, skipping gateway.');
            } elseif ($request->payment_method === 'xendit' || $request->payment_method === 'faspay') {
                $activeGateway = config('services.active_payment_gateway');
                
                Log::info('Checkout Debug: Entering Gateway block.', [
                    'active_gateway' => $activeGateway
                ]);

                if ($activeGateway === 'xendit') {
                    $xenditService = new \App\Services\XenditService();
                    $customer = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $address->phone ?? $user->phone,
                    ];

                    $invoice = $xenditService->createInvoice($order, $customer, $xenditItems);

                    if ($invoice && isset($invoice['id'])) {
                        $xenditInvoiceId = $invoice['id'];
                        $xenditInvoiceUrl = $invoice['invoice_url'] ?? null;

                        $order->xendit_invoice_id = $xenditInvoiceId;
                        $order->xendit_invoice_url = $xenditInvoiceUrl;
                        $order->save();

                        Log::info('Xendit invoice created and saved to order', [
                            'order_id' => $order->id,
                            'invoice_id' => $xenditInvoiceId,
                        ]);
                    } else {
                        DB::rollBack();
                        Log::error('Failed to create Xendit invoice', ['order_id' => $order->id]);
                        return redirect()->back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi.');
                    }
                } else {
                    $faspayService = new \App\Services\FaspayService();
                    
                    $invoice = $faspayService->createBill($order, $user);

                    if ($invoice && isset($invoice['redirect_url'])) {
                        // Update order with invoice information
                        $order->faspay_bill_no = $invoice['bill_no'] ?? $order->order_number;
                        $order->faspay_redirect_url = $invoice['redirect_url'];
                        $order->save();
                        
                        $faspayInvoiceUrl = $order->faspay_redirect_url;

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
                        return redirect()->back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi atau pastikan kredensial Faspay valid.');
                    }
                }
            } 
            // QAD sync (customer + SO) should only run after payment is PAID/SETTLED.
            // For Xendit, this is handled by the webhook (and success-page backup).
            // This prevents creating QAD customers for users who haven't completed a purchase.

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
                if ($order->address && (! $order->address->relationLoaded('village') || ! $order->address->relationLoaded('district') || ! $order->address->relationLoaded('regency') || ! $order->address->relationLoaded('province'))) {
                    $order->address->load(['village', 'district', 'regency', 'province']);
                }
                $order->loadMissing(['user', 'sourceWarehouse']);
            }

                // Dispatch background job to send payment notification
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'payment');
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_new_order');
            
            // Note: Thank you notification will be sent after payment is successful via Xendit webhook

            Log::info('Checkout Debug: Checking redirect URLs.', [
                'xenditInvoiceUrl' => $xenditInvoiceUrl ?? null,
                'faspayInvoiceUrl' => $faspayInvoiceUrl ?? null,
                'request_payment_method' => $request->payment_method
            ]);

            // Redirect based on generated invoice URL rather than request parameter 
            // (in case frontend sent outdated payment_method due to cached views)
            if (isset($xenditInvoiceUrl) && $xenditInvoiceUrl) {
                // Redirect to Xendit payment page
                Log::info('Checkout Debug: Redirecting to Xendit', ['url' => $xenditInvoiceUrl]);
                return redirect($xenditInvoiceUrl);
            }

            if (isset($faspayInvoiceUrl) && $faspayInvoiceUrl) {
                // Redirect to Faspay payment page
                Log::info('Checkout Debug: Redirecting to Faspay', ['url' => $faspayInvoiceUrl]);
                return redirect($faspayInvoiceUrl);
            }

            Log::info('Checkout Debug: No gateway URL found. Redirecting to success page.');
            return redirect()->route('checkout.success', $order)->with('success', 'Pesanan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
            ]);
        }

        $order->load([
            'user',
            'items.product',
            'address.district',
            'address.regency',
            'address.province',
            'expedition',
            'sourceWarehouse.province',
            'sourceWarehouse.regency',
            'sourceWarehouse.district',
        ]);

        Log::info('Checkout success page accessed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
        ]);

        // Verifikasi Xendit, sync customer/QAD SO, dan WA thank-you setelah response terkirim (tidak menahan loading halaman).
        ProcessCheckoutSuccessJob::dispatch((string) $order->id)->afterResponse();

        return view('checkout.success', compact('order'));
    }

    /**
     * Status pembayaran untuk halaman checkout/success (polling realtime).
     */
    public function successPaymentStatus(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->refresh();

        return response()->json([
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'payment_method' => $order->payment_method,
            'payment_url' => $order->faspay_redirect_url ?? $order->xendit_invoice_url,
        ]);
    }

    private function generateOrderNumber(): string
    {
        return QadWsOrderNumberGenerator::generate();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Cart>  $carts
     * @return array{
     *     retail_subtotal: float,
     *     distributor_price_discount: float,
     *     subtotal_after_distributor: float,
     *     price_level_name: ?string,
     *     show_distributor_pricing: bool
     * }
     */
    private function cartPricingBreakdown(User $user, $carts): array
    {
        $retailSubtotal = (float) $carts->sum(function ($cart) {
            return (float) $cart->product->price * (int) $cart->quantity;
        });

        $user->loadMissing('priceLevel');

        $subtotalAfterDistributor = $retailSubtotal;
        $distributorPriceDiscount = 0.0;
        $priceLevelName = null;

        if ($user->isDistributor() && $user->priceLevel) {
            $priceLevelName = $user->priceLevel->name;
            $subtotalAfterDistributor = (float) $carts->sum(function ($cart) use ($user) {
                return $user->getProductPrice($cart->product) * (int) $cart->quantity;
            });
            $distributorPriceDiscount = max(0.0, $retailSubtotal - $subtotalAfterDistributor);
        }

        return [
            'retail_subtotal' => $retailSubtotal,
            'distributor_price_discount' => $distributorPriceDiscount,
            'subtotal_after_distributor' => $subtotalAfterDistributor,
            'price_level_name' => $priceLevelName,
            'show_distributor_pricing' => $user->isDistributor()
                && $user->priceLevel !== null
                && $distributorPriceDiscount > 0,
        ];
    }

    private function syncWarehouseByAddress(Address $address): ?array
    {
        $carts = Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->get();
        if ($carts->isEmpty()) {
            return null;
        }

        $currentWarehouseId = $carts->first()->warehouse_id;
        $excludeOwnHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();
        $hubChanged = false;
        $bestHub = null;

        if (ShopFulfillment::autoHubByAddress()) {
            $bestHub = ShopFulfillment::resolveNearestHub($address, $excludeOwnHubId);

            if ($bestHub && $bestHub->id !== $currentWarehouseId) {
                $hubChanged = true;
                Cart::where('user_id', Auth::id())
                    ->where('cart_type', 'regular')
                    ->update(['warehouse_id' => $bestHub->id]);

                session([
                    'selected_hub_id' => $bestHub->id,
                    'selected_hub_name' => $bestHub->name,
                    'selected_hub_slug' => $bestHub->slug,
                ]);
            }
        }

        $currentHub = $bestHub ?: Warehouse::find($currentWarehouseId);

        return [
            'hub_changed' => $hubChanged,
            'warehouse' => $currentHub,
            'stock_warnings' => [],
        ];
    }

    /**
     * @return array{data: array<int, array<string, mixed>>}|null
     */
    private function resolveShippingCost(
        Expedition $expedition,
        Warehouse $sourceWarehouse,
        Address $address,
        float $totalWeightGrams
    ): ?array {
        $cacheKey = sprintf(
            'shipping_cost_%s_%s_%s_%s',
            $expedition->code,
            $sourceWarehouse->id,
            $address->id,
            $totalWeightGrams
        );

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($expedition, $sourceWarehouse, $address, $totalWeightGrams) {
            if ($this->usesEkspedisiKuRates($expedition->code)) {
                return $this->ekspedisiku->calculateCost(
                    $sourceWarehouse->district_id,
                    $address->district_id,
                    max(1, $totalWeightGrams / 1000),
                    $expedition->code,
                    [
                        'warehouse' => $sourceWarehouse,
                        'address' => $address,
                    ]
                );
            }

            return $this->rajaOngkir->calculateCost(
                $sourceWarehouse->district_id,
                $address->district_id,
                $totalWeightGrams,
                $expedition->code
            );
        });
    }

    private function formatEstimatedDelivery(?string $etd, string $expeditionCode): string
    {
        if ($etd !== null && $etd !== '') {
            return $etd.' hari';
        }

        return $expeditionCode === 'lalamove' ? 'Hari yang sama' : '2-3 hari';
    }

    /**
     * Courier codes that are active in EkspedisiKu API (is_active=true).
     *
     * @return array<int, string>
     */
    private function activeApiCourierCodes(): array
    {
        $dbCodes = Expedition::where('is_active', true)
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $courierRes = $this->ekspedisiku->getCouriers();
        if (! isset($courierRes['data']) || ! is_array($courierRes['data'])) {
            return $dbCodes;
        }

        $activeEkspedisiKuCodes = [];
        foreach ($courierRes['data'] as $courier) {
            if (($courier['is_active'] ?? false) === true && ! empty($courier['id'])) {
                $activeEkspedisiKuCodes[] = $courier['id'];
            }
        }

        return array_values(array_intersect($dbCodes, $activeEkspedisiKuCodes));
    }

    private function findAvailableExpedition(?string $expeditionId): ?Expedition
    {
        if (! $expeditionId) {
            return null;
        }

        return Expedition::whereIn('code', $this->activeApiCourierCodes())->find($expeditionId);
    }

    private function usesEkspedisiKuRates(string $code): bool
    {
        return in_array($code, ['lion_parcel', 'lalamove', 'jne', 'sicepat'], true);
    }
}
