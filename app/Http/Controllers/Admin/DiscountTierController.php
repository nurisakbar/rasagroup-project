<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountTier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DiscountTierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DiscountTier::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('quantity_display', function ($tier) {
                    return number_format($tier->min_quantity, 0, ',', '.') . ' Item';
                })
                ->addColumn('discount_display', function ($tier) {
                    return $tier->discount_percent . ' %';
                })
                ->addColumn('status_badge', function ($tier) {
                    if ($tier->is_active) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($tier) {
                    $editUrl = route('admin.discount-tiers.edit', $tier);
                    $deleteUrl = route('admin.discount-tiers.destroy', $tier);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.discount_tiers.index');
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
