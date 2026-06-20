<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Support\PromoProducts;

class PromoController extends Controller
{
    public function index()
    {
        $products = PromoProducts::get(withActivePromos: true);

        return view('themes.nest.promo.index', compact('products'));
    }

    public function show($slug)
    {
        $promo = Promo::where('slug', $slug)
            ->with(['products' => fn ($q) => $q->where('status', 'active')->with(['category', 'brand', 'warehouseStocks'])])
            ->firstOrFail();

        return view('themes.nest.promo.show', compact('promo'));
    }
}
