<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Menu::with(['details.product'])->withCount('details');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image_display', function ($menu) {
                    if ($menu->gambar) {
                        return '<img src="' . $menu->image_url . '" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px;" alt="' . $menu->nama_menu . '">';
                    }
                    return '<i class="fa fa-image fa-2x text-muted"></i>';
                })
                ->addColumn('nama_menu', function ($menu) {
                    return '<strong>' . $menu->nama_menu . '</strong>';
                })
                ->addColumn('slug', function ($menu) {
                    return '<code class="text-maroon">' . $menu->slug . '</code>';
                })
                ->addColumn('product_names', function ($menu) {
                    $names = $menu->details->map(function ($detail) {
                        return '<span class="label label-default">' . ($detail->product->name ?? 'Produk Terhapus') . ' (' . $detail->jumlah . ')</span>';
                    })->implode(' ');
                    return $names ?: '<i class="text-muted">Tidak ada produk</i>';
                })
                ->addColumn('status_badge', function ($menu) {
                    if ($menu->status_aktif) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($menu) {
                    $editUrl = route('admin.menus.edit', $menu);
                    $deleteUrl = route('admin.menus.destroy', $menu);
                    
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
                ->rawColumns(['image_display', 'nama_menu', 'slug', 'product_names', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.menus.index');
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        return view('admin.menus.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_aktif' => 'nullable',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'nama_menu' => $request->nama_menu,
                'deskripsi' => $request->deskripsi,
                'status_aktif' => $request->has('status_aktif'),
            ];

            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('menus', 'public');
            }

            $menu = Menu::create($data);

            foreach ($request->details as $detail) {
                MenuDetail::create([
                    'menu_id' => $menu->id,
                    'product_id' => $detail['product_id'],
                    'jumlah' => $detail['jumlah'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Menu $menu)
    {
        $menu->load('details.product');
        $products = Product::where('status', 'active')->orderBy('name')->get();
        return view('admin.menus.edit', compact('menu', 'products'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_aktif' => 'nullable',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'nama_menu' => $request->nama_menu,
                'deskripsi' => $request->deskripsi,
                'status_aktif' => $request->has('status_aktif'),
            ];

            if ($request->hasFile('gambar')) {
                if ($menu->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($menu->gambar)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($menu->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('menus', 'public');
            }

            $menu->update($data);

            // Simple way: delete and recreate details
            $menu->details()->delete();

            foreach ($request->details as $detail) {
                MenuDetail::create([
                    'menu_id' => $menu->id,
                    'product_id' => $detail['product_id'],
                    'jumlah' => $detail['jumlah'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Menu $menu)
    {
        $menu->delete(); // details deleted by cascade
        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil dihapus');
    }
}
