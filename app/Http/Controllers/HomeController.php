<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;
use App\Models\WebsitePopup;

class HomeController extends Controller
{
    public function index()
    {
        $selectedHubId = session('selected_hub_id');
        
        $baseQuery = Product::where('status', 'active');
        
        if ($selectedHubId) {
            $baseQuery->whereHas('warehouseStocks', function($q) use ($selectedHubId) {
                $q->where('warehouse_id', $selectedHubId)->where('stock', '>', 0);
            });
        } else {
            $baseQuery->whereHas('warehouseStocks', function($q) {
                $q->where('stock', '>', 0);
            });
        }

        $popularProducts = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->latest()
            ->take(10)
            ->get();

        $dailyBestSells = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        $topSelling = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->orderBy('price', 'desc')
            ->take(3)
            ->get();

        $trendingProducts = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->inRandomOrder()
            ->take(3)
            ->get();

        $recentlyAdded = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->latest()
            ->take(3)
            ->get();

        $topRated = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->inRandomOrder()
            ->take(3)
            ->get();
            
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->take(10)
            ->get(); // Limit categories for tabs

        // Get products for each category for the tabs
        $categoryProducts = [];
        foreach($categories as $category) {
            $categoryProducts[$category->id] = (clone $baseQuery)
                ->where('category_id', $category->id)
                ->with(['category', 'brand', 'warehouseStocks'])
                ->take(8)
                ->get();
        }

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activePopups = WebsitePopup::where('is_active', true)->latest()->get();

        return view('themes.nest.home.index', compact(
            'popularProducts', 
            'dailyBestSells', 
            'topSelling', 
            'trendingProducts', 
            'recentlyAdded', 
            'topRated', 
            'categories', 
            'categoryProducts', 
            'sliders',
            'activePopups'
        ));
    }
}









