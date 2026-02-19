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
        // Exclude routes that don't need hub selection
        // Admin, auth, and the hub selection routes themselves
        if ($request->is('admin/*') || 
            $request->is('warehouse/*') || 
            $request->is('distributor/*') || 
            $request->is('driippreneur/*') || 
            $request->is('login') || 
            $request->is('register') || 
            $request->is('logout') || 
            $request->routeIs('hubs.index') || 
            $request->routeIs('hubs.select') ||
            $request->routeIs('hubs.get-regencies') || // Allow ajax calls
            $request->routeIs('webhooks.*')) {
            return $next($request);
        }

        // Check if hub is selected in session
        if (!session()->has('selected_hub_id')) {
            // Check if cookie exists to restore session
            if ($request->hasCookie('selected_hub_id')) {
                $hubId = $request->cookie('selected_hub_id');
                // Verify if warehouse still exists and is active (optional but good practice)
                // For performance, we might skip DB check or trust the ID
                session(['selected_hub_id' => $hubId]);
                
                // Optionally verify name from cookie or DB if needed, but ID is enough for logic
                if ($request->hasCookie('selected_hub_name')) {
                    session(['selected_hub_name' => $request->cookie('selected_hub_name')]);
                }
                
                return $next($request);
            }

            // If no session and no cookie, redirect to hub selection
            return redirect()->route('hubs.index')->with('info', 'Silakan pilih Hub/Distributor terdekat untuk melanjutkan belanja.');
        }

        return $next($request);
    }
}
