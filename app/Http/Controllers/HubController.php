<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Regency;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class HubController extends Controller
{
    /**
     * Display list of hubs/distributors
     */
    public function index(Request $request)
    {
        $query = Warehouse::with(['province', 'regency'])
            ->where('is_active', true)
            ->withCount(['stocks as products_count' => function ($q) {
                $q->where('stock', '>', 0);
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
        
        $provinces = Province::orderBy('name')->get();
        $regencies = collect();
        
        if ($request->filled('province_id')) {
            $regencies = Regency::where('province_id', $request->province_id)->orderBy('name')->get();
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

        $warehouse->load(['province', 'regency', 'stocks.product']);

        // Get products with stock - filter out stocks with invalid products
        $productsWithStock = $warehouse->stocks()
            ->with(['product.brand', 'product.category'])
            ->where('stock', '>', 0)
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
            ->where('stock', '>', 0)
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
        $regencies = Regency::where('province_id', $request->province_id)
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
                $q->where('stock', '>', 0);
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
                'name' => $warehouse->name,
                'location' => $warehouse->full_location,
            ],
            'stocks' => $result,
        ]);
    }
}

