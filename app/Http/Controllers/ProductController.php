<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $selectedHubId = session('selected_hub_id');
        
        $query = Product::where('status', 'active');

        // Only show products that have stock in the selected hub
        if ($selectedHubId) {
            $query->whereHas('warehouseStocks', function($q) use ($selectedHubId) {
                $q->where('warehouse_id', $selectedHubId)
                  ->where('stock', '>', 0);
            });
            
            // Eager load the stock for the current hub
            $query->with(['warehouseStocks' => function($q) use ($selectedHubId) {
                $q->where('warehouse_id', $selectedHubId);
            }]);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('commercial_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderByRaw('COALESCE(commercial_name, name) asc');
                break;
            default:
                $query->latest();
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage)->withQueryString();
        $categories = \App\Models\Category::where('is_active', true)->get();

        return view('products.index', compact('products', 'selectedHubId', 'categories'));
    }

    public function show(Request $request, $identifier)
    {
        $product = Product::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        // If found by ID instead of slug, redirect to SEO URL
        if ($product->id === $identifier && $product->slug) {
            return redirect()->route('products.show', $product->slug);
        }

        if ($product->status !== 'active') {
            abort(404);
        }

        $selectedHubId = session('selected_hub_id');
        $selectedWarehouseId = $request->query('warehouse_id', $selectedHubId);
        
        // Ensure the product is available in the selected hub
        if ($selectedWarehouseId) {
            $hasStock = $product->warehouseStocks()
                ->where('warehouse_id', $selectedWarehouseId)
                ->where('stock', '>', 0)
                ->exists();
                
            if (!$hasStock && $selectedWarehouseId == $selectedHubId) {
                // If no stock in session-selected hub, we still show the product but let them choose another hub
                $selectedWarehouseId = null;
            }
        }
        
        return view('products.show', compact('product', 'selectedHubId', 'selectedWarehouseId'));
    }

    public function quickView($identifier)
    {
        $product = Product::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();
            
        $selectedHubId = session('selected_hub_id');
        return view('themes.nest.partials.quick-view-content', compact('product', 'selectedHubId'))->render();
    }
}
