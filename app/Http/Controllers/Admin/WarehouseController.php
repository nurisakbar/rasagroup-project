<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use App\Services\EkspedisiKuService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /**
     * Sync with QID API.
     */
    public function syncQid()
    {
        try {
            Log::info('Starting QID Hub Synchronization...');
            
            $response = Http::withHeaders([
                'Accept' => 'text/plain',
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InVzZXIuem9obyIsImZ1bGxuYW1lIjoiVXNlciBab2hvIiwicm9sZW5hbWUiOiJVc2VyIiwicm9sZUlkIjoiZjA1MDg4ZDAtY2U1MC0xMWVmLWE3OWMtMmI0NTI1MzI5NDg2IiwiYXBwc0lkIjoiODY3NzA0NjAtY2ZkMy0xMWVlLWIwNDQtZDc0N2Y1NmEwZDM5IiwiZXhwaXJlZCI6IjIwMjctMDQtMDhUMjE6MzM6MjUuOTI5WiIsInN1cGVydXNlciI6ZmFsc2UsImNhbl9wb3N0aW5nIjpmYWxzZSwiY2FuX3N1Ym1pdCI6ZmFsc2UsImNhbl9hcHByb3ZlIjpmYWxzZSwiY2FuX2NhbmNlbCI6ZmFsc2UsImNhbl9wcmludCI6ZmFsc2UsImlhdCI6MTc3NTY2MzA1MywiZXhwIjoxODA3MjIwMDA1fQ.YPpZQWjpr_4Z9blgPodUPzJL1RYQ9zG84JPEawRGzVM'
            ])->get('https://development-qadwebapi.rasagroupoffice.com/api/master/inventory/location');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('QID API Response received', ['count' => count($data)]);
                
                // Debug log raw data first few items
                Log::debug('QID Data Sample', ['data' => array_slice($data, 0, 3)]);
                
                $locations = isset($data['data']) ? $data['data'] : $data;
                $synchronizedCount = 0;
                
                foreach ($locations as $index => $loc) {
                    $kodeHub = $loc['location'] ?? $loc['locationID'] ?? $loc['locationid'] ?? null;
                    $namaHub = $loc['description'] ?? $loc['locationName'] ?? $loc['locationname'] ?? null;
                    
                    if ($kodeHub && $namaHub) {
                        Warehouse::updateOrCreate(
                            ['kode_hub' => $kodeHub],
                            [
                                'name' => $namaHub,
                                'slug' => Str::slug($namaHub) . '-' . strtolower($kodeHub),
                                'description' => 'Synced from QID',
                                'is_active' => true,
                            ]
                        );
                        $synchronizedCount++;
                    } else {
                        Log::warning("Skipping location at index {$index} due to missing data", ['item' => $loc]);
                    }
                }
                
                Log::info("QID Synchronization finished. Total synced: {$synchronizedCount}");
                return back()->with('success', "Berhasil mensinkronisasi {$synchronizedCount} Hub dari QID.");
            }
            
            Log::error('QID API Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->with('error', 'Gagal menghubungi server QID: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('QID Sync Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }
    protected $ekspedisiku;

    public function __construct(EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Warehouse::with(['province', 'regency'])
                ->withCount('stocks as products_count')
                ->withSum('stocks', 'stock');

            // Filter by province
            if ($request->filled('province_id') && $request->province_id != '') {
                $query->where('province_id', $request->province_id);
            }

            // Filter by regency
            if ($request->filled('regency_id') && $request->regency_id != '') {
                $query->where('regency_id', $request->regency_id);
            }

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('is_active', $request->status == '1');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_info', function ($warehouse) {
                    $html = '<strong>' . $warehouse->name . '</strong>';
                    if ($warehouse->kode_hub) {
                        $html .= '<br><small class="text-muted">Kode: ' . $warehouse->kode_hub . '</small>';
                    }
                    return $html;
                })
                ->addColumn('location_info', function ($warehouse) {
                    if ($warehouse->regency && $warehouse->province) {
                        return '<i class="fa fa-map-marker text-red"></i> ' . $warehouse->regency->name . ', ' . $warehouse->province->name;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('phone_display', function ($warehouse) {
                    return $warehouse->phone ?? '-';
                })
                ->addColumn('products_info', function ($warehouse) {
                    return '<span class="badge bg-blue">' . ($warehouse->products_count ?? 0) . ' produk</span>';
                })
                ->addColumn('stock_info', function ($warehouse) {
                    return '<span class="badge bg-green">' . number_format($warehouse->stocks_sum_stock ?? 0) . ' unit</span>';
                })
                ->addColumn('status_info', function ($warehouse) {
                    if ($warehouse->is_active) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($warehouse) {
                    $showUrl = route('admin.warehouses.show', $warehouse);
                    $editUrl = route('admin.warehouses.edit', $warehouse);
                    $deleteUrl = route('admin.warehouses.destroy', $warehouse);
                    
                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-xs" title="Detail & Stock">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['name_info', 'location_info', 'products_info', 'stock_info', 'status_info', 'action'])
                ->make(true);
        }

        $provinces = \App\Models\RajaOngkirProvince::orderBy('name')->get();

        return view('admin.warehouses.index', compact('provinces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $result = $this->ekspedisiku->getProvinces();
        $provinces = isset($result['data']) ? $result['data'] : [];
        return view('admin.warehouses.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Warehouse data
            'name' => 'required|string|max:255|unique:warehouses,name',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'province_id' => 'nullable',
            'regency_id' => 'nullable',
            'district_id' => 'nullable',
            'village_id' => 'nullable',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            // User data
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_phone' => 'nullable|string|max:20',
            'user_password' => 'required|string|min:8|confirmed',
        ]);

        // Create warehouse
        $warehouse = Warehouse::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'description' => $validated['description'] ?? null,
            'province_id' => $validated['province_id'] ?? null,
            'regency_id' => $validated['regency_id'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'village_id' => $validated['village_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        // Create user for this warehouse
        User::create([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'phone' => $validated['user_phone'] ?? null,
            'password' => Hash::make($validated['user_password']),
            'role' => 'warehouse',
            'warehouse_id' => $warehouse->id,
        ]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse dan akun pengelola berhasil dibuat. Login di: ' . url('/warehouse/login'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Warehouse $warehouse)
    {
        $warehouse->load(['province', 'regency', 'users']);
        
        // Sinkronisasi otomatis dari QID di balik layar jika ada kode_hub
        if ($warehouse->kode_hub) {
            $this->refreshStockFromQid($warehouse);
        }

        // Get stocks with product information
        $query = WarehouseStock::with('product')
            ->where('warehouse_id', $warehouse->id);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        
        $stocks = $query->orderBy('updated_at', 'desc')->paginate(10);
        
        // Get products not yet in this warehouse for adding
        $existingProductIds = $warehouse->stocks()->pluck('product_id')->toArray();
        $availableProducts = Product::where('status', 'active')
            ->whereNotIn('id', $existingProductIds)
            ->orderBy('name')
            ->get();
        
        return view('admin.warehouses.show', compact('warehouse', 'stocks', 'availableProducts'));
    }

    public function edit(Warehouse $warehouse)
    {
        $provinceRes = $this->ekspedisiku->getProvinces();
        $provinces = isset($provinceRes['data']) ? $provinceRes['data'] : [];
        
        $regencyRes = $warehouse->province_id 
            ? $this->ekspedisiku->getRegencies($warehouse->province_id)
            : null;
        $regencies = isset($regencyRes['data']) ? $regencyRes['data'] : [];
            
        $districtRes = $warehouse->regency_id
            ? $this->ekspedisiku->getDistricts($warehouse->regency_id)
            : null;
        $districts = isset($districtRes['data']) ? $districtRes['data'] : [];

        $villageRes = $warehouse->district_id
            ? $this->ekspedisiku->getVillages($warehouse->district_id)
            : null;
        $villages = isset($villageRes['data']) ? $villageRes['data'] : [];

        return view('admin.warehouses.edit', compact('warehouse', 'provinces', 'regencies', 'districts', 'villages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'province_id' => 'nullable',
            'regency_id' => 'nullable',
            'district_id' => 'nullable',
            'village_id' => 'nullable',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        // Delete all stocks first
        $warehouse->stocks()->delete();
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse berhasil dihapus.');
    }

    /**
     * Add product to warehouse stock.
     */
    public function addStock(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:0',
        ]);

        // Check if already exists
        $existingStock = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingStock) {
            return back()->with('error', 'Produk sudah ada di warehouse ini.');
        }

        WarehouseStock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $validated['product_id'],
            'stock' => $validated['stock'],
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke warehouse.');
    }

    /**
     * Update stock for a product in warehouse.
     */
    public function updateStock(Request $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $stock->update(['stock' => $validated['stock']]);

        return back()->with('success', 'Stock berhasil diperbarui.');
    }

    /**
     * Remove product from warehouse.
     */
    public function removeStock(Warehouse $warehouse, WarehouseStock $stock)
    {
        $stock->delete();

        return back()->with('success', 'Produk berhasil dihapus dari warehouse.');
    }

    /**
     * Sync all products to warehouse with stock 0.
     */
    public function syncProducts(Warehouse $warehouse)
    {
        // Get all active products
        $products = Product::where('status', 'active')->get();
        
        // Get existing product IDs in this warehouse
        $existingProductIds = $warehouse->stocks()->pluck('product_id')->toArray();
        
        $addedCount = 0;
        
        foreach ($products as $product) {
            // Skip if product already exists in warehouse
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }
            
            WarehouseStock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'stock' => 0,
            ]);
            
            $addedCount++;
        }
        
        if ($addedCount > 0) {
            return back()->with('success', "Berhasil menambahkan {$addedCount} produk ke warehouse dengan stock 0.");
        }
        
        return back()->with('info', 'Semua produk sudah ada di warehouse ini.');
    }

    /**
     * Get regencies by province (AJAX).
     */
    public function getRegencies(Request $request)
    {
        $result = $this->ekspedisiku->getRegencies($request->province_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Get districts by regency (AJAX).
     */
    public function getDistricts(Request $request)
    {
        $result = $this->ekspedisiku->getDistricts($request->regency_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Get villages by district (AJAX).
     */
    public function getVillages(Request $request)
    {
        $result = $this->ekspedisiku->getVillages($request->district_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Add user to warehouse.
     */
    public function addUser(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'sub_role' => 'required|in:admin,staff',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'warehouse',
            'sub_role' => $validated['sub_role'],
            'warehouse_id' => $warehouse->id,
            'wa_verified_at' => now(), // Managers/Staff usually don't need verification if created by admin
        ]);

        return back()->with('success', 'User warehouse berhasil ditambahkan.');
    }

    /**
     * Remove user from warehouse.
     */
    public function removeUser(Warehouse $warehouse, User $user)
    {
        if ($user->warehouse_id !== $warehouse->id) {
            return back()->with('error', 'User tidak terdaftar di warehouse ini.');
        }

        $user->delete();

        return back()->with('success', 'User warehouse berhasil dihapus.');
    }

    /**
     * Sinkronisasi stok dari QID secara internal (di balik layar)
     */
    private function refreshStockFromQid(Warehouse $warehouse)
    {
        try {
            Log::info("Auto Refreshing QID Stock for Hub: {$warehouse->name} ({$warehouse->kode_hub})");
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InVzZXIuem9obyIsImZ1bGxuYW1lIjoiVXNlciBab2hvIiwicm9sZW5hbWUiOiJVc2VyIiwicm9sZUlkIjoiZjA1MDg4ZDAtY2U1MC0xMWVmLWE3OWMtMmI0NTI1MzI5NDg2IiwiYXBwc0lkIjoiODY3NzA0NjAtY2ZkMy0xMWVlLWIwNDQtZDc0N2Y1NmEwZDM5IiwiZXhwaXJlZCI6IjIwMjctMDQtMDhUMjE6MzM6MjUuOTI5WiIsInN1cGVydXNlciI6ZmFsc2UsImNhbl9wb3N0aW5nIjpmYWxzZSwiY2FuX3N1Ym1pdCI6ZmFsc2UsImNhbl9hcHByb3ZlIjpmYWxzZSwiY2FuX2NhbmNlbCI6ZmFsc2UsImNhbl9wcmludCI6ZmFsc2UsImlhdCI6MTc3NTY2MzA1MywiZXhwIjoxODA3MjIwMDA1fQ.YPpZQWjpr_4Z9blgPodUPzJL1RYQ9zG84JPEawRGzVM'
            ])->timeout(10)->post('https://development-qadwebapi.rasagroupoffice.com/api/master/inventory/all', [
                'location' => $warehouse->kode_hub,
                'search' => '',
                'batch' => '',
                'length' => 1000
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['data'] ?? [];
                
                if (count($items) > 0) {
                    foreach ($items as $item) {
                        $itemCode = $item['item_code'] ?? $item['itemCode'] ?? $item['itemID'] ?? $item['itemid'] ?? null;
                        $qty = $item['qty'] ?? $item['quantity'] ?? $item['onHand'] ?? 0;
                        
                        if ($itemCode) {
                            $product = Product::where('code', $itemCode)->first();
                            if ($product) {
                                WarehouseStock::updateOrCreate(
                                    [
                                        'warehouse_id' => $warehouse->id,
                                        'product_id' => $product->id
                                    ],
                                    [
                                        'stock' => $qty
                                    ]
                                );
                            }
                        }
                    }
                    Log::info("Auto Refresh Success for Hub {$warehouse->kode_hub}. Processed " . count($items) . " items.");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Auto Refresh Stock Failed for Hub {$warehouse->kode_hub}: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Debug EkspedisiKu API Connectivity
     */
    public function debugEkspedisiku()
    {
        $baseUrl = config('services.ekspedisiku.base_url');
        $token = config('services.ekspedisiku.token');
        
        $results = [
            'config' => [
                'base_url' => $baseUrl,
                'token_length' => strlen($token),
                'token_prefix' => substr($token, 0, 10) . '...',
            ],
            'tests' => []
        ];

        // Test Provinces
        try {
            $start = microtime(true);
            $response = Http::withToken($token)->timeout(10)->get("{$baseUrl}/provinces");
            $end = microtime(true);
            
            $results['tests']['provinces'] = [
                'status' => $response->status(),
                'duration' => round($end - $start, 2) . 's',
                'success' => $response->successful(),
                'data_count' => isset($response->json()['data']) ? count($response->json()['data']) : 0,
                'raw_response' => $response->successful() ? 'OK' : $response->body(),
            ];
        } catch (\Exception $e) {
            $results['tests']['provinces'] = [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ];
        }

        // Test connectivity to QID API
        $qidBaseUrl = config('qidapi.base_url');
        $qidApi = app(\App\Services\QidApiService::class);
        $qidToken = $qidApi->getToken();
        
        try {
            $start = microtime(true);
            $response = Http::timeout(5)->get($qidBaseUrl);
            $end = microtime(true);
            $results['tests']['qid_connectivity'] = [
                'base_url' => $qidBaseUrl,
                'status' => $response->status(),
                'duration' => round($end - $start, 2) . 's',
                'reachable' => true,
                'token_valid' => !empty($qidToken),
                'token_preview' => $qidToken ? substr($qidToken, 0, 15) . '...' : 'NONE'
            ];
            
            if ($qidToken) {
                // Test a simple master data endpoint
                $itemStart = microtime(true);
                $itemRes = Http::withToken($qidToken)->post("{$qidBaseUrl}/api/master/item/list", [
                    'prodLine' => 'FG',
                    'length' => 1
                ]);
                $itemEnd = microtime(true);
                $results['tests']['qid_master_data'] = [
                    'endpoint' => '/api/master/item/list',
                    'status' => $itemRes->status(),
                    'duration' => round($itemEnd - $itemStart, 2) . 's',
                    'success' => $itemRes->successful(),
                ];
            }
        } catch (\Exception $e) {
            $results['tests']['qid_connectivity'] = [
                'base_url' => $qidBaseUrl,
                'error' => $e->getMessage(),
                'reachable' => false
            ];
        }

        // Server Environment Info
        $results['env'] = [
            'php_version' => PHP_VERSION,
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        return response()->json($results);
    }
}
