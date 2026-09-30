<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventSuperAdminFromBuyer
{
    /**
     * Halaman pembelian toko depan ditolak untuk role warehouse dan super admin.
     */
    public static function deny(Request $request): ?Response
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $isWarehouse = $user->role === User::ROLE_WAREHOUSE;

        if (! $isWarehouse && ! $user->isSuperAdmin()) {
            return null;
        }

        $message = $isWarehouse
            ? 'Akun dengan role warehouse tidak dapat mengakses halaman pembelian di toko depan.'
            : 'Super Admin tidak diizinkan mengakses halaman pembeli.';

        $redirect = $isWarehouse && $user->warehouse_id
            ? route('warehouse.dashboard')
            : ($user->isSuperAdmin() ? route('admin.dashboard') : route('home'));

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('error', $message);

            return response()->json([
                'error' => $message,
                'redirect' => $redirect,
            ], 403);
        }

        return redirect()->to($redirect)->with('error', $message);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($denied = self::deny($request)) {
            return $denied;
        }

        return $next($request);
    }
}
