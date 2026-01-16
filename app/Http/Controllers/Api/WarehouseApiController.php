<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WarehouseApiController extends Controller
{
    /**
     * Get list of warehouses
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'api_warehouses_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 86400, function () use ($request) {
            $query = Warehouse::with(['province', 'regency'])
                ->withCount('stocks as products_count')
                ->withSum('stocks', 'stock');

            // Filter by province
            if ($request->filled('province_id')) {
                $query->where('province_id', $request->province_id);
            }

            // Filter by regency
            if ($request->filled('regency_id')) {
                $query->where('regency_id', $request->regency_id);
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            // Search by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $warehouses = $query->orderBy('name')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $warehouses->items(),
                'meta' => [
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                    'per_page' => $warehouses->perPage(),
                    'total' => $warehouses->total(),
                ],
            ]);
        });
    }

    /**
     * Get products and stock for a specific warehouse
     * 
     * @param Request $request
     * @param string $warehouse
     * @return JsonResponse
     */
    public function getProducts(Request $request, string $warehouse): JsonResponse
    {
        $cacheKey = 'api_warehouse_products_' . $warehouse . '_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 86400, function () use ($request, $warehouse) {
            $warehouseModel = Warehouse::with(['province', 'regency'])->find($warehouse);

            if (!$warehouseModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse not found',
                ], 404);
            }

        // Build query with joins for better search and sorting
        $query = $warehouseModel->stocks()
            ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('warehouse_stocks.*');

        // Filter by product status
        if ($request->filled('product_status')) {
            $query->where('products.status', $request->product_status);
        } else {
            $query->where('products.status', 'active');
        }

        // Search by product name, code, or commercial name
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('products.name', 'like', $searchTerm)
                    ->orWhere('products.code', 'like', $searchTerm)
                    ->orWhere('products.commercial_name', 'like', $searchTerm);
            });
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        // Filter by stock availability
        if ($request->filled('stock_available')) {
            if ($request->stock_available === 'yes' || filter_var($request->stock_available, FILTER_VALIDATE_BOOLEAN)) {
                $query->where('warehouse_stocks.stock', '>', 0);
            } else {
                $query->where('warehouse_stocks.stock', '=', 0);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'product_name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'stock':
                $query->orderBy('warehouse_stocks.stock', $sortOrder);
                break;
            case 'product_code':
                $query->orderBy('products.code', $sortOrder);
                break;
            case 'price':
                $query->orderBy('products.price', $sortOrder);
                break;
            case 'product_name':
            default:
                $query->orderBy('products.name', $sortOrder);
                break;
        }

        $perPage = $request->get('per_page', 15);
        $stocks = $query->paginate($perPage);

        // Load relationships for formatted response
        $stocks->load(['product.brand', 'product.category']);

        // Format response
        $products = $stocks->map(function ($stock) {
            // Build full image URL
            // Build full image URL using Product accessor
            $imageUrl = $stock->product->image_url;

            return [
                'id' => $stock->product->id,
                'code' => $stock->product->code,
                'name' => $stock->product->name,
                'commercial_name' => $stock->product->commercial_name,
                'description' => $stock->product->description,
                'price' => (float) $stock->product->price,
                'unit' => $stock->product->unit,
                'size' => $stock->product->size,
                'weight' => $stock->product->weight,
                'image' => $imageUrl,
                'image_path' => $stock->product->image, // Keep original path for reference
                'status' => $stock->product->status,
                'brand' => $stock->product->brand ? [
                    'id' => $stock->product->brand->id,
                    'name' => $stock->product->brand->name,
                ] : null,
                'category' => $stock->product->category ? [
                    'id' => $stock->product->category->id,
                    'name' => $stock->product->category->name,
                ] : null,
                'stock' => $stock->stock,
                'stock_id' => $stock->id,
            ];
        });

        return response()->json([
            'success' => true,
            'warehouse' => [
                'id' => $warehouseModel->id,
                'name' => $warehouseModel->name,
                'address' => $warehouseModel->address,
                'phone' => $warehouseModel->phone,
                'description' => $warehouseModel->description,
                'is_active' => $warehouseModel->is_active,
                'province' => $warehouseModel->province ? [
                    'id' => $warehouseModel->province->id,
                    'name' => $warehouseModel->province->name,
                ] : null,
                'regency' => $warehouseModel->regency ? [
                    'id' => $warehouseModel->regency->id,
                    'name' => $warehouseModel->regency->name,
                ] : null,
                'full_location' => $warehouseModel->full_location,
            ],
            'data' => $products,
            'meta' => [
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'per_page' => $stocks->perPage(),
                'total' => $stocks->total(),
            ],
        ]);
        });
    }

    /**
     * Get all warehouses with their products and stock details
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllWithProducts(Request $request): JsonResponse
    {
        $cacheKey = 'api_warehouses_with_products_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 86400, function () use ($request) {
            $query = Warehouse::with(['province', 'regency']);

            // Filter by province
            if ($request->filled('province_id')) {
                $query->where('province_id', $request->province_id);
            }

            // Filter by regency
            if ($request->filled('regency_id')) {
                $query->where('regency_id', $request->regency_id);
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            } else {
                // Default: only active warehouses
                $query->where('is_active', true);
            }

            // Search by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filter by product status
            $productStatus = $request->get('product_status', 'active');

            // Filter by stock availability
            $stockAvailable = $request->filled('stock_available') 
                ? filter_var($request->stock_available, FILTER_VALIDATE_BOOLEAN) 
                : null;

            $warehouses = $query->orderBy('name')->get();

            // Format warehouses with products and stock
            $formattedWarehouses = $warehouses->map(function ($warehouse) use ($productStatus, $stockAvailable) {
                // Get stocks with products
                $stocksQuery = $warehouse->stocks()
                    ->with(['product.brand', 'product.category'])
                    ->whereHas('product', function ($q) use ($productStatus) {
                        $q->where('status', $productStatus);
                    });

                // Filter by stock availability
                if ($stockAvailable !== null) {
                    if ($stockAvailable) {
                        $stocksQuery->where('stock', '>', 0);
                    } else {
                        $stocksQuery->where('stock', '=', 0);
                    }
                }

                $stocks = $stocksQuery->get();

                // Format products with stock
                $products = $stocks->map(function ($stock) {
                    $product = $stock->product;
                    $imageUrl = $product->image_url;

                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'commercial_name' => $product->commercial_name,
                        'description' => $product->description,
                        'technical_description' => $product->technical_description,
                        'price' => (float) $product->price,
                        'formatted_price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                        'unit' => $product->unit,
                        'size' => $product->size,
                        'weight' => $product->weight,
                        'formatted_weight' => $product->formatted_weight,
                        'image_url' => $imageUrl,
                        'status' => $product->status,
                        'brand' => $product->brand ? [
                            'id' => $product->brand->id,
                            'name' => $product->brand->name,
                        ] : null,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                        ] : null,
                        'stock' => $stock->stock,
                        'stock_id' => $stock->id,
                        'is_available' => $stock->stock > 0,
                    ];
                });

                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'phone' => $warehouse->phone,
                    'description' => $warehouse->description,
                    'is_active' => $warehouse->is_active,
                    'province' => $warehouse->province ? [
                        'id' => $warehouse->province->id,
                        'name' => $warehouse->province->name,
                    ] : null,
                    'regency' => $warehouse->regency ? [
                        'id' => $warehouse->regency->id,
                        'name' => $warehouse->regency->name,
                    ] : null,
                    'full_location' => $warehouse->full_location,
                    'total_products' => $products->count(),
                    'total_stock' => $products->sum('stock'),
                    'products' => $products,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Warehouses with products retrieved successfully',
                'data' => $formattedWarehouses,
                'count' => $formattedWarehouses->count(),
            ], 200);
        });
    }
}

