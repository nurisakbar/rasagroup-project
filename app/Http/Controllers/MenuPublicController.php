<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuPublicController extends Controller
{
    /**
     * Daftar menu paket yang aktif & dalam jadwal tampil.
     */
    public function index(): View
    {
        $menus = Menu::query()
            ->where('status_aktif', true)
            ->currentlyVisible()
            ->with(['details.product'])
            ->withCount('details')
            ->orderBy('nama_menu')
            ->paginate(12);

        return view('themes.nest.menu.index', compact('menus'));
    }

    /**
     * Detail satu menu (slug).
     */
    public function show(string $slug): View
    {
        $menu = Menu::query()
            ->where('slug', $slug)
            ->where('status_aktif', true)
            ->firstOrFail();

        if (! $menu->isWithinDisplayWindow()) {
            abort(404);
        }

        $hubId = session('selected_hub_id');
        $selectedHub = $hubId
            ? Warehouse::query()->where('id', $hubId)->where('is_active', true)->first()
            : null;

        $menu->load([
            'details.product' => function ($q) use ($hubId) {
                if ($hubId) {
                    $q->with(['warehouseStocks' => function ($w) use ($hubId) {
                        $w->where('warehouse_id', $hubId);
                    }]);
                }
            },
        ]);

        return view('themes.nest.menu.show', compact('menu', 'selectedHub'));
    }

    /**
     * Tambahkan semua produk komposisi menu ke keranjang (hub dari sesi), sesuai stok tersedia.
     */
    public function addCompositionToCart(Request $request, string $slug): RedirectResponse
    {
        $menu = Menu::query()
            ->where('slug', $slug)
            ->where('status_aktif', true)
            ->firstOrFail();

        if (! $menu->isWithinDisplayWindow()) {
            abort(404);
        }

        $hubId = session('selected_hub_id');
        if (! $hubId) {
            return back()->with('error', 'Pilih hub pengirim terlebih dahulu.');
        }

        $warehouse = Warehouse::query()
            ->where('id', $hubId)
            ->where('is_active', true)
            ->first();

        if (! $warehouse) {
            return back()->with('error', 'Hub tidak valid atau tidak tersedia.');
        }

        $menu->load(['details.product']);
        $cartController = app(CartController::class);

        $linesAdded = 0;
        $linesPartial = 0;
        $linesSkippedStock = 0;

        foreach ($menu->details as $detail) {
            $product = $detail->product;
            if (! $product || $product->status !== 'active') {
                continue;
            }

            $requestedBase = $product->orderedQuantityToBase(max(1, (int) $detail->jumlah), 'base');
            if ($requestedBase < 1) {
                continue;
            }

            $merge = $cartController->mergeRegularCartLine($product, $warehouse, $requestedBase, true, 'base', null);
            if (! $merge['ok']) {
                return back()->with('error', $merge['error']);
            }

            if (($merge['added'] ?? 0) < 1) {
                $linesSkippedStock++;
            } else {
                $linesAdded++;
                if (! empty($merge['partial'])) {
                    $linesPartial++;
                }
            }
        }

        if ($linesAdded === 0) {
            return back()->with(
                'error',
                'Tidak ada produk komposisi yang bisa ditambahkan — stok di hub ini habis atau tidak mencukupi.'
            );
        }

        $msg = 'Komposisi menu ditambahkan ke keranjang untuk produk yang memiliki stok.';
        if ($linesPartial > 0) {
            $msg .= ' Sebagian kuantitas disesuaikan dengan stok yang tersedia.';
        }
        if ($linesSkippedStock > 0) {
            $msg .= ' Item tanpa stok dilewati.';
        }

        return back()->with('success', $msg);
    }
}
