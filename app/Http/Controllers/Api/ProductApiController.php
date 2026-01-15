<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    /**
     * Get all products for chatbot knowledge base
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['brand', 'category'])
                ->where('status', 'active');

            // Search filter
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('code', 'like', $searchTerm)
                        ->orWhere('commercial_name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            }

            // Brand filter
            if ($request->filled('brand_id')) {
                $query->where('brand_id', $request->brand_id);
            }

            // Category filter
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Pagination
            $perPage = $request->get('per_page', 100); // Default 100 for chatbot knowledge
            $perPage = min($perPage, 500); // Max 500 per page
            
            $products = $query->orderBy('name')->paginate($perPage);

            // Format products for chatbot knowledge
            $formattedProducts = $products->map(function ($product) {
                // Build image URL using Product accessor
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
                    // Knowledge base friendly format
                    'knowledge_text' => $this->buildKnowledgeText($product),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $formattedProducts,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all products without pagination (for chatbot knowledge base)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['brand', 'category'])
                ->where('status', 'active');

            // Search filter
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('code', 'like', $searchTerm)
                        ->orWhere('commercial_name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            }

            // Brand filter
            if ($request->filled('brand_id')) {
                $query->where('brand_id', $request->brand_id);
            }

            // Category filter
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $products = $query->orderBy('name')->get();

            // Format products for chatbot knowledge
            $formattedProducts = $products->map(function ($product) {
                // Build image URL using Product accessor
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
                    // Knowledge base friendly format
                    'knowledge_text' => $this->buildKnowledgeText($product),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All products retrieved successfully',
                'data' => $formattedProducts,
                'count' => $formattedProducts->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build knowledge text for chatbot
     * 
     * @param Product $product
     * @return string
     */
    private function buildKnowledgeText(Product $product): string
    {
        $text = "Produk: {$product->name}";
        
        if ($product->code) {
            $text .= " (Kode: {$product->code})";
        }
        
        if ($product->commercial_name) {
            $text .= "\nNama Komersial: {$product->commercial_name}";
        }
        
        if ($product->brand) {
            $text .= "\nBrand: {$product->brand->name}";
        }
        
        if ($product->category) {
            $text .= "\nKategori: {$product->category->name}";
        }
        
        if ($product->description) {
            $text .= "\nDeskripsi: {$product->description}";
        }
        
        if ($product->technical_description) {
            $text .= "\nSpesifikasi Teknis: {$product->technical_description}";
        }
        
        $text .= "\nHarga: Rp " . number_format($product->price, 0, ',', '.');
        
        if ($product->size) {
            $text .= "\nUkuran: {$product->size}";
        }
        
        if ($product->unit) {
            $text .= "\nUnit: {$product->unit}";
        }
        
        if ($product->weight) {
            $text .= "\nBerat: {$product->formatted_weight}";
        }
        
        return $text;
    }
}

