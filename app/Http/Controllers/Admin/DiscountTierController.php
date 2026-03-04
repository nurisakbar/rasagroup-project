<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountTier;
use Illuminate\Http\Request;

class DiscountTierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discountTiers = DiscountTier::orderBy('min_purchase', 'asc')->get();
        return view('admin.discount_tiers.index', compact('discountTiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.discount_tiers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'min_purchase' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ], [
            'min_purchase.required' => 'Minimal pembelian wajib diisi.',
            'min_purchase.numeric' => 'Minimal pembelian harus berupa angka.',
            'discount_percent.required' => 'Persentase diskon wajib diisi.',
            'discount_percent.max' => 'Persentase diskon tidak boleh lebih dari 100%.',
        ]);

        DiscountTier::create([
            'min_purchase' => $request->min_purchase,
            'discount_percent' => $request->discount_percent,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.discount-tiers.index')->with('success', 'Potongan harga berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiscountTier $discountTier)
    {
        return view('admin.discount_tiers.edit', compact('discountTier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DiscountTier $discountTier)
    {
        $request->validate([
            'min_purchase' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
        ]);

        $discountTier->update([
            'min_purchase' => $request->min_purchase,
            'discount_percent' => $request->discount_percent,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.discount-tiers.index')->with('success', 'Potongan harga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiscountTier $discountTier)
    {
        $discountTier->delete();
        return redirect()->route('admin.discount-tiers.index')->with('success', 'Potongan harga berhasil dihapus.');
    }
}
