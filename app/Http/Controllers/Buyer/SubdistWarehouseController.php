<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Subdist memilih gudang distributor asal pesanan (bila distributor punya lebih dari satu gudang).
 */
class SubdistWarehouseController extends Controller
{
    public function select(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isSubdist(), 403);

        $request->validate(['warehouse_id' => ['required', 'string']]);

        $warehouse = $user->subdistWarehouseOptions()->firstWhere('id', $request->input('warehouse_id'));
        if (! $warehouse || ! $warehouse->isReadyForSubdist()) {
            return back()->with('error', 'Gudang tidak tersedia. Pilih gudang lain.');
        }

        session([
            'subdist_warehouse_id' => $warehouse->id,
            'selected_hub_id' => $warehouse->id,
            'selected_hub_name' => $warehouse->name,
            'selected_hub_slug' => $warehouse->slug,
        ]);

        // Keranjang mengikuti gudang yang dipilih; ketersediaan stok dicek ulang di checkout.
        Cart::where('user_id', $user->id)->where('cart_type', 'regular')->update(['warehouse_id' => $warehouse->id]);

        return back()->with('success', "Gudang asal pesanan: {$warehouse->name}.");
    }
}
