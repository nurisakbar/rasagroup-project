<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHubSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Exclude system routes, admin panels, and auth routes
        if ($request->is('admin/*') || 
            $request->is('warehouse/*') || 
            $request->is('distributor/*') || 
            $request->is('driippreneur/*') || 
            $request->is('login') || 
            $request->is('register') || 
            $request->is('logout') || 
            $request->is('password/*') ||
            $request->routeIs('webhooks.*')) {
            return $next($request);
        }

        // 2. Allow public browsing routes so geolocation can run in the background
        // This is the "smooth" experience: user can land on home/products and location will be detected
        if ($request->is('/') || 
            $request->is('about') || 
            $request->is('contact') || 
            $request->is('p/*') ||
            $request->is('products*') || 
            $request->is('promo*') || 
            $request->is('hubs*') || 
            $request->routeIs('hubs.*') ||
            $request->routeIs('cart.product-stock')) {
            return $next($request);
        }

        // 3. Check if hub is selected in session
        if (!session()->has('selected_hub_id')) {
            // Check if cookie exists to restore session
            if ($request->hasCookie('selected_hub_id')) {
                $hubId = $request->cookie('selected_hub_id');
                $hubName = $request->cookie('selected_hub_name');
                $hubSlug = $request->cookie('selected_hub_slug');
                
                session([
                    'selected_hub_id' => $hubId,
                    'selected_hub_name' => $hubName,
                    'selected_hub_slug' => $hubSlug
                ]);
                
                return $next($request);
            }

            // 4. Force redirect for "action" routes (Cart, Checkout, Buyer Profile, etc.)
            // We only redirect if they are NOT on a whitelisted browsing route
            return redirect()->route('hubs.index')->with('info', 'Silakan pilih Hub/Distributor terdekat untuk melihat ketersediaan barang dan mulai berbelanja.');
        }

        return $next($request);
    }
}
