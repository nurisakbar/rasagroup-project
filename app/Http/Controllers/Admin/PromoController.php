<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Promo::query()->with('products');

            if ($request->filled('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->currentlyActive();
                } elseif ($request->status === 'inactive') {
                    $query->where(function ($q) {
                        $q->where('awal', '>', now())
                            ->orWhere('akhir', '<', now());
                    });
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('masa_berlaku', function ($promo) {
                    return $promo->awal->format('d/m/Y H:i') . ' - ' . $promo->akhir->format('d/m/Y H:i');
                })
                ->addColumn('status_badge', function ($promo) {
                    if ($promo->isCurrentlyActive()) {
                        return '<span class="label label-success">Aktif</span>';
                    }

                    return '<span class="label label-danger">Nonaktif</span>';
                })
                ->addColumn('products_display', function ($promo) {
                    if ($promo->products->isEmpty()) {
                        return '<span class="text-muted">—</span>';
                    }

                    $defaultImage = asset('adminlte/img/default-50x50.gif');
                    $items = $promo->products->map(function (Product $product) use ($defaultImage) {
                        $imageUrl = $product->image_url ?: $defaultImage;
                        $name = e($product->full_name);

                        return '
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <img src="' . e($imageUrl) . '" alt="' . $name . '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                <span>' . $name . '</span>
                            </div>
                        ';
                    })->implode('');

                    return '<div class="promo-products-list">' . $items . '</div>';
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
                ->rawColumns(['products_display', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.promos.index');
    }

    public function create()
    {
        $selectedProducts = $this->resolveSelectedProducts(
            is_array(old('product_ids')) ? old('product_ids') : []
        );

        return view('admin.promos.create', compact('selectedProducts'));
    }

    public function searchProducts(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));

        $query = Product::query()
            ->where('status', 'active')
            ->orderByRaw('COALESCE(commercial_name, name) ASC');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('commercial_name', 'like', "%{$keyword}%");
            });
        }

        $products = $query->limit(20)->get();

        return response()->json([
            'results' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'text' => $product->full_name,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_promo' => 'nullable|string|max:50|unique:promos,kode_promo',
            'judul_promo' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deskripsi' => 'nullable|string',
            'awal' => 'required|date',
            'akhir' => 'required|date|after_or_equal:awal',
            'target_audience' => 'required|array|min:1',
            'target_audience.*' => 'in:umum,affiliator,distributor',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'uuid|exists:products,id',
        ]);

        $validated['slug'] = $this->uniquePromoSlug($validated['judul_promo']);
        $validated['kode_promo'] = filled($validated['kode_promo'] ?? null) ? $validated['kode_promo'] : null;
        $validated['deskripsi'] = filled($validated['deskripsi'] ?? null) ? $validated['deskripsi'] : null;
        $validated['image'] = $request->hasFile('image')
            ? $request->file('image')->store('promos', 'public')
            : null;

        $productIds = $validated['product_ids'];
        unset($validated['product_ids']);

        $promo = Promo::create($validated);
        $promo->products()->sync($productIds);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promo $promo)
    {
        $promo->load('products');

        $selectedProductIds = is_array(old('product_ids'))
            ? old('product_ids')
            : $promo->products->pluck('id')->all();

        $selectedProducts = $this->resolveSelectedProducts($selectedProductIds);

        return view('admin.promos.edit', compact('promo', 'selectedProducts'));
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validate([
            'kode_promo' => 'nullable|string|max:50|unique:promos,kode_promo,' . $promo->id,
            'judul_promo' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deskripsi' => 'nullable|string',
            'awal' => 'required|date',
            'akhir' => 'required|date|after_or_equal:awal',
            'target_audience' => 'required|array|min:1',
            'target_audience.*' => 'in:umum,affiliator,distributor',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'uuid|exists:products,id',
        ]);

        $validated['slug'] = $this->uniquePromoSlug($validated['judul_promo'], $promo->id);

        unset($validated['kode_promo'], $validated['deskripsi']);

        if ($request->hasFile('image')) {
            $promo->deleteStoredImageFile();
            $validated['image'] = $request->file('image')->store('promos', 'public');
        } else {
            unset($validated['image']);
        }

        $productIds = $validated['product_ids'];
        unset($validated['product_ids']);

        $promo->update($validated);
        $promo->products()->sync($productIds);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        $promo->products()->detach();
        $promo->deleteStoredImageFile();
        $promo->delete();

        return redirect()->route('admin.promos.index')
            ->with('success', 'Promo berhasil dihapus.');
    }

    /**
     * @param  array<int, string>|null  $ids
     */
    private function resolveSelectedProducts(?array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $ids)
            ->orderByRaw('COALESCE(commercial_name, name) ASC')
            ->get();
    }

    /**
     * Slug URL dari judul; unik di tabel (suffix -1, -2, … bila bentrok).
     */
    private function uniquePromoSlug(string $judul, ?string $exceptPromoId = null): string
    {
        $slug = Str::slug($judul);
        if ($slug === '') {
            $slug = 'promo';
        }
        $original = $slug;
        $n = 1;
        while (Promo::query()
            ->where('slug', $slug)
            ->when($exceptPromoId, fn ($q) => $q->where('id', '!=', $exceptPromoId))
            ->exists()) {
            $slug = $original.'-'.$n;
            $n++;
        }

        return $slug;
    }
}
