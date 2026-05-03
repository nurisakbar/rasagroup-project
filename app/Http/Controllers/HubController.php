<?php

namespace App\Http\Controllers;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirProvince;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HubController extends Controller
{
    /**
     * Display list of hubs/distributors
     */
    public function index(Request $request)
    {
        Auth::user()?->forgetOwnHubShoppingSelectionIfSet();

        $excludeHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();

        $query = Warehouse::with(['province', 'regency'])
            ->where('is_active', true)
            ->when($excludeHubId, fn ($q) => $q->where('id', '!=', $excludeHubId))
            ->withCount(['stocks as products_count' => function ($q) {
                $q->whereHas('product', function ($p) {
                    $p->where('status', 'active');
                });
            }])
            ->withSum('stocks', 'stock');

        // Filter by province
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by regency
        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy('name')->get();

        $activeHubQuery = Warehouse::query()
            ->where('is_active', true)
            ->whereNotNull('province_id')
            ->when($excludeHubId, fn ($q) => $q->where('id', '!=', $excludeHubId));

        $provinceIdsWithHubs = (clone $activeHubQuery)->distinct()->pluck('province_id');

        $provinces = RajaOngkirProvince::query()
            ->whereIn('id', $provinceIdsWithHubs)
            ->orderBy('name')
            ->get();

        $regencies = collect();
        if ($request->filled('province_id')
            && $provinces->contains(fn (RajaOngkirProvince $p) => (string) $p->id === (string) $request->province_id)) {
            $regencyIdsWithHubs = Warehouse::query()
                ->where('is_active', true)
                ->where('province_id', $request->province_id)
                ->when($excludeHubId, fn ($q) => $q->where('id', '!=', $excludeHubId))
                ->whereNotNull('regency_id')
                ->distinct()
                ->pluck('regency_id');

            if ($regencyIdsWithHubs->isNotEmpty()) {
                $regencies = RajaOngkirCity::query()
                    ->where('province_id', $request->province_id)
                    ->whereIn('id', $regencyIdsWithHubs)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('hubs.index', compact('warehouses', 'provinces', 'regencies'));
    }

    /**
     * Show hub detail
     */
    public function show(Warehouse $warehouse)
    {
        if (!$warehouse->is_active) {
            abort(404);
        }

        $excludeHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();
        if ($excludeHubId && (string) $warehouse->id === $excludeHubId) {
            abort(404);
        }

        $warehouse->load(['province', 'regency', 'stocks.product']);

        // Produk di hub (termasuk stok 0) — hanya produk aktif
        $productsWithStock = $warehouse->stocks()
            ->with(['product.brand', 'product.category'])
            ->whereHas('product', function($query) {
                $query->where('status', 'active');
            })
            ->get()
            ->filter(function($stock) {
                return $stock->product !== null 
                    && $stock->product->status === 'active'
                    && isset($stock->product->display_name);
            })
            ->values();
        
        // Count total valid stocks for info
        $totalValidStocksCount = $warehouse->stocks()
            ->whereHas('product', function($query) {
                $query->where('status', 'active');
            })
            ->count();
        $hasInvalidStocks = false; // We filter them out now, so no invalid stocks should appear

        return view('hubs.show', compact('warehouse', 'productsWithStock'));
    }

    /**
     * Get regencies by province (AJAX)
     */
    public function getRegencies(Request $request)
    {
        $request->validate([
            'province_id' => 'required',
        ]);

        $provinceId = $request->province_id;

        $hasHubs = Warehouse::query()
            ->where('is_active', true)
            ->where('province_id', $provinceId)
            ->exists();

        if (! $hasHubs) {
            return response()->json([]);
        }

        $regencyIdsWithHubs = Warehouse::query()
            ->where('is_active', true)
            ->where('province_id', $provinceId)
            ->whereNotNull('regency_id')
            ->distinct()
            ->pluck('regency_id');

        if ($regencyIdsWithHubs->isEmpty()) {
            return response()->json([]);
        }

        $regencies = RajaOngkirCity::query()
            ->where('province_id', $provinceId)
            ->whereIn('id', $regencyIdsWithHubs)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regencies);
    }

    /**
     * Get nearby hubs based on province/regency
     */
    public function getNearbyHubs(Request $request)
    {
        $query = Warehouse::with(['province', 'regency'])
            ->where('is_active', true)
            ->withCount(['stocks as products_count' => function ($q) {
                $q->whereHas('product', function ($p) {
                    $p->where('status', 'active');
                });
            }]);

        // Priority: same regency > same province > other
        $warehouses = collect();

        if ($request->filled('regency_id')) {
            // Same regency first
            $sameRegency = (clone $query)->where('regency_id', $request->regency_id)->get();
            $warehouses = $warehouses->merge($sameRegency);
        }

        if ($request->filled('province_id')) {
            // Same province (excluding already added)
            $sameProvince = (clone $query)
                ->where('province_id', $request->province_id)
                ->when($request->filled('regency_id'), function ($q) use ($request) {
                    $q->where('regency_id', '!=', $request->regency_id);
                })
                ->get();
            $warehouses = $warehouses->merge($sameProvince);
        }

        // Other provinces (limit to 5)
        $otherProvinces = (clone $query)
            ->when($request->filled('province_id'), function ($q) use ($request) {
                $q->where('province_id', '!=', $request->province_id);
            })
            ->limit(5)
            ->get();
        $warehouses = $warehouses->merge($otherProvinces);

        return response()->json([
            'warehouses' => $warehouses->map(function ($w) use ($request) {
                $distance = 'Luar Provinsi';
                if ($request->filled('regency_id') && $w->regency_id == $request->regency_id) {
                    $distance = 'Sekota/Kabupaten';
                } elseif ($request->filled('province_id') && $w->province_id == $request->province_id) {
                    $distance = 'Seprovinsi';
                }
                
                return [
                    'id' => $w->id,
                    'slug' => $w->slug,
                    'name' => $w->name,
                    'address' => $w->address,
                    'phone' => $w->phone,
                    'province' => $w->province?->name,
                    'regency' => $w->regency?->name,
                    'location' => $w->full_location,
                    'products_count' => $w->products_count,
                    'distance' => $distance,
                ];
            })
        ]);
    }

    /**
     * Check stock availability in a hub for given products
     */
    public function checkStock(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $productIds = $request->product_ids ?? [];

        $warehouse = Warehouse::findOrFail($warehouseId);

        $stocks = $warehouse->stocks()
            ->whereIn('product_id', $productIds)
            ->with('product')
            ->get()
            ->keyBy('product_id');

        $result = [];
        foreach ($productIds as $productId) {
            $stock = $stocks->get($productId);
            $result[$productId] = [
                'available' => $stock ? $stock->stock : 0,
                'product_name' => $stock ? $stock->product->display_name : null,
            ];
        }

        return response()->json([
            'warehouse' => [
                'id' => $warehouse->id,
                'slug' => $warehouse->slug,
                'name' => $warehouse->name,
                'location' => $warehouse->full_location,
            ],
            'stocks' => $result,
        ]);
    }

    /**
     * Set selected hub for shopping session
     */
    public function select(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        $warehouse = Warehouse::find($request->warehouse_id);

        $excludeHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();
        if ($excludeHubId && (string) $warehouse->id === $excludeHubId) {
            return back()->with('error', 'Sebagai distributor, Anda tidak dapat memilih hub sendiri sebagai lokasi belanja.');
        }
        
        // Store in session
        session([
            'selected_hub_id' => $warehouse->id,
            'selected_hub_name' => $warehouse->name,
            'selected_hub_slug' => $warehouse->slug
        ]);
        
        // Store in cookie for 30 days
        cookie()->queue('selected_hub_id', $warehouse->id, 60 * 24 * 30);
        cookie()->queue('selected_hub_name', $warehouse->name, 60 * 24 * 30);
        cookie()->queue('selected_hub_slug', $warehouse->slug, 60 * 24 * 30);

        return redirect()->route('home')->with('success', "Hub {$warehouse->name} terpilih sebagai lokasi belanja Anda.");
    }

    /**
     * Detect nearest hub based on coordinates (AJAX)
     */
    public function detectNearestHub(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Get all active hubs with coordinates
        $excludeHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();
        $hubs = Warehouse::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($excludeHubId, fn ($q) => $q->where('id', '!=', $excludeHubId))
            ->get();

        if ($hubs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada hub aktif dengan data lokasi untuk saat ini.'
            ]);
        }

