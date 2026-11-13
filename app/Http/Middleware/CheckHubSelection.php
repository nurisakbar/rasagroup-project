<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Outlet/reseller yang terdaftar di sebuah hub: hub belanja di session dikunci ke hub tersebut,
 * sehingga stok & harga di toko selalu dari hub terdaftar. Isi keranjang ikut dipindah ke hub itu.
 */
class CheckHubSelection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*', 'warehouse', 'warehouse/*') && Auth::guard('web')->check()) {
            $hub = Auth::guard('web')->user()->registeredShoppingHub();

            if ($hub && (string) session('selected_hub_id') !== (string) $hub->id) {
                session([
                    'selected_hub_id' => $hub->id,
                    'selected_hub_name' => $hub->name,
                    'selected_hub_slug' => $hub->slug,
                ]);
                cookie()->queue('selected_hub_id', $hub->id, 60 * 24 * 30);
                cookie()->queue('selected_hub_name', $hub->name, 60 * 24 * 30);
                cookie()->queue('selected_hub_slug', $hub->slug, 60 * 24 * 30);

                Cart::where('user_id', Auth::guard('web')->id())
                    ->where('cart_type', 'regular')
                    ->where(fn ($q) => $q->whereNull('warehouse_id')->orWhere('warehouse_id', '!=', $hub->id))
                    ->update(['warehouse_id' => $hub->id]);
            }
        }

        return $next($request);
    }
}
