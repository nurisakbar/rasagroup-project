<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceLevel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PriceLevelController extends Controller
{
    /**
     * Display a listing of price levels.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PriceLevel::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('discount_formatted', function ($priceLevel) {
                    return number_format($priceLevel->discount_percentage, 2, ',', '.') . '%';
                })
                ->addColumn('status_badge', function ($priceLevel) {
                    if ($priceLevel->is_active) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Tidak Aktif</span>';
                })
                ->addColumn('products_count', function ($priceLevel) {
                    return $priceLevel->products()->count();
                })
                ->addColumn('action', function ($priceLevel) {
                    $html = '<a href="' . route('admin.price-levels.show', $priceLevel) . '" class="btn btn-info btn-xs" title="Detail">
                        <i class="fa fa-eye"></i>
                    </a> ';
                    $html .= '<a href="' . route('admin.price-levels.edit', $priceLevel) . '" class="btn btn-warning btn-xs" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a> ';
                    $html .= '<form action="' . route('admin.price-levels.destroy', $priceLevel) . '" method="POST" style="display: inline-block;" class="delete-form">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>';
                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.price-levels.index');
    }

    /**
     * Show the form for creating a new price level.
     */
    public function create()
    {
        return view('admin.price-levels.create');
    }

    /**
     * Store a newly created price level.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama level harus diisi.',
            'discount_percentage.required' => 'Persentase diskon harus diisi.',
            'discount_percentage.numeric' => 'Persentase diskon harus berupa angka.',
            'discount_percentage.min' => 'Persentase diskon minimal 0%.',
            'discount_percentage.max' => 'Persentase diskon maksimal 100%.',
        ]);

        PriceLevel::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.price-levels.index')
            ->with('success', 'Level harga berhasil ditambahkan.');
    }

    /**
     * Display the specified price level.
     */
    public function show(PriceLevel $priceLevel)
    {
        $priceLevel->load('products');
        return view('admin.price-levels.show', compact('priceLevel'));
    }

    /**
     * Show the form for editing the specified price level.
     */
    public function edit(PriceLevel $priceLevel)
    {
        return view('admin.price-levels.edit', compact('priceLevel'));
    }

    /**
     * Update the specified price level.
     */
    public function update(Request $request, PriceLevel $priceLevel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama level harus diisi.',
            'discount_percentage.required' => 'Persentase diskon harus diisi.',
            'discount_percentage.numeric' => 'Persentase diskon harus berupa angka.',
            'discount_percentage.min' => 'Persentase diskon minimal 0%.',
            'discount_percentage.max' => 'Persentase diskon maksimal 100%.',
        ]);

        $priceLevel->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.price-levels.index')
            ->with('success', 'Level harga berhasil diupdate.');
    }

    /**
     * Remove the specified price level.
     */
    public function destroy(PriceLevel $priceLevel)
    {
        $priceLevel->delete();

        return redirect()->route('admin.price-levels.index')
            ->with('success', 'Level harga berhasil dihapus.');
    }
}
