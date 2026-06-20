<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;
use App\Models\WebsitePopup;
use App\Support\PromoProducts;

class HomeController extends Controller
{
    public function index()
    {
        $selectedHubId = session('selected_hub_id');
        $baseQuery = Product::where('status', 'active')
            ->withBuyerPrice()
            ->orderByInStockFirst($selectedHubId);

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
            
        $categories = Category::forStorefrontSidebar()->take(10);

        $brands = Brand::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->take(10)
            ->get();

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
            ->get()
            ->each(function (Menu $menu) {
                $menu->setRelation(
                    'details',
                    $menu->details->filter(
                        fn ($detail) => $detail->product && (float) $detail->product->price > 0
                    )
                );
            });

        $promoProducts = PromoProducts::get(10, withActivePromos: true);

        return view('themes.nest.home.index', compact(
            'popularProducts', 
            'topSelling', 
            'trendingProducts', 
            'recentlyAdded', 
            'topRated', 
            'categories',
            'brands', 
            'sliders',
            'activePopups',
            'todayMenus',
            'promoProducts'
        ));
    }
}









