<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
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
                    return '<strong>' . $warehouse->name . '</strong>';
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
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus hub ini?\');">
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

        $provinces = Province::orderBy('name')->get();
        return view('admin.warehouses.index', compact('provinces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::orderBy('name')->get();
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
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
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
                $q->where('name', 'like', '%' . $search . '%');
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        $provinces = Province::orderBy('name')->get();
        $regencies = $warehouse->province_id 
            ? Regency::where('province_id', $warehouse->province_id)->orderBy('name')->get() 
            : collect();
        return view('admin.warehouses.edit', compact('warehouse', 'provinces', 'regencies'));
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
            'province_id' => 'nullable|exists:provinces,id',
            'regency_id' => 'nullable|exists:regencies,id',
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
        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get();

        return response()->json($regencies);
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
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'warehouse',
            'warehouse_id' => $warehouse->id,
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
}
