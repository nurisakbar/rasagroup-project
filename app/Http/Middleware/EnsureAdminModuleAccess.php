<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminAccess\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menegakkan hak akses modul panel admin: rute dipetakan ke modul dan aksi (lihat/tambah/ubah/hapus),
 * lalu dicek ke izin role. Rute admin yang belum terpetakan ditolak.
 */
class EnsureAdminModuleAccess
{
    private const ACTION_LABELS = ['view' => 'melihat', 'create' => 'menambah', 'edit' => 'mengubah', 'delete' => 'menghapus'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('admin_access.enforce', true)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return redirect()->route('admin.login');
        }
        if ($user->role === User::ROLE_SUPER_ADMIN || AdminAccess::isWhitelisted($routeName)) {
            return $next($request);
        }

        $moduleKey = AdminAccess::moduleKeyForRoute($routeName);
        if ($moduleKey === null) {
            Log::warning('Rute admin belum terpetakan ke modul, akses ditolak', ['route' => $routeName, 'user_id' => $user->id, 'role' => $user->role]);
            abort(403, 'Halaman ini belum terdaftar pada modul hak akses mana pun. Hubungi administrator.');
        }

        $action = AdminAccess::actionFor($routeName, $request->method());
        if (! AdminAccess::can($user->role, $moduleKey, $action)) {
            Log::info('Akses modul admin ditolak', ['route' => $routeName, 'module' => $moduleKey, 'action' => $action, 'user_id' => $user->id, 'role' => $user->role]);
            abort(403, 'Anda tidak memiliki izin untuk ' . self::ACTION_LABELS[$action] . ' pada modul ini.');
        }

        return $next($request);
    }
}
