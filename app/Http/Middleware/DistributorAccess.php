<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DistributorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isDistributor() || !Auth::user()->warehouse_id) {
            Auth::logout();
            return redirect()->route('distributor.login')
                ->withErrors(['email' => 'Anda tidak memiliki akses ke panel Distributor atau belum terdaftar di hub manapun.']);
        }

        return $next($request);
    }
}

