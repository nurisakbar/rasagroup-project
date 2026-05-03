<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::query()
            ->where('awal', '<=', now())
            ->where('akhir', '>=', now())
            ->orderBy('akhir')
            ->get();

        return view('themes.nest.promo.index', compact('promos'));
    }

    public function show($slug)
    {
        $promo = Promo::where('slug', $slug)->firstOrFail();

        return view('themes.nest.promo.show', compact('promo'));
    }
}
