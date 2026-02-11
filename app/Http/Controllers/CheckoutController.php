<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $rajaOngkir;

    public function __construct(\App\Services\RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
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

        $expeditions = Expedition::where('is_active', true)->get();
        $defaultExpedition = $expeditions->first();

        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district'])
            ->orderByDesc('is_default')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
    
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

    if ($defaultAddress && $sourceWarehouse && $sourceWarehouse->district_id && $expeditions->count() > 0) {
        $courierCodes = $expeditions->pluck('code')->implode(':');
        
        \Log::info('Calling RajaOngkir API:', [
            'origin' => $sourceWarehouse->district_id,
            'destination' => $defaultAddress->district_id,
            'weight' => $totalWeight,
            'courier' => $courierCodes,
        ]);
        
        $costResult = $this->rajaOngkir->calculateCost(
            $sourceWarehouse->district_id,
            $defaultAddress->district_id,
            $totalWeight,
            $courierCodes
        );

        \Log::info('RajaOngkir Response:', [
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

    \Log::info('=== END CHECKOUT DEBUG ===');

    $total = $subtotal + $shippingCost;

    return view('checkout.index', compact(
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
        'allShippingServices',
        'sourceWarehouse'
    ));
}

    public function calculateShipping(Request $request)
    {
        $address = Address::find($request->address_id);
        
        if (!$address || $address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Alamat tidak valid'], 400);
        }

        $carts = Cart::with(['product', 'warehouse'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });
        
        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $sourceWarehouse = $carts->first()?->warehouse;
        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
            return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }

        $expedition = Expedition::find($request->expedition_id);
        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak ditemukan'], 400);
        }

        $costResult = $this->rajaOngkir->calculateCost(
            $sourceWarehouse->district_id,
            $address->district_id,
            $totalWeight,
            $expedition->code
        );

        $shippingCost = 0;
        $serviceName = $request->service_code;
        $estimatedDelivery = '-';

        \Log::info('=== CALCULATE SHIPPING DEBUG ===');
        \Log::info('Request params:', [
            'expedition_id' => $request->expedition_id,
            'expedition_code' => $expedition->code,
            'service_code' => $request->service_code,
        ]);
        \Log::info('RajaOngkir response:', [
            'has_data' => isset($costResult['data']),
            'data_count' => isset($costResult['data']) ? count($costResult['data']) : 0,
        ]);

        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array, find matching service
            foreach ($costResult['data'] as $service) {
                \Log::info('Checking service:', [
                    'service_code' => $service['code'],
                    'service_name' => $service['service'],
                    'matches_expedition' => $service['code'] === $expedition->code,
                    'matches_service' => $service['service'] === $request->service_code,
                ]);
                
                if ($service['code'] === $expedition->code && $service['service'] === $request->service_code) {
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
        
        \Log::info('Final shipping cost:', ['cost' => $shippingCost]);
        \Log::info('=== END CALCULATE SHIPPING DEBUG ===');

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
            'estimated_delivery' => $estimatedDelivery,
            'address' => [
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
        $expedition = Expedition::find($request->expedition_id);
        
        if (!$expedition) {
            return response()->json(['error' => 'Ekspedisi tidak valid'], 400);
        }

        $address = Address::find($request->address_id);
        if (!$address) {
            return response()->json(['error' => 'Alamat tidak ditemukan'], 400);
        }

        $carts = Cart::with(['product', 'warehouse'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $sourceWarehouse = $carts->first()?->warehouse;
        if (!$sourceWarehouse || !$sourceWarehouse->district_id) {
             return response()->json(['error' => 'Data hub pengirim tidak lengkap'], 400);
        }
        
        $costResult = $this->rajaOngkir->calculateCost(
            $sourceWarehouse->district_id,
            $address->district_id,
            $totalWeight,
            $expedition->code
        );

        $services = [];
        if ($costResult && isset($costResult['data']) && !empty($costResult['data'])) {
            // RajaOngkir returns flat array, filter by expedition code
            foreach ($costResult['data'] as $service) {
                if ($service['code'] === $expedition->code) {
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

        return response()->json([
            'expedition' => [
                'id' => $expedition->id,
                'code' => $expedition->code,
                'name' => $expedition->name,
            ],
            'services' => $services,
        ]);
    }

    // Hardcoded logic removed in favor of RajaOngkirService

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'expedition_id' => 'required|exists:expeditions,id',
            'expedition_service' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Verify address belongs to user
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

        $carts = Cart::with(['product', 'warehouse'])
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
            $costResult = $this->rajaOngkir->calculateCost(
                $sourceWarehouse->district_id,
                $address->district_id,
                $totalWeight,
                $expedition->code
            );

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
                 // Fallback or error if shipping cost calculation fails at store time
                 \Log::error('Store: Gagal menghitung ongkos kirim. Shipping cost is 0 or less.');
                 throw new \Exception('Gagal menghitung ongkos kirim. Silakan pilih layanan pengiriman kembali.');
            }
            $total = $subtotal + $shippingCost;

            // Build full shipping address string for record
            $shippingAddressText = $address->recipient_name . "\n" .
                $address->phone . "\n" .
                $address->address_detail . "\n" .
                ($address->district ? 'Kec. ' . $address->district->name . ', ' : '') .
                ($address->regency ? $address->regency->name . ', ' : '') .
                ($address->province ? $address->province->name : '') .
                ($address->postal_code ? ' ' . $address->postal_code : '');

            // Calculate points for DRiiPPreneur
            $pointsEarned = 0;
            $user = Auth::user();
            if ($user->isDriippreneurApproved()) {
                $pointRate = \App\Models\Setting::get('driippreneur_point_rate', 1000);
                $totalItems = $carts->sum('quantity');
                $pointsEarned = (int)$pointRate * $totalItems;
            }

            // Track affiliate referral and award points
            $affiliateId = session('affiliate_id');
            $affiliatePoints = 0;
            
            if ($affiliateId && $affiliateId !== Auth::id()) {
                $affiliate = User::find($affiliateId);
                
                if ($affiliate) {
                    // Award 1 point per product purchased
                    $totalProducts = $carts->sum('quantity');
                    $affiliatePoints = $totalProducts;
                    
                    // Add points to affiliate's account
                    $affiliate->increment('points', $affiliatePoints);
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

            $order = Order::create([
                'order_type' => Order::TYPE_REGULAR, // Online order
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'expedition_service' => $request->expedition_service,
                'source_warehouse_id' => $sourceWarehouse->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'shipping_address' => $shippingAddressText,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $request->notes,
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
            if ($request->payment_method === 'xendit') {
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

            DB::commit();

            // Load relationships for notification (after commit to ensure data is saved)
            if (!$order->relationLoaded('address') || !$order->relationLoaded('items') || !$order->relationLoaded('expedition')) {
                $order->refresh();
                $order->load(['address.district', 'address.regency', 'address.province', 'items.product', 'expedition']);
            } else {
                // Ensure address relationships are loaded
                if ($order->address && (!$order->address->relationLoaded('district') || !$order->address->relationLoaded('regency') || !$order->address->relationLoaded('province'))) {
                    $order->address->load(['district', 'regency', 'province']);
                }
            }

            // Send WhatsApp payment notification ONLY after Xendit link is ready (for xendit) or immediately (for manual transfer)
            if ($request->payment_method === 'xendit') {
                // Always try to send payment notification with Xendit link
                // If URL is not available, it will be handled in the helper
                try {
                    \App\Helpers\WACloudHelper::sendPaymentNotification($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the checkout process
                    \Log::error('Failed to send WhatsApp payment notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // For manual transfer, send notification immediately
                try {
                    \App\Helpers\WACloudHelper::sendPaymentNotification($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the checkout process
                    \Log::error('Failed to send WhatsApp payment notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
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
        $order->load('items.product', 'address.district', 'address.regency', 'address.province', 'expedition', 'sourceWarehouse');
        
        // Send thank you notification if payment is already paid
        // This handles the case where user accesses success page after payment
        // For Xendit: webhook should handle this, but we also check here as backup
        // For manual transfer: notification should be sent after checkout, but we check here too
        
        Log::info('Checkout success page accessed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
        ]);
        
        // For Xendit: Check payment status from Xendit API if invoice ID exists
        if ($order->payment_method === 'xendit' && $order->xendit_invoice_id && $order->payment_status !== 'paid') {
            Log::info('Verifying payment status from Xendit API', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_id' => $order->xendit_invoice_id,
                'current_payment_status' => $order->payment_status,
            ]);
            
            try {
                // Verify payment status from Xendit API
                $xenditService = new XenditService();
                $invoiceDetails = $xenditService->getInvoice($order->xendit_invoice_id);
                
                Log::info('Xendit API response received', [
                    'order_id' => $order->id,
                    'invoice_id' => $order->xendit_invoice_id,
                    'has_response' => !empty($invoiceDetails),
                    'invoice_status' => $invoiceDetails['status'] ?? 'unknown',
                ]);
                
                if ($invoiceDetails && isset($invoiceDetails['status']) && $invoiceDetails['status'] === 'PAID') {
                    // Update order status if payment is actually paid
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    
                    // Auto update order status to processing if still pending
                    if ($order->order_status === 'pending') {
                        $order->update(['order_status' => 'processing']);
                    }
                    
                    // Refresh order to get updated status
                    $order->refresh();
                    
                    Log::info('Payment status updated from Xendit API', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_status' => $invoiceDetails['status'],
                        'new_payment_status' => $order->payment_status,
                    ]);
                } else {
                    Log::info('Payment not yet paid in Xendit', [
                        'order_id' => $order->id,
                        'invoice_id' => $order->xendit_invoice_id,
                        'invoice_status' => $invoiceDetails['status'] ?? 'unknown',
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to verify payment status from Xendit', [
                    'order_id' => $order->id,
                    'invoice_id' => $order->xendit_invoice_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('Skipping Xendit verification', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'has_invoice_id' => !empty($order->xendit_invoice_id),
                'payment_status' => $order->payment_status,
            ]);
        }
        
        // Send thank you notification if payment is paid
        if ($order->payment_status === 'paid') {
            try {
                Log::info('Payment is paid, sending thank you notification', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                \App\Helpers\WACloudHelper::sendThankYouNotification($order);
            } catch (\Exception $e) {
                // Log error but don't fail the page load
                Log::error('Failed to send WhatsApp thank you notification on success page', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::info('Payment not yet paid, skipping thank you notification', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
            ]);
        }
        
        return view('checkout.success', compact('order'));
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

        return 'ORD-' . $date . '-' . $newNumber;
    }
}
