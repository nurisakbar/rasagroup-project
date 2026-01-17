<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'active')
            ->with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::where('is_active', true)
            ->take(6)
            ->get();

        return view('home', compact('products', 'categories'));
    }
}









