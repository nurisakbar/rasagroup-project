<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            $imageUrl = null;
            if ($stock->product->image) {
                // If image already contains full URL, use it as is
                if (filter_var($stock->product->image, FILTER_VALIDATE_URL)) {
                    $imageUrl = $stock->product->image;
                } else {
                    // Otherwise, prepend with base URL
                    $imagePath = ltrim($stock->product->image, '/');
                    $imageUrl = asset('images/' . $imagePath);
                }
            }

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
    }
}

