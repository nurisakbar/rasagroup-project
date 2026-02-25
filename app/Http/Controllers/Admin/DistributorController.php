<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PriceLevel;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Services\RajaOngkirService;

class DistributorController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }
    /**
     * Display a listing of Distributors.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::where('role', User::ROLE_DISTRIBUTOR)
                ->with(['warehouse.province', 'warehouse.regency']);

            // Filter by province
            if ($request->filled('province_id') && $request->province_id != '') {
                $query->whereHas('warehouse', function ($q) use ($request) {
                    $q->where('province_id', $request->province_id);
                });
            }

            // Filter by regency
            if ($request->filled('regency_id') && $request->regency_id != '') {
                $query->whereHas('warehouse', function ($q) use ($request) {
                    $q->where('regency_id', $request->regency_id);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_info', function ($dist) {
                    return '<strong>' . $dist->name . '</strong>';
                })
                ->addColumn('hub_info', function ($dist) {
                    if ($dist->warehouse) {
                        return '<span class="label label-warning">' . $dist->warehouse->name . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('location_info', function ($dist) {
                    if ($dist->warehouse) {
                        $location = '';
                        if ($dist->warehouse->regency) {
                            $location .= $dist->warehouse->regency->name;
                        }
                        if ($dist->warehouse->province) {
                            $location .= ($location ? ', ' : '') . $dist->warehouse->province->name;
                        }
                        return '<small>' . ($location ?: '-') . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('phone_display', function ($dist) {
                    return $dist->phone ?? '-';
                })
                ->addColumn('created_date', function ($dist) {
                    return $dist->created_at->format('d M Y');
                })
                ->addColumn('action', function ($dist) {
                    $showUrl = route('admin.distributors.show', $dist);
                    $editUrl = route('admin.distributors.edit', $dist);
                    $deleteUrl = route('admin.distributors.destroy', $dist);
                    
                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-xs" title="Detail">
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
                ->rawColumns(['name_info', 'hub_info', 'location_info', 'action'])
                ->make(true);
        }

        $provinces = \App\Models\RajaOngkirProvince::orderBy('name')->get();
        $pendingCount = User::where('distributor_status', 'pending')->count();

        return view('admin.distributors.index', compact('provinces', 'pendingCount'));
    }

    /**
     * Display pending distributor applications.
     */
    public function applications(Request $request)
    {
        $query = User::where('distributor_status', 'pending');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%")
                  ->orWhere('no_npwp', 'like', "%{$search}%");
            });
        }

        $applications = $query->orderBy('distributor_applied_at', 'asc')->paginate(20);
        $provinces = \App\Models\RajaOngkirProvince::orderBy('name')->get();

        return view('admin.distributors.applications', compact('applications', 'provinces'));
    }

    /**
     * Show application details.
     */
    public function showApplication(User $user)
    {
        if ($user->distributor_status !== 'pending') {
            return redirect()->route('admin.distributors.applications')
                ->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }

        $user->load(['distributorProvince', 'distributorRegency']);
        
        $provinceRes = $this->rajaOngkir->getProvinces();
        $provinces = isset($provinceRes['data']) ? $provinceRes['data'] : [];
        
        $priceLevels = PriceLevel::active()->ordered()->get();
        return view('admin.distributors.application-detail', compact('user', 'provinces', 'priceLevels'));
    }

    /**
     * Approve distributor application.
     */
    public function approve(Request $request, User $user)
    {
        if ($user->distributor_status !== 'pending') {
            return redirect()->route('admin.distributors.applications')
                ->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }

        $validated = $request->validate([
            'hub_name' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:raja_ongkir_provinces,id'],
            'regency_id' => ['required', 'exists:raja_ongkir_cities,id'],
            'district_id' => ['required', 'exists:raja_ongkir_districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'hub_phone' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_level_id' => ['nullable', 'exists:price_levels,id'],
        ]);

        // Create the warehouse/hub
        $warehouse = Warehouse::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['hub_name'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'village_id' => $validated['village_id'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['hub_phone'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => true,
        ]);

        // Update user to distributor role
        $user->update([
            'role' => User::ROLE_DISTRIBUTOR,
            'warehouse_id' => $warehouse->id,
            'price_level_id' => $validated['price_level_id'] ?? null,
            'distributor_status' => 'approved',
            'distributor_approved_at' => now(),
        ]);

        // Sync all active products to warehouse stock
        $this->syncProductsToWarehouse($warehouse);

        return redirect()->route('admin.distributors.applications')
            ->with('success', "Pengajuan {$user->name} berhasil disetujui sebagai Distributor. Semua produk aktif telah di-sync ke warehouse.");
    }

    /**
     * Reject distributor application.
     */
    public function reject(Request $request, User $user)
    {
        if ($user->distributor_status !== 'pending') {
            return redirect()->route('admin.distributors.applications')
                ->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $user->update([
            'distributor_status' => 'rejected',
            'distributor_rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.distributors.applications')
            ->with('success', "Pengajuan {$user->name} telah ditolak.");
    }

    /**
     * Show the form for creating a new Distributor.
     */
    public function create()
    {
        $result = $this->rajaOngkir->getProvinces();
        $provinces = isset($result['data']) ? $result['data'] : [];
        $priceLevels = PriceLevel::active()->ordered()->get();
        return view('admin.distributors.create', compact('provinces', 'priceLevels'));
    }

    /**
     * Store a newly created Distributor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hub data
            'hub_name' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:raja_ongkir_provinces,id'],
            'regency_id' => ['required', 'exists:raja_ongkir_cities,id'],
            'district_id' => ['required', 'exists:raja_ongkir_districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'hub_phone' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // User data
            'user_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_password' => ['required', 'string', 'min:8', 'confirmed'],
            'price_level_id' => ['nullable', 'exists:price_levels,id'],
        ]);

        // Create the warehouse/hub first
        $warehouse = Warehouse::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['hub_name'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'village_id' => $validated['village_id'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['hub_phone'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => true,
        ]);

        // Create the user
        User::create([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'phone' => $validated['user_phone'],
            'password' => Hash::make($validated['user_password']),
            'role' => User::ROLE_DISTRIBUTOR,
            'warehouse_id' => $warehouse->id,
            'price_level_id' => $validated['price_level_id'] ?? null,
            'distributor_status' => 'approved',
            'distributor_approved_at' => now(),
        ]);

        // Sync all active products to warehouse stock
        $this->syncProductsToWarehouse($warehouse);

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor berhasil ditambahkan. Semua produk aktif telah di-sync ke warehouse.');
    }

    /**
     * Show Distributor details.
     */
    public function show(Request $request, User $distributor)
    {
        if (!$distributor->isDistributor()) {
            abort(404);
        }

        $distributor->load('warehouse');

        // Handle AJAX request for stock DataTable
        if ($request->ajax() && $request->get('type') === 'stock' && $distributor->warehouse) {
            $query = WarehouseStock::with(['product.brand', 'product.category'])
                ->where('warehouse_id', $distributor->warehouse->id);

            // Filter by product search
            if ($request->filled('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('product_image', function ($stock) {
                    if ($stock->product->image_url) {
                        return '<img src="' . asset($stock->product->image_url) . '" alt="' . $stock->product->display_name . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px;">';
                    }
                    return '<img src="' . asset('adminlte/img/default-50x50.gif') . '" alt="No Image" style="width: 40px; height: 40px;">';
                })
                ->addColumn('product_code', function ($stock) {
                    return $stock->product->code ?? '-';
                })
                ->addColumn('product_name', function ($stock) {
                    // Use display_name (which uses commercial_name as primary)
                    $html = '<strong>' . $stock->product->display_name . '</strong>';
                    // Show original name as secondary info if commercial_name exists
                    if ($stock->product->commercial_name && $stock->product->commercial_name !== $stock->product->name) {
                        $html .= '<br><small class="text-muted">' . $stock->product->name . '</small>';
                    }
                    return $html;
                })
                ->addColumn('product_info', function ($stock) {
                    $html = '';
                    if ($stock->product->brand) {
                        $html .= '<span class="label label-primary">' . $stock->product->brand->name . '</span> ';
                    }
                    if ($stock->product->category) {
                        $html .= '<span class="label label-default">' . $stock->product->category->name . '</span>';
                    }
                    return $html ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('stock_badge', function ($stock) {
                    if ($stock->stock <= 0) {
                        return '<span class="label label-danger">Kosong (0)</span>';
                    } elseif ($stock->stock <= 10) {
                        return '<span class="label label-warning">' . number_format($stock->stock, 0, ',', '.') . '</span>';
                    } else {
                        return '<span class="label label-success">' . number_format($stock->stock, 0, ',', '.') . '</span>';
                    }
                })
                ->addColumn('product_price', function ($stock) {
                    return 'Rp ' . number_format($stock->product->price, 0, ',', '.');
                })
                ->rawColumns(['product_image', 'product_name', 'product_info', 'stock_badge'])
                ->make(true);
        }

        // Handle AJAX request for orders DataTable
        if ($request->ajax() && $request->get('type') === 'orders') {
            $query = Order::with(['user', 'expedition', 'sourceWarehouse'])
                ->where('user_id', $distributor->id);

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('order_status', $request->status);
            }

            // Filter by payment status
            if ($request->filled('payment_status') && $request->payment_status != '') {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by date range
            if ($request->filled('start_date')) {
                try {
                    $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
                    $query->whereDate('created_at', '>=', $startDate);
                } catch (\Exception $e) {
                    // Fallback or ignore invalid format
                }
            }
            if ($request->filled('end_date')) {
                try {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
                    $query->whereDate('created_at', '<=', $endDate);
                } catch (\Exception $e) {
                    // Fallback or ignore invalid format
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_number_display', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    if ($order->order_type === 'distributor') {
                        $html .= '<br><span class="label label-warning" style="font-size: 10px;">Distributor</span>';
                    } else {
                        $html .= '<br><span class="label label-default" style="font-size: 10px;">Regular</span>';
                    }
                    return $html;
                })
                ->addColumn('order_date', function ($order) {
                    return $order->created_at->format('d-m-Y');
                })
                ->addColumn('customer_info', function ($order) {
                    $html = '<strong>' . $order->user->name . '</strong>';
                    if ($order->user->phone) {
                        $html .= '<br><small class="text-muted"><i class="fa fa-phone"></i> ' . $order->user->phone . '</small>';
                    }
                    return $html;
                })
                ->addColumn('total_amount_formatted', function ($order) {
                    return 'Rp ' . number_format($order->total_amount, 0, ',', '.');
                })
                ->addColumn('order_status_badge', function ($order) {
                    $colors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    ];
                    $color = $colors[$order->order_status] ?? 'default';
                    $statusText = ucfirst($order->order_status);
                    return '<span class="label label-' . $color . '">' . $statusText . '</span>';
                })
                ->addColumn('payment_status_badge', function ($order) {
                    $colors = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    ];
                    $color = $colors[$order->payment_status] ?? 'default';
                    $statusText = ucfirst($order->payment_status);
                    return '<span class="label label-' . $color . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($order) {
                    $showUrl = route('admin.orders.show', $order);
                    return '<a href="' . $showUrl . '" class="btn btn-info btn-xs" title="Detail">
                        <i class="fa fa-eye"></i> Detail
                    </a>';
                })
                ->rawColumns(['order_number_display', 'customer_info', 'order_status_badge', 'payment_status_badge', 'action'])
                ->make(true);
        }

        // Calculate stock statistics
        $stockStats = null;
        if ($distributor->warehouse) {
            $stockStats = WarehouseStock::where('warehouse_id', $distributor->warehouse->id)
                ->leftJoin('products', 'warehouse_stocks.product_id', '=', 'products.id')
                ->selectRaw('
                    COUNT(warehouse_stocks.id) as total_products,
                    SUM(CASE WHEN warehouse_stocks.stock > 0 THEN 1 ELSE 0 END) as products_in_stock,
                    SUM(CASE WHEN warehouse_stocks.stock = 0 THEN 1 ELSE 0 END) as products_out_of_stock,
                    SUM(CASE WHEN warehouse_stocks.stock <= 10 AND warehouse_stocks.stock > 0 THEN 1 ELSE 0 END) as products_low_stock,
                    COALESCE(SUM(warehouse_stocks.stock), 0) as total_stock,
                    COALESCE(SUM(CASE WHEN warehouse_stocks.stock > 0 THEN warehouse_stocks.stock * products.price ELSE 0 END), 0) as total_value
                ')
                ->first();
            
            // Ensure stockStats is not null even if no stocks exist
            if (!$stockStats) {
                $stockStats = (object) [
                    'total_products' => 0,
                    'products_in_stock' => 0,
                    'products_out_of_stock' => 0,
                    'products_low_stock' => 0,
                    'total_stock' => 0,
                    'total_value' => 0,
                ];
            } else {
                // Ensure all values are set (handle null values from SUM)
                $stockStats->total_products = $stockStats->total_products ?? 0;
                $stockStats->products_in_stock = $stockStats->products_in_stock ?? 0;
                $stockStats->products_out_of_stock = $stockStats->products_out_of_stock ?? 0;
                $stockStats->products_low_stock = $stockStats->products_low_stock ?? 0;
                $stockStats->total_stock = $stockStats->total_stock ?? 0;
                $stockStats->total_value = $stockStats->total_value ?? 0;
            }
        }

        return view('admin.distributors.show', compact('distributor', 'stockStats'));
    }

    /**
     * Show the form for editing a Distributor.
     */
    public function edit(User $distributor)
    {
        if (!$distributor->isDistributor()) {
            abort(404);
        }

        $distributor->load('warehouse');
        
        $provinceRes = $this->rajaOngkir->getProvinces();
        $provinces = isset($provinceRes['data']) ? $provinceRes['data'] : [];
        
        $regencies = ($distributor->warehouse && $distributor->warehouse->province_id)
            ? ($this->rajaOngkir->getCities($distributor->warehouse->province_id)['data'] ?? [])
            : [];
            
        $districts = ($distributor->warehouse && $distributor->warehouse->regency_id)
            ? ($this->rajaOngkir->getDistricts($distributor->warehouse->regency_id)['data'] ?? [])
            : [];

        $villages = [];
        if ($distributor->warehouse && $distributor->warehouse->district_id) {
            $villages = $this->getVillagesInternal($distributor->warehouse->district_id);
        }

        $priceLevels = PriceLevel::active()->ordered()->get();
        
        return view('admin.distributors.edit', compact('distributor', 'provinces', 'regencies', 'districts', 'villages', 'priceLevels'));
    }

    /**
     * Update the specified distributor.
     */
    public function update(Request $request, User $distributor)
    {
        if (!$distributor->isDistributor()) {
            abort(404);
        }

        $validated = $request->validate([
            // Hub data
            'hub_name' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:raja_ongkir_provinces,id'],
            'regency_id' => ['required', 'exists:raja_ongkir_cities,id'],
            'district_id' => ['required', 'exists:raja_ongkir_districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'hub_phone' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // User data
            'user_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $distributor->id],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'price_level_id' => ['nullable', 'exists:price_levels,id'],
        ]);

        // Update hub
        if ($distributor->warehouse) {
            $distributor->warehouse->update([
                'name' => $validated['hub_name'],
                'province_id' => $validated['province_id'],
                'regency_id' => $validated['regency_id'],
                'district_id' => $validated['district_id'],
                'village_id' => $validated['village_id'] ?? null,
                'address' => $validated['address'],
                'phone' => $validated['hub_phone'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);
        }

        // Update user
        $userData = [
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'phone' => $validated['user_phone'],
            'price_level_id' => $validated['price_level_id'] ?? null,
        ];

        if ($request->filled('user_password')) {
            $userData['password'] = Hash::make($validated['user_password']);
        }

        $distributor->update($userData);

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor berhasil diperbarui.');
    }

    /**
     * Delete a Distributor.
     */
    public function destroy(User $distributor)
    {
        if (!$distributor->isDistributor()) {
            abort(404);
        }

        // Optionally delete the warehouse if only this user is assigned
        $warehouse = $distributor->warehouse;
        
        $distributor->delete();

        // If warehouse has no other users, delete it too
        if ($warehouse && $warehouse->users()->count() === 0) {
            $warehouse->delete();
        }

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor berhasil dihapus.');
    }

    /**
     * Get regencies by province (AJAX).
     */
    public function getRegencies(Request $request)
    {
        $result = $this->rajaOngkir->getCities($request->province_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Internal helper to get villages for initial form load.
     */
    private function getVillagesInternal($districtId)
    {
        $roDistrict = \App\Models\RajaOngkirDistrict::find($districtId);
        if (!$roDistrict) {
            return [];
        }

        $localDistrict = \App\Models\District::where('name', $roDistrict->name)->first();
        if (!$localDistrict) {
            $localDistrict = \App\Models\District::where('name', 'like', '%' . $roDistrict->name . '%')->first();
        }

        if ($localDistrict) {
            return \App\Models\Village::where('district_id', $localDistrict->id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return [];
    }

    /**
     * Sync all products to distributor warehouse.
     */
    public function syncProducts(User $distributor)
    {
        if (!$distributor->isDistributor() || !$distributor->warehouse) {
            return back()->with('error', 'Distributor atau warehouse tidak ditemukan.');
        }

        $addedCount = $this->syncProductsToWarehouse($distributor->warehouse);

        return back()->with('success', "Sinkronisasi produk berhasil. {$addedCount} produk baru ditambahkan ke stock warehouse.");
    }

    /**
     * Helper method to sync products to warehouse.
     */
    private function syncProductsToWarehouse(Warehouse $warehouse): int
    {
        // Get all active products
        $products = Product::where('status', 'active')->get();
        
        // Get existing product IDs in this warehouse
        $existingProductIds = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->pluck('product_id')
            ->toArray();
        
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
        
        return $addedCount;
    }
}
