<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['creator', 'brand', 'category']);

            // Filter by status
            if ($request->filled('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Filter by brand
            if ($request->filled('brand_id') && $request->brand_id != '') {
                $query->where('brand_id', $request->brand_id);
            }

            // Filter by category
            if ($request->filled('category_id') && $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->orderColumn('name', function ($query, $order) {
                    $query->orderByRaw('COALESCE(commercial_name, name) ' . $order);
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('commercial_name', 'like', "%{$keyword}%")
                          ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('brand', function ($query, $keyword) {
                    $query->whereHas('brand', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('size', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('size', 'like', "%{$keyword}%")
                          ->orWhere('unit', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('image_display', function ($product) {
                    if ($product->image_url) {
                        return '<img src="' . asset($product->image_url) . '" alt="' . $product->display_name . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">';
                    }
                    return '<img src="' . asset('adminlte/img/default-50x50.gif') . '" alt="No Image" style="width: 50px; height: 50px;">';
                })
                ->addColumn('qrcode_display', function ($product) {
                    $url = route('products.show', $product->slug ?? $product->id);
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url);
                    return '
                        <a href="' . $qrUrl . '" target="_blank" title="Scan untuk lihat detail">
                            <img src="' . $qrUrl . '" alt="QR Code" style="width: 50px; height: 50px; border: 1px solid #ddd; padding: 2px;">
                        </a>
                    ';
                })
                ->addColumn('code_display', function ($product) {
                    return $product->code ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('name_info', function ($product) {
                    // Use display_name (which uses commercial_name as primary)
                    $html = '<strong>' . $product->display_name . '</strong>';
                    // Show original name as secondary info if commercial_name exists
                    if ($product->commercial_name && $product->commercial_name !== $product->name) {
                        $html .= '<br><small class="text-muted">' . $product->name . '</small>';
                    }
                    return $html;
                })
                ->addColumn('brand_info', function ($product) {
                    $html = '';
                    if ($product->brand) {
                        $html .= '<span class="label label-primary">' . $product->brand->name . '</span>';
                    }
                    if ($product->category) {
                        $html .= ' <span class="label label-default">' . $product->category->name . '</span>';
                    }
                    return $html ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('size_unit', function ($product) {
                    $html = '';
                    if ($product->size) {
                        $html .= $product->size;
                    }
                    if ($product->unit) {
                        $html .= ' <small class="text-muted">(' . $product->unit . ')</small>';
                    }
                    return $html ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('price_formatted', function ($product) {
                    return 'Rp ' . number_format($product->price, 0, ',', '.');
                })
                ->addColumn('reseller_point_display', function ($product) {
                    return number_format($product->reseller_point, 0, ',', '.') . ' Pts';
                })
                ->addColumn('weight_formatted', function ($product) {
                    return $product->formatted_weight;
                })
                ->addColumn('status_badge', function ($product) {
                    $class = $product->status === 'active' ? 'label-success' : 'label-danger';
                    $text = $product->status === 'active' ? 'Aktif' : 'Tidak Aktif';
                    return '<span class="label ' . $class . '">' . $text . '</span>';
                })
                ->addColumn('action', function ($product) {
                    $showUrl = route('admin.products.show', $product);
                    $editUrl = route('admin.products.edit', $product);
                    $deleteUrl = route('admin.products.destroy', $product);
                    
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
                ->rawColumns(['image_display', 'qrcode_display', 'code_display', 'name_info', 'brand_info', 'size_unit', 'status_badge', 'action'])
                ->make(true);
        }

        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.index', compact('brands', 'categories'));
    }

    public function import(Request $request)
    {
        Log::info('ProductController: Import request received', [
            'user_id' => Auth::id(),
            'has_file' => $request->hasFile('file'),
            'file_name' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : null,
            'file_size' => $request->hasFile('file') ? $request->file('file')->getSize() : null,
            'file_mime' => $request->hasFile('file') ? $request->file('file')->getMimeType() : null,
        ]);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $file = $request->file('file');
            Log::info('ProductController: Starting import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_path' => $file->getRealPath(),
            ]);

            $import = new ProductsImport();
            
            Log::info('ProductController: Calling Excel::import');
            Excel::import($import, $file);
            Log::info('ProductController: Excel::import completed');

            $failures = $import->failures();
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();

            Log::info('ProductController: Import completed', [
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'failures_count' => count($failures),
            ]);

            $message = "Berhasil import {$importedCount} produk.";
            
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} produk dilewati karena kode sudah ada.";
            }

            if (count($failures) > 0) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $errorMsg = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                    $errorMessages[] = $errorMsg;
                    Log::warning('ProductController: Import failure', [
                        'row' => $failure->row(),
                        'errors' => $failure->errors(),
                        'values' => $failure->values(),
                    ]);
                }
                
                Log::warning('ProductController: Import completed with failures', [
                    'total_failures' => count($failures),
                ]);
                
                return back()
                    ->with('warning', $message . " " . count($failures) . " baris gagal diimport.")
                    ->with('import_errors', $errorMessages);
            }

            Log::info('ProductController: Import successful', [
                'message' => $message,
            ]);

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('ProductController: Import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_produk.csv"',
        ];

        $columns = ['Product Code', 'Description', 'Description 2', 'Commercial Name', 'Brand', 'Size', 'Category', 'UM', 'Price', 'Reseller Point'];
        $examples = [
            ['FMF020-CT12', 'MB Cons 1L-Coconut Milk', '(In Bottle) FG Multibev', 'Coconut Milk', 'Multibev', '1 L', 'Coconut', 'BT', '70000', '1000'],
            ['FMF020-CT11', 'MB Cons 1L-Coconut Water', '(In Bottle) FG Multibev', 'Coconut Water', 'Multibev', '1 L', 'Coconut', 'BT', '70000', '1000'],
            ['FMF020-CT02', 'MB Cons 1L-Coconut Milk', 'FG Multibev', 'Coconut Milk', 'Multibev', '1 L', 'Coconut', 'PK', '70000', '1000'],
        ];

        $callback = function () use ($columns, $examples) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            foreach ($examples as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();
        
        return view('admin.products.create', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'technical_description' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'size' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            'large_unit' => 'nullable|string|max:50',
            'units_per_large' => 'nullable|integer|min:2|max:999999',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        if (empty($validated['large_unit'])) {
            $validated['units_per_large'] = null;
        }

        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['creator', 'brand', 'category']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();
        
        return view('admin.products.edit', compact('product', 'brands', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'technical_description' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'size' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            'large_unit' => 'nullable|string|max:50',
            'units_per_large' => 'nullable|integer|min:2|max:999999',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        } else {
            unset($validated['image']);
        }

        if (empty($validated['large_unit'])) {
            $validated['units_per_large'] = null;
        }

        $product->update($validated);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                ]);
            }
        }

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Delete product image.
     */
    public function deleteImage(Product $product, ProductImage $image)
    {
        // Ensure image belongs to product
        if ($image->product_id !== $product->id) {
            return response()->json(['success' => false, 'message' => 'Image does not belong to this product.'], 403);
        }

        // Delete from storage
        Storage::disk('public')->delete($image->image_path);
        
        // Delete from DB
        $image->delete();

        return response()->json(['success' => true]);
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Sync products with QID API.
     */
    public function syncQid()
    {
        try {
            Log::info('Starting QID Product Synchronization...');
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InVzZXIuem9obyIsImZ1bGxuYW1lIjoiVXNlciBab2hvIiwicm9sZW5hbWUiOiJVc2VyIiwicm9sZUlkIjoiZjA1MDg4ZDAtY2U1MC0xMWVmLWE3OWMtMmI0NTI1MzI5NDg2IiwiYXBwc0lkIjoiODY3NzA0NjAtY2ZkMy0xMWVlLWIwNDQtZDc0N2Y1NmEwZDM5IiwiZXhwaXJlZCI6IjIwMjctMDQtMDhUMjE6MzM6MjUuOTI5WiIsInN1cGVydXNlciI6ZmFsc2UsImNhbl9wb3N0aW5nIjpmYWxzZSwiY2FuX3N1Ym1pdCI6ZmFsc2UsImNhbl9hcHByb3ZlIjpmYWxzZSwiY2FuX2NhbmNlbCI6ZmFsc2UsImNhbl9wcmludCI6ZmFsc2UsImlhdCI6MTc3NTY2MzA1MywiZXhwIjoxODA3MjIwMDA1fQ.YPpZQWjpr_4Z9blgPodUPzJL1RYQ9zG84JPEawRGzVM'
            ])->post('https://development-qadwebapi.rasagroupoffice.com/api/master/item/list', [
                'prodLine' => 'FG',
                'status' => 'active'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('QID Product API Response received', ['count' => count($data)]);
                
                // Debug log raw data sample
                Log::debug('QID Product Data Sample', ['data' => array_slice($data, 0, 3)]);
                
                $items = isset($data['data']) ? $data['data'] : $data;
                $synchronizedCount = 0;
                
                foreach ($items as $index => $item) {
                    $itemCode = $item['itemCode'] ?? $item['item_code'] ?? null;
                    $itemName = $item['description'] ?? $item['item_name'] ?? null;
                    
                    if ($itemCode && $itemName) {
                        // Handle Brand
                        $brandId = null;
                        if (!empty($item['brand'])) {
                            $brand = Brand::firstOrCreate(
                                ['name' => $item['brand']],
                                ['status' => 'active']
                            );
                            $brandId = $brand->id;
                        }

                        // Handle Category
                        $categoryId = null;
                        if (!empty($item['category'])) {
                            $category = Category::firstOrCreate(
                                ['name' => $item['category']],
                                ['status' => 'active']
                            );
                            $categoryId = $category->id;
                        }

                        // Mapping data
                        $productData = [
                            'name' => $itemName,
                            'brand_id' => $brandId,
                            'category_id' => $categoryId,
                            'unit' => $item['uom'] ?? null,
                            'size' => $item['sizing'] ?? null,
                            'price' => $item['defaultPrice'] ?? 0,
                            'status' => isset($item['status']) ? strtolower($item['status']) : 'active',
                            'weight' => 1000, // Default weight since it's required but not in API
                            'created_by' => Auth::id() ?? \App\Models\User::where('role', 'super_admin')->first()?->id,
                        ];

                        Product::updateOrCreate(
                            ['code' => $itemCode],
                            $productData
                        );

                        $synchronizedCount++;
                    } else {
                        Log::warning("Skipping product at index {$index} due to missing data", ['item' => $item]);
                    }
                }
                
                Log::info("QID Product Synchronization finished. Total synced: {$synchronizedCount}");
                return back()->with('success', "Berhasil mensinkronisasi {$synchronizedCount} produk dari QID.");
            }
            
            Log::error('QID Product API Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->with('error', 'Gagal menghubungi server QID Product: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('QID Product Sync Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi produk: ' . $e->getMessage());
        }
    }
}