        $nearestHub = null;
        $minDistance = null;

        foreach ($hubs as $hub) {
            // Haversine formula
            $distance = $this->calculateDistance($lat, $lng, $hub->latitude, $hub->longitude);
            
            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $nearestHub = $hub;
            }
        }

        if ($nearestHub) {
            // Check if already selected the same hub to avoid redundant flash messages
            $currentHubId = session('selected_hub_id');
            
            // Store in session
            session([
                'selected_hub_id' => $nearestHub->id,
                'selected_hub_name' => $nearestHub->name,
                'selected_hub_slug' => $nearestHub->slug
            ]);
            
            // Store coordinates for reference
            session([
                'user_latitude' => $lat,
                'user_longitude' => $lng
            ]);

            // Store in cookie for 30 days
            cookie()->queue('selected_hub_id', $nearestHub->id, 60 * 24 * 30);
            cookie()->queue('selected_hub_name', $nearestHub->name, 60 * 24 * 30);
            cookie()->queue('selected_hub_slug', $nearestHub->slug, 60 * 24 * 30);

            return response()->json([
                'success' => true,
                'hub' => [
                    'id' => $nearestHub->id,
                    'name' => $nearestHub->name,
                    'distance' => round($minDistance, 2) . ' km'
                ],
                'is_new' => $currentHubId != $nearestHub->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menentukan hub terdekat.'
        ]);
    }

    /**
     * Helper to calculate distance between two points (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}

