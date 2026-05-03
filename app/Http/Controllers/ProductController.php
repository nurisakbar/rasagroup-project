<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        Auth::user()?->forgetOwnHubShoppingSelectionIfSet();
        $selectedHubId = session('selected_hub_id');

        $query = Product::with(['category', 'brand', 'warehouseStocks'])->where('status', 'active');

        if ($selectedHubId) {
            $query->with(['warehouseStocks' => function ($q) use ($selectedHubId) {
                $q->where('warehouse_id', $selectedHubId);
            }]);
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $like = '%' . addcslashes($keyword, '%_\\') . '%';

            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('commercial_name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('technical_description', 'like', $like)
                    ->orWhereHas('brand', function ($b) use ($like) {
                        $b->where('name', 'like', $like);
                    })
                    ->orWhereHas('category', function ($c) use ($like) {
                        $c->where('name', 'like', $like);
                    });
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', function($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $query->orderByInStockFirst($selectedHubId);

        // Sort (valid values: latest, price_low, price_high, name)
        $sort = $request->get('sort', 'latest');
        if (! in_array($sort, ['latest', 'price_low', 'price_high', 'name'], true)) {
            $sort = 'latest';
        }
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc')->orderBy('created_at', 'desc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'name':
                $query->orderByRaw('COALESCE(commercial_name, name) asc')->orderBy('created_at', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $perPage = $request->get('per_page', 15);
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

        Auth::user()?->forgetOwnHubShoppingSelectionIfSet();
        $selectedHubId = session('selected_hub_id');
        $selectedWarehouseId = $request->query('warehouse_id', $selectedHubId);

        $excludeOwn = Auth::user()?->distributorShoppingExcludedWarehouseId();
        if ($excludeOwn && $selectedWarehouseId && (string) $selectedWarehouseId === $excludeOwn) {
            $selectedWarehouseId = null;
        }

        // Get Related Products (Same category, excluding current product)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(3)
            ->get();

        // If not enough related products by category, find by name similarity
        if ($relatedProducts->count() < 3) {
            $moreRelated = Product::where('name', 'like', '%' . substr($product->name, 0, 5) . '%')
                ->where('id', '!=', $product->id)
                ->where('category_id', '!=', $product->category_id)
                ->where('status', 'active')
                ->take(3 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($moreRelated);
        }

        // Generate Dummy Reviews
        $dummyReviews = [
            [
                'user' => 'Andi Wijaya',
                'rating' => 5,
                'date' => '12 Maret 2026',
                'comment' => 'Produk sangat berkualitas, rasa milk dates-nya sangat terasa dan autentik. Pengiriman cepat!'
            ],
            [
                'user' => 'Siti Aminah',
                'rating' => 4,
                'date' => '05 April 2026',
                'comment' => 'Enak buat campuran minuman di cafe saya. Pelanggan pada suka. Reorder lagi nanti.'
            ],
            [
                'user' => 'Budi Santoso',
                'rating' => 5,
                'date' => '20 April 2026',
                'comment' => 'Packaging aman, tidak ada bocor. Bubuknya halus dan gampang larut.'
            ]
        ];
        
        return view('products.show', compact('product', 'selectedHubId', 'selectedWarehouseId', 'relatedProducts', 'dummyReviews'));
    }

    public function quickView($identifier)
    {
        $product = Product::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();
            
        Auth::user()?->forgetOwnHubShoppingSelectionIfSet();
        $selectedHubId = session('selected_hub_id');
        return view('themes.nest.partials.quick-view-content', compact('product', 'selectedHubId'))->render();
    }
}
