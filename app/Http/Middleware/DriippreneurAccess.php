<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DriippreneurAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isDriippreneur()) {
            Auth::logout();
            return redirect()->route('driippreneur.login')
                ->withErrors(['email' => 'Anda tidak memiliki akses ke panel DRiiPPreneur.']);
        }

        return $next($request);
    }
}

