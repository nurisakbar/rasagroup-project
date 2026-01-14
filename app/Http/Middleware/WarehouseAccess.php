<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WarehouseAccess
{
    /**
     * Handle an incoming request.
     * Allow access for warehouse role users.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('warehouse.login');
        }

        $user = auth()->user();

        // Allow warehouse users
        if ($user->role === 'warehouse' && $user->warehouse_id) {
            return $next($request);
        }

        // Deny access
        abort(403, 'Akses ditolak. Anda bukan staff warehouse.');
    }
}
