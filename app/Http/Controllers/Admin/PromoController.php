<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Promo::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($promo) {
                    if ($promo->image) {
                        return '<img src="' . asset('storage/' . $promo->image) . '" class="img-thumbnail" style="height: 50px;">';
                    }
                    return '<span class="label label-default">No Image</span>';
                })
                ->addColumn('harga_format', function ($promo) {
                    return 'Rp ' . number_format($promo->harga, 0, ',', '.');
                })
                ->addColumn('masa_berlaku', function ($promo) {
                    return $promo->awal->format('d/m/Y') . ' - ' . $promo->akhir->format('d/m/Y');
                })
                ->addColumn('action', function ($promo) {
                    $editUrl = route('admin.promos.edit', $promo);
                    $deleteUrl = route('admin.promos.destroy', $promo);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('admin.promos.index');
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_promo' => 'required|string|max:50|unique:promos,kode_promo',
            'judul_promo' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:promos,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'awal' => 'required|date',
            'akhir' => 'required|date|after_or_equal:awal',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['judul_promo']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('promos', 'public');
        }

        Promo::create($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validate([
            'kode_promo' => 'required|string|max:50|unique:promos,kode_promo,' . $promo->id,
            'judul_promo' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:promos,slug,' . $promo->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'awal' => 'required|date',
            'akhir' => 'required|date|after_or_equal:awal',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['judul_promo']);
        }

        if ($request->hasFile('image')) {
            if ($promo->image) {
                Storage::disk('public')->delete($promo->image);
            }
            $validated['image'] = $request->file('image')->store('promos', 'public');
        }

        $promo->update($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        if ($promo->image) {
            Storage::disk('public')->delete($promo->image);
        }
        
        $promo->delete();

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil dihapus.');
    }
}
