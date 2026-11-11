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
            if ($user->isSuperAdmin() || $user->isDistributor() || env('BYPASS_WA_VERIFICATION', true)) {
                return $next($request);
            }

            if (! $user->wa_verified_at) {
                // Kembali ke halaman asal setelah verifikasi (mis. halaman produk saat tambah ke keranjang).
                $returnTo = $request->isMethod('GET') && ! $request->expectsJson()
                    ? $request->fullUrl()
                    : url()->previous();
                $request->session()->put('url.intended', $returnTo);

                if ($request->expectsJson()) {
                    // Frontend (tambah ke keranjang, dll.) mengalihkan ke halaman verifikasi, bukan menampilkan error.
                    return response()->json([
                        'message' => 'Silakan verifikasi nomor WhatsApp Anda terlebih dahulu.',
                        'redirect' => route('wa.verify'),
                    ], 403);
                }

                return redirect()->route('wa.verify');
            }
        }

        return $next($request);
    }
}
