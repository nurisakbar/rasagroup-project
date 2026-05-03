<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Promo;
use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;
use App\Models\WebsitePopup;

class HomeController extends Controller
{
    public function index()
    {
        $selectedHubId = session('selected_hub_id');
        $baseQuery = Product::where('status', 'active')->orderByInStockFirst($selectedHubId);

        $popularProducts = (clone $baseQuery)
            ->with(['category', 'brand', 'warehouseStocks'])
            ->latest()
            ->take(10)
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
                ->latest()
                ->take(8)
                ->get();
        }

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activePopups = WebsitePopup::where('is_active', true)->latest()->get();

        $todayMenus = Menu::query()
            ->where('status_aktif', true)
            ->currentlyVisible()
            ->with(['details.product'])
            ->orderBy('nama_menu')
            ->take(4)
            ->get();

        $homePromos = Promo::query()
            ->where('awal', '<=', now())
            ->where('akhir', '>=', now())
            ->orderBy('akhir')
            ->take(4)
            ->get();

        return view('themes.nest.home.index', compact(
            'popularProducts', 
            'topSelling', 
            'trendingProducts', 
            'recentlyAdded', 
            'topRated', 
            'categories', 
            'categoryProducts', 
            'sliders',
            'activePopups',
            'todayMenus',
            'homePromos'
        ));
    }
}









