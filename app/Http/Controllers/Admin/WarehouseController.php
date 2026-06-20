<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\OperationalHour;
use App\Models\Product;
use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirDistrict;
use App\Models\RajaOngkirProvince;
use App\Models\User;
use App\Models\Village;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use App\Services\EkspedisiKuService;
use App\Services\JubelioStockSyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /**
     * Sync with QID API (QAD) — dinonaktifkan.
     */
    public function syncQid()
    {
        return back()->with('error', 'Sinkronisasi Hub & Stock dengan QID/QAD dinonaktifkan.');
    }

    /**
     * Sync with Jubelio API.
     */
    public function syncJubelio()
    {
        set_time_limit(0);
        try {
            Log::info('Starting Jubelio Locations Synchronization...');
            
            $email = env('JUBELIO_EMAIL');
            $password = env('JUBELIO_PASSWORD');

            if (!$email || !$password) {
                return back()->with('error', 'Kredensial Jubelio tidak ditemukan di file .env');
            }

            // 1. Login to get Token
            $loginResponse = Http::post('https://api2.jubelio.com/login', [
                'email' => $email,
                'password' => $password,
            ]);

            if (!$loginResponse->successful()) {
                return back()->with('error', 'Gagal login ke Jubelio. Periksa kembali email dan password di .env');
            }

            $token = $loginResponse->json()['token'] ?? null;
            if (!$token) {
                return back()->with('error', 'Token Jubelio tidak ditemukan dalam response.');
            }

            // 2. Fetch all locations (paginated)
            $this->ekspedisiku->getProvinces();

            $locations = [];
            $page = 1;
            do {
                $response = Http::withToken($token)
                    ->get('https://api2.jubelio.com/locations/', [
                        'page' => $page,
                        'pageSize' => 200,
                    ]);

                if (!$response->successful()) {
                    Log::error('Jubelio API Failed', ['status' => $response->status(), 'body' => $response->body()]);
                    return back()->with('error', 'Gagal menghubungi server Jubelio: ' . $response->status());
                }

                $data = $response->json();
                $batch = $data['data'] ?? [];
                $locations = array_merge($locations, $batch);
                $totalCount = (int) ($data['totalCount'] ?? count($locations));
                $page++;
            } while (count($locations) < $totalCount && count($batch) > 0);

            Log::info('Jubelio Locations API Response received', ['count' => count($locations)]);

            $synchronizedCount = 0;

            foreach ($locations as $index => $loc) {
                $locationCode = $loc['location_code'] ?? null;
                $locationName = $loc['location_name'] ?? null;

                if (!$locationCode || !$locationName) {
                    Log::warning("Skipping location at index {$index} due to missing data", ['item' => $loc]);
                    continue;
                }

                $regionIds = $this->ensureRajaOngkirRegionIds(
                    $loc['province_id'] ?? null,
                    $loc['city_id'] ?? null,
                    $loc['district_id'] ?? null
                );
                $coords = $this->parseJubelioCoordinate($loc['coordinate'] ?? null);
                $villageId = $this->resolveJubelioVillageId($loc['subdistrict_id'] ?? null);

                $warehouse = Warehouse::updateOrCreate(
                    ['kode_hub' => $locationCode],
                    array_merge([
                        'name' => $locationName,
                        'slug' => Str::slug($locationName) . '-' . strtolower($locationCode),
                        'address' => $loc['address'] ?? null,
                        'postal_code' => $loc['post_code'] ?? null,
                        'phone' => $loc['phone'] ?? null,
                        'description' => 'Synced from Jubelio',
                        'is_active' => (bool) ($loc['is_active'] ?? true),
                        'village_id' => $villageId,
                        'latitude' => $coords['latitude'],
                        'longitude' => $coords['longitude'],
                    ], $regionIds)
                );
                $warehouse->markSyncSource('jubelio')->save();

                $synchronizedCount++;
            }

            Log::info("Jubelio Locations Synchronization finished. Total synced: {$synchronizedCount}");
            return back()->with('success', "Berhasil mensinkronisasi {$synchronizedCount} Lokasi dari Jubelio.");
        } catch (\Exception $e) {
            Log::error('Jubelio Sync Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Sync warehouse stocks from Jubelio all-stocks API.
     */
    public function syncStockJubelio(JubelioStockSyncService $stockSync)
    {
        set_time_limit(0);

        try {
            Log::info('Starting Jubelio Stock Synchronization...');

            $stats = $stockSync->sync();

            return back()->with(
                'success',
                "Berhasil mensinkronisasi stok Jubelio: {$stats['stock_rows']} baris stok, "
                . "{$stats['products']} produk. "
                . "({$stats['skipped_products']} produk & {$stats['skipped_locations']} lokasi dilewati — belum ada di website/hub lokal)"
            );
        } catch (\Throwable $e) {
            Log::error('Jubelio Stock Sync Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal sinkronisasi stok Jubelio: ' . $e->getMessage());
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
                    return '<strong>' . e($warehouse->name) . '</strong>';
                })
                ->addColumn('kode_hub_display', function ($warehouse) {
                    return $warehouse->kode_hub
                        ? e($warehouse->kode_hub)
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('sync_sources_info', function ($warehouse) {
                    return $warehouse->syncSourceBadgesHtml();
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
                    ';
                })
                ->rawColumns(['name_info', 'kode_hub_display', 'sync_sources_info', 'location_info', 'products_info', 'stock_info', 'status_info', 'action'])
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
            'postal_code' => 'nullable|string|max:10|regex:/^[0-9]{0,10}$/',
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
            'postal_code' => $validated['postal_code'] ?? null,
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
            'postal_code' => 'nullable|string|max:10|regex:/^[0-9]{0,10}$/',
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
        $warehouse->operationalHours()->delete();
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse berhasil dihapus.');
    }

    /**
     * Hapus semua hub beserta seluruh data terkait — hanya untuk APP_ENV=local (testing).
     */
    public function destroyAll(Request $request)
    {
        abort_unless(app()->environment('local'), 403);

        $request->validate([
            'confirm' => 'required|in:HAPUS SEMUA',
        ]);

        $warehouseCount = Warehouse::count();

        $deletedRelated = [];

        DB::transaction(function () use (&$deletedRelated) {
            $deletedRelated = [
                'jadwal_operasional' => OperationalHour::query()
                    ->where('operatable_type', Warehouse::class)
                    ->delete(),
                'staff_hub' => User::query()->where('role', User::ROLE_WAREHOUSE)->delete(),
                'riwayat_stok' => WarehouseStockHistory::query()->delete(),
                'stok_gudang' => WarehouseStock::query()->delete(),
                'keranjang_hub' => Cart::query()->whereNotNull('warehouse_id')->delete(),
                'hub' => Warehouse::query()->delete(),
            ];
        });

        $summary = collect($deletedRelated)
            ->map(fn ($count, $label) => "{$label}: {$count}")
            ->implode(', ');

        return redirect()->route('admin.warehouses.index')
            ->with('success', "Data hub & relasinya dihapus ({$warehouseCount} hub). {$summary}");
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
     * Sinkronisasi stok QID secara manual — dinonaktifkan.
     */
    public function syncStockQid(Warehouse $warehouse)
    {
        return back()->with('error', 'Sinkronisasi stok dengan QID/QAD dinonaktifkan.');
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
     * Generate default operational hours for warehouse.
     */
    public function generateOperationalHours(Warehouse $warehouse)
    {
        $warehouse->generateDefaultOperationalHours();
        return back()->with('success', 'Jadwal operasional default berhasil dibuat.');
    }

    /**
     * Pastikan ID wilayah Jubelio ada di tabel Raja Ongkir sebelum disimpan ke warehouse.
     */
    private function ensureRajaOngkirRegionIds($provinceId, $cityId, $districtId): array
    {
        $result = [
            'province_id' => null,
            'regency_id' => null,
            'district_id' => null,
        ];

        $provinceId = $provinceId !== null && $provinceId !== '' ? (string) $provinceId : null;
        $cityId = $cityId !== null && $cityId !== '' ? (string) $cityId : null;
        $districtId = $districtId !== null && $districtId !== '' ? (string) $districtId : null;

        if ($provinceId) {
            $this->ekspedisiku->getProvinces();
            if (RajaOngkirProvince::where('id', $provinceId)->exists()) {
                $result['province_id'] = $provinceId;
            }
        }

        if ($result['province_id'] && $cityId) {
            $this->ekspedisiku->getRegencies($result['province_id']);
            if (RajaOngkirCity::where('id', $cityId)->where('province_id', $result['province_id'])->exists()) {
                $result['regency_id'] = $cityId;
            }
        }

        if ($result['regency_id'] && $districtId) {
            $this->ekspedisiku->getDistricts($result['regency_id']);
            if (RajaOngkirDistrict::where('id', $districtId)->where('city_id', $result['regency_id'])->exists()) {
                $result['district_id'] = $districtId;
            }
        }

        return $result;
    }

    private function resolveJubelioVillageId($subdistrictId): ?string
    {
        if ($subdistrictId === null || $subdistrictId === '') {
            return null;
        }

        $subdistrictId = (string) $subdistrictId;

        return Village::where('id', $subdistrictId)->exists() ? $subdistrictId : null;
    }

    /**
     * Parse format Jubelio: "(-6.3167126,107.10949704999999)"
     */
    private function parseJubelioCoordinate(?string $coordinate): array
    {
        if (!$coordinate || !preg_match('/\(?\s*([-\d.]+)\s*,\s*([-\d.]+)\s*\)?/', $coordinate, $matches)) {
            return ['latitude' => null, 'longitude' => null];
        }

        return [
            'latitude' => (float) $matches[1],
            'longitude' => (float) $matches[2],
        ];
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

        // Test connectivity to QID API (jika kredensial terisi)
        if (\App\Support\QadIntegration::isConfigured()) {
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
                    'token_valid' => ! empty($qidToken),
                    'token_preview' => $qidToken ? substr($qidToken, 0, 15) . '...' : 'NONE',
                ];

                if ($qidToken) {
                    $itemStart = microtime(true);
                    $itemRes = Http::withToken($qidToken)->post("{$qidBaseUrl}/api/master/item/list", [
                        'prodLine' => 'FG',
                        'length' => 1,
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
                    'reachable' => false,
                    'error' => $e->getMessage(),
                ];
            }
        } else {
            $results['tests']['qid_connectivity'] = [
                'skipped' => true,
                'reason' => 'QIDAPI belum dikonfigurasi (QIDAPI_* di .env)',
            ];
        }

        $results['env'] = [
            'php_version' => PHP_VERSION,
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        return response()->json($results);
    }
}
