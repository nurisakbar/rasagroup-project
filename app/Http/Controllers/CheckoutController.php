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

        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        // Only show expeditions that are active AND available in the EkspedisiKu API
        $courierRes = $this->ekspedisiku->getCouriers();
        $apiCourierCodes = [];
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
            }
        }

        $expeditions = Expedition::where('is_active', true)
            ->whereIn('code', $apiCourierCodes)
            ->get();
        $defaultExpedition = $expeditions->first();

        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district'])
            ->orderByDesc('is_default')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

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

                if (!empty($syncResult['stock_warnings']) && !session()->has('error')) {
                    $warningMessage = "Sumber pengiriman diubah ke {$sourceWarehouse->name} berdasarkan alamat Anda. Namun ada beberapa kendala stok:\n" . implode("\n", $syncResult['stock_warnings']);
                    session()->flash('warning', $warningMessage);
                }
            }
        }

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
        
        if ($defaultExpedition->code === 'lion_parcel') {
            $costResult = $this->ekspedisiku->calculateCost(
                $sourceWarehouse->district_id,
                $defaultAddress->district_id,
                $totalWeight / 1000 // EkspedisiKu expects kg
            );
        } else {
            $costResult = $this->rajaOngkir->calculateCost(
                $sourceWarehouse->district_id,
                $defaultAddress->district_id,
                $totalWeight,
                $defaultExpedition->code
            );
        }

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
                        'estimated_days' => ($service['etd'] ?: '2-3') . ' hari'
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
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });
        
        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        // Re-detect best Hub based on selected address
        $syncResult = $this->syncWarehouseByAddress($address);
        $sourceWarehouse = $syncResult['warehouse'] ?? $carts->first()?->warehouse;
        $hubChanged = $syncResult['hub_changed'] ?? false;
        $stockWarnings = $syncResult['stock_warnings'] ?? [];

        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
            return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }

        $courierRes = $this->ekspedisiku->getCouriers();
        $apiCourierCodes = [];
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
            }
        }

        $expedition = Expedition::whereIn('code', $apiCourierCodes)->find($request->expedition_id);
        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak ditemukan atau tidak tersedia'], 400);
        }

        if ($expedition->code === 'lion_parcel') {
            $costResult = $this->ekspedisiku->calculateCost(
                $sourceWarehouse->district_id,
                $address->district_id,
                $totalWeight / 1000
            );
        } else {
            $costResult = $this->rajaOngkir->calculateCost(
                $sourceWarehouse->district_id,
                $address->district_id,
                $totalWeight,
                $expedition->code
            );
        }

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
                    $estimatedDelivery = ($service['etd'] ?: '2-3') . ' hari';
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
        // \Log::info('=== END CALCULATE SHIPPING DEBUG ===');

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
        $courierRes = $this->ekspedisiku->getCouriers();
        $apiCourierCodes = [];
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
            }
        }

        $expedition = Expedition::whereIn('code', $apiCourierCodes)->find($request->expedition_id);
        
        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak valid atau tidak tersedia'], 400);
        }

        $address = Address::with(['district', 'regency', 'province'])->find($request->address_id);
        if (!$address) {
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

        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
             return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }
        
        if ($expedition->code === 'lion_parcel') {
             $costResult = $this->ekspedisiku->calculateCost(
                 $sourceWarehouse->district_id,
                 $address->district_id,
                 $totalWeight / 1000
             );
        } else {
            $costResult = $this->rajaOngkir->calculateCost(
                $sourceWarehouse->district_id,
                $address->district_id,
                $totalWeight,
                $expedition->code
            );
        }

        $services = [];
        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array, filter by expedition code
            foreach ($costResult['data'] as $service) {
                if (($service['code'] ?? '') === $expedition->code) {
                    $services[] = [
                        'code' => $service['service'],
                        'name' => $service['description'],
                        'cost' => $service['cost'],
                        'cost_formatted' => 'Rp ' . number_format($service['cost'], 0, ',', '.'),
                        'estimated_days' => ($service['etd'] ?: '2-3') . ' hari',
                    ];
                }
            }
        }

        // Removed dummy services fallback to ensure API data is the only source

        return response()->json([
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
        ]);
    }

    // Hardcoded logic removed in favor of RajaOngkirService

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowedPayments = ['xendit', 'manual_transfer'];
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

        $courierRes = $this->ekspedisiku->getCouriers();
        $apiCourierCodes = [];
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
            }
        }

        $expedition = Expedition::whereIn('code', $apiCourierCodes)->find($request->expedition_id);
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
            return back()->with('error', 'Stock tidak mencukupi di hub ' . $sourceWarehouse->name . ":\n" . implode("\n", $stockErrors));
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

            $shippingCost = 0;
            \Log::info('Store: calculating shipping cost', [
                'origin' => $sourceWarehouse->district_id,
                'destination' => $address->district_id,
                'weight' => $totalWeight,
                'courier_code' => $expedition->code,
                'service_requested' => $request->expedition_service
            ]);
            if ($expedition->code === 'lion_parcel') {
                $costResult = $this->ekspedisiku->calculateCost(
                    $sourceWarehouse->district_id,
                    $address->district_id,
                    $totalWeight / 1000
                );
            } else {
                $costResult = $this->rajaOngkir->calculateCost(
                    $sourceWarehouse->district_id,
                    $address->district_id,
                    $totalWeight,
                    $expedition->code
                );
            }


            if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
                // RajaOngkir returns flat array, find matching service
                foreach ($costResult['data'] as $service) {
                    \Log::info('Store: checking service', [
                        'code' => $service['code'],
                        'service' => $service['service'] ?? null,
                    ]);
                    $serviceCode = $service['service'] ?? '';
                    $serviceCourier = $service['code'] ?? '';
                    
                    // Normalize for comparison
                    $normalizedServiceCode = strtoupper(trim($serviceCode));
                    $normalizedRequestService = strtoupper(trim($request->expedition_service));
                    $normalizedCourier = strtolower(trim($serviceCourier));
                    $normalizedExpeditionCode = strtolower(trim($expedition->code));
                    
                    if ($normalizedCourier === $normalizedExpeditionCode && $normalizedServiceCode === $normalizedRequestService) {
                        $shippingCost = $service['cost'];
                        \Log::info('Store: shipping cost matched', ['cost' => $shippingCost]);
                        break;
                    }
                }
            } else {
                \Log::warning('Store: costResult data empty or format unexpected', ['result' => $costResult]);
            }

            if ($shippingCost <= 0) {
                // Fallback for simulation
                if ($request->expedition_service === 'REG') {
                    $shippingCost = 15000;
                } elseif ($request->expedition_service === 'OKE') {
                    $shippingCost = 12000;
                } else {
                    \Log::error('Store: Gagal menghitung ongkos kirim. Shipping cost is 0 or less.');
                    throw new \Exception('Gagal menghitung ongkos kirim. Silakan pilih layanan pengiriman kembali.');
                }
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

            $order = Order::create([
                'order_type' => Order::TYPE_REGULAR, // Online order
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
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'order_uom' => $cart->order_uom,
                    'quantity_ordered' => $cart->quantity_ordered,
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

            Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->delete();

            // Handle Xendit payment first (to get invoice URL)
            $xenditInvoiceUrl = null;
            if ($request->payment_method === 'term_of_payment') {
                // TOT/TOP: tidak membuat invoice Xendit; pesanan menunggu pembayaran sesuai tempo
            } elseif ($request->payment_method === 'xendit') {
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
                    return back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi atau pilih metode pembayaran lain.');
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

            // Redirect based on payment method
            if ($request->payment_method === 'xendit' && $xenditInvoiceUrl) {
                // Redirect to Xendit payment page
                return redirect($xenditInvoiceUrl);
            }

            return redirect()->route('checkout.success', $order)->with('success', 'Pesanan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
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
            'xendit_invoice_url' => $order->xendit_invoice_url,
        ]);
    }

    private function generateOrderNumber(): string
    {
        return QadWsOrderNumberGenerator::generate();
    }

    private function syncWarehouseByAddress(Address $address)
    {
        $carts = Cart::where('user_id', Auth::id())->where('cart_type', 'regular')->get();
        if ($carts->isEmpty()) return null;
        
        $currentWarehouseId = $carts->first()->warehouse_id;
        $bestHub = Warehouse::findBestHubForAddress($address);
        
        $hubChanged = false;
        if ($bestHub && $bestHub->id !== $currentWarehouseId) {
            $hubChanged = true;
            Cart::where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->update(['warehouse_id' => $bestHub->id]);
            
            session(['selected_hub_id' => $bestHub->id]);
        }
        
        // Always check stocks in the (new or current) warehouse
        $stockWarnings = [];
        $currentHub = $bestHub ?: Warehouse::find($currentWarehouseId);
        
        if ($currentHub) {
            $cartsForStock = Cart::with('product')
                ->where('user_id', Auth::id())
                ->where('cart_type', 'regular')
                ->get();

            foreach ($cartsForStock as $item) {
                $stock = WarehouseStock::where('warehouse_id', $currentHub->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if (!$stock || $stock->stock < $item->quantity) {
                    $available = $stock ? $stock->stock : 0;
                    $stockWarnings[] = "Stok {$item->product->name} tidak mencukupi (Tersedia: {$available})";
                }
            }
        }
        
        return [
            'hub_changed' => $hubChanged,
            'warehouse' => $currentHub,
            'stock_warnings' => $stockWarnings,
        ];
    }
}
