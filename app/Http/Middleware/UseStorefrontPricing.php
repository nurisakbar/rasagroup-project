<?php

namespace App\Http\Middleware;

use App\Support\Pricing\PricingContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Harga produk di storefront mengikuti harga master hub terpilih. Panel admin & warehouse
 * tetap melihat harga global produk.
 */
class UseStorefrontPricing
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*', 'warehouse', 'warehouse/*')) {
            PricingContext::current()->enableStorefront();
        }

        return $next($request);
    }
}
