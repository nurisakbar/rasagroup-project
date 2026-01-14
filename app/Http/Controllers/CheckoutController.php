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

class CheckoutController extends Controller
{
    // Simulasi tarif ongkir per provinsi (dalam Rupiah per kg)
    private $shippingRates = [
        '11' => 15000,  // Aceh
        '12' => 15000,  // Sumatera Utara
        '13' => 18000,  // Sumatera Barat
        '14' => 20000,  // Riau
        '15' => 22000,  // Jambi
        '16' => 25000,  // Sumatera Selatan
        '17' => 25000,  // Bengkulu
        '18' => 28000,  // Lampung
        '19' => 30000,  // Kepulauan Bangka Belitung
        '21' => 32000,  // Kepulauan Riau
        '31' => 10000,  // DKI Jakarta
        '32' => 12000,  // Jawa Barat
        '33' => 12000,  // Jawa Tengah
        '34' => 10000,  // DI Yogyakarta
        '35' => 12000,  // Jawa Timur
        '36' => 15000,  // Banten
        '51' => 20000,  // Bali
        '52' => 25000,  // Nusa Tenggara Barat
        '53' => 30000,  // Nusa Tenggara Timur
        '61' => 35000,  // Kalimantan Barat
        '62' => 35000,  // Kalimantan Tengah
        '63' => 35000,  // Kalimantan Selatan
        '64' => 35000,  // Kalimantan Timur
        '65' => 35000,  // Kalimantan Utara
        '71' => 40000,  // Sulawesi Utara
        '72' => 40000,  // Sulawesi Tengah
        '73' => 40000,  // Sulawesi Selatan
        '74' => 40000,  // Sulawesi Tenggara
        '75' => 40000,  // Gorontalo
        '76' => 40000,  // Sulawesi Barat
        '81' => 50000,  // Maluku
        '82' => 50000,  // Maluku Utara
        '91' => 55000,  // Papua Barat
        '94' => 55000,  // Papua
    ];

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

        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district', 'village'])
            ->orderByDesc('is_default')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        
        // Calculate total weight
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });
        
        // Get available expeditions
        $expeditions = Expedition::active()->get();
        $defaultExpedition = $expeditions->first();
        $defaultService = $defaultExpedition ? $defaultExpedition->services[0] : null;
        
        // Calculate shipping cost based on default address and expedition
        $shippingCost = 0;
        if ($defaultAddress && $defaultExpedition && $defaultService) {
            $shippingCost = $this->calculateShippingCost(
                $defaultAddress->province_id, 
                $totalWeight, 
                $defaultExpedition->base_cost,
                $defaultService['multiplier']
            );
        }

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
            'sourceWarehouse'
        ));
    }

    public function calculateShipping(Request $request)
    {
        $address = Address::find($request->address_id);
        
        if (!$address || $address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Alamat tidak valid'], 400);
        }

        $carts = Cart::with(['product', 'warehouse.province', 'warehouse.regency'])
            ->where('user_id', Auth::id())
            ->where('cart_type', 'regular')
            ->get();
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });
        
        $subtotal = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        // Get source warehouse from cart
        $sourceWarehouse = $carts->first()?->warehouse;

        // Get expedition and service multipliers
        $expeditionId = $request->expedition_id;
        $serviceCode = $request->service_code;
        
        $expedition = Expedition::find($expeditionId);
        $expeditionMultiplier = $expedition ? $expedition->base_cost : 1.0;
        
        // Find service multiplier
        $serviceMultiplier = 1.0;
        $serviceName = 'Reguler';
        $estDaysMin = $expedition->est_days_min ?? 2;
        $estDaysMax = $expedition->est_days_max ?? 4;
        
        if ($expedition && $serviceCode) {
            foreach ($expedition->services as $service) {
                if ($service['code'] === $serviceCode) {
                    $serviceMultiplier = $service['multiplier'];
                    $serviceName = $service['name'];
                    $estDaysMin = max(1, $expedition->est_days_min + $service['days_add']);
                    $estDaysMax = max(1, $expedition->est_days_max + $service['days_add']);
                    break;
                }
            }
        }
        
        $shippingCost = $this->calculateShippingCost(
            $address->province_id, 
            $totalWeight,
            $expeditionMultiplier,
            $serviceMultiplier
        );

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
            'estimated_delivery' => $estDaysMin === $estDaysMax 
                ? $estDaysMin . ' hari' 
                : $estDaysMin . '-' . $estDaysMax . ' hari',
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
        $carts = Cart::with('product')->where('user_id', Auth::id())->where('cart_type', 'regular')->get();
        
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 500) * $cart->quantity;
        });

        $provinceId = $address ? $address->province_id : '31';
        
        $services = [];
        foreach ($expedition->services as $service) {
            $cost = $this->calculateShippingCost(
                $provinceId,
                $totalWeight,
                $expedition->base_cost,
                $service['multiplier']
            );
            
            $estDaysMin = max(1, $expedition->est_days_min + $service['days_add']);
            $estDaysMax = max(1, $expedition->est_days_max + $service['days_add']);
            
            $services[] = [
                'code' => $service['code'],
                'name' => $service['name'],
                'cost' => $cost,
                'cost_formatted' => 'Rp ' . number_format($cost, 0, ',', '.'),
                'estimated_days' => $estDaysMin === $estDaysMax 
                    ? $estDaysMin . ' hari'
                    : $estDaysMin . '-' . $estDaysMax . ' hari',
            ];
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

    private function calculateShippingCost($provinceId, $totalWeight, $expeditionMultiplier = 1.0, $serviceMultiplier = 1.0)
    {
        // Base rate per province (for first 1kg)
        $baseRate = $this->shippingRates[$provinceId] ?? 35000;
        
        // Additional cost per kg after first kg
        $perKgCost = 5000;
        
        // Calculate total weight in kg
        $weightInKg = $totalWeight / 1000;
        
        // First 1kg included in base rate
        $additionalKg = max(0, ceil($weightInKg) - 1);
        
        $baseCost = $baseRate + ($additionalKg * $perKgCost);
        
        // Apply expedition and service multipliers
        return round($baseCost * $expeditionMultiplier * $serviceMultiplier);
    }

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
            ->with(['province', 'regency', 'district', 'village'])
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
                $stockErrors[] = "{$cart->product->name}: dipesan {$cart->quantity}, tersedia {$availableStock}";
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

            // Find service multiplier
            $serviceMultiplier = 1.0;
            foreach ($expedition->services as $service) {
                if ($service['code'] === $request->expedition_service) {
                    $serviceMultiplier = $service['multiplier'];
                    break;
                }
            }

            $shippingCost = $this->calculateShippingCost(
                $address->province_id, 
                $totalWeight,
                $expedition->base_cost,
                $serviceMultiplier
            );
            $total = $subtotal + $shippingCost;

            // Build full shipping address string for record
            $shippingAddressText = $address->recipient_name . "\n" .
                $address->phone . "\n" .
                $address->address_detail . "\n" .
                ($address->village ? $address->village->name . ', ' : '') .
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

            // Prepare items for Xendit invoice
            $xenditItems = [];
            foreach ($carts as $cart) {
                $xenditItems[] = [
                    'name' => $cart->product->name,
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
            ]);

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

            // Handle Xendit payment
            if ($request->payment_method === 'xendit') {
                $xenditService = new XenditService();
                $customer = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $address->phone,
                ];

                $invoice = $xenditService->createInvoice($order, $customer, $xenditItems);

                if ($invoice && isset($invoice['id'])) {
                    $order->update([
                        'xendit_invoice_id' => $invoice['id'],
                        'xendit_invoice_url' => $invoice['invoice_url'] ?? null,
                    ]);

                    DB::commit();

                    // Redirect to Xendit payment page
                    return redirect($invoice['invoice_url']);
                } else {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi atau pilih metode pembayaran lain.');
                }
            }

            DB::commit();

            return redirect()->route('checkout.success', $order)->with('success', 'Pesanan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        $order->load('items.product', 'address', 'expedition', 'sourceWarehouse');
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
