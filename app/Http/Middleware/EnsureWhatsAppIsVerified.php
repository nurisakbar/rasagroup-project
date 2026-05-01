<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWhatsAppIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Bypass verification for admin and distributor
            if ($user->isSuperAdmin() || $user->isDistributor()) {
                return $next($request);
            }

            if (! $user->wa_verified_at) {
                return $request->expectsJson()
                        ? abort(403, 'Your WhatsApp number is not verified.')
                        : redirect()->route('wa.verify');
            }
        }

        return $next($request);
    }
}
