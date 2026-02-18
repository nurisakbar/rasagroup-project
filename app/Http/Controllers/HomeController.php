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
        $popularProducts = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->latest()
            ->take(10)
            ->get();

        $dailyBestSells = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        $topSelling = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->orderBy('price', 'desc')
            ->take(3)
            ->get();

        $trendingProducts = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->inRandomOrder()
            ->take(3)
            ->get();

        $recentlyAdded = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->latest()
            ->take(3)
            ->get();

        $topRated = Product::where('status', 'active')
            ->with(['category', 'brand'])
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
            $categoryProducts[$category->id] = Product::where('status', 'active')
                ->where('category_id', $category->id)
                ->with(['category', 'brand'])
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









