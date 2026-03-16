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
        $discountTiers = DiscountTier::orderBy('min_quantity', 'asc')->get();
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
            'min_quantity' => 'required|integer|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ], [
            'min_quantity.required' => 'Minimal item belanja wajib diisi.',
            'min_quantity.integer' => 'Minimal item belanja harus berupa angka bulat.',
            'discount_percent.required' => 'Persentase diskon wajib diisi.',
            'discount_percent.max' => 'Persentase diskon tidak boleh lebih dari 100%.',
        ]);

        DiscountTier::create([
            'min_quantity' => $request->min_quantity,
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
            'min_quantity' => 'required|integer|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
        ], [
            'min_quantity.required' => 'Minimal item belanja wajib diisi.',
            'min_quantity.integer' => 'Minimal item belanja harus berupa angka bulat.',
            'discount_percent.required' => 'Persentase diskon wajib diisi.',
            'discount_percent.max' => 'Persentase diskon tidak boleh lebih dari 100%.',
        ]);

        $discountTier->update([
            'min_quantity' => $request->min_quantity,
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
