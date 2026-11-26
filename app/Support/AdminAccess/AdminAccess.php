<?php

namespace App\Support\AdminAccess;

use App\Models\AdminModule;
use App\Models\AdminRolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Pusat pengecekan hak akses modul panel admin (dinamis dari database).
 *
 * - super_admin selalu boleh semuanya.
 * - Modul dipetakan dari nama rute lewat route_patterns (pola paling spesifik menang).
 * - Aksi dipetakan dari nama rute dan metode HTTP: lihat, tambah, ubah, hapus.
 * - Rute admin yang tidak terpetakan ke modul ditolak (gagal tertutup), kecuali daftar WHITELIST.
 */
class AdminAccess
{
    private const CACHE_KEY = 'admin_access.v1';

    /** Rute admin yang tidak butuh modul (semua pengguna panel boleh). */
    public const WHITELIST = ['admin.dashboard', 'admin.logout', 'admin.login', 'admin.', 'admin.profile.*'];

    /** @var array{modules: array<string, array<string, mixed>>, perms: array<string, array<string, array<string, bool>>>}|null */
    private static ?array $memo = null;

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        self::$memo = null;
    }

    /** @return array{modules: array<string, array<string, mixed>>, perms: array<string, array<string, array<string, bool>>>} */
    private static function data(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        return self::$memo = Cache::rememberForever(self::CACHE_KEY, function () {
            $modules = AdminModule::orderBy('sort_order')->orderBy('name')->get();
            $keyById = $modules->pluck('key', 'id');

            $perms = [];
            foreach (AdminRolePermission::all() as $p) {
                $key = $keyById[$p->module_id] ?? null;
                if ($key) {
                    $perms[$p->role][$key] = ['view' => $p->can_view, 'create' => $p->can_create, 'edit' => $p->can_edit, 'delete' => $p->can_delete];
                }
            }

            return [
                'modules' => $modules->mapWithKeys(fn (AdminModule $m) => [$m->key => [
                    'key' => $m->key, 'name' => $m->name, 'group' => $m->group, 'icon' => $m->icon,
                    'menu_route' => $m->menu_route, 'patterns' => $m->route_patterns ?? [], 'sort' => $m->sort_order,
                    'show_in_menu' => $m->show_in_menu, 'is_active' => $m->is_active,
                ]])->all(),
                'perms' => $perms,
            ];
        });
    }

    /** Apakah role ini boleh melakukan aksi pada modul (key). */
    public static function can(?string $role, string $moduleKey, string $action = 'view'): bool
    {
        if ($role === null || $role === '') {
            return false;
        }
        if ($role === User::ROLE_SUPER_ADMIN) {
            return true;
        }
        if (in_array($moduleKey, DefaultModules::SUPER_ADMIN_ONLY, true)) {
            return false;
        }

        $data = self::data();
        $module = $data['modules'][$moduleKey] ?? null;
        if (! $module || ! $module['is_active']) {
            return false;
        }

        $perm = $data['perms'][$role][$moduleKey] ?? null;
        if (! $perm) {
            return false;
        }

        // Tambah/ubah/hapus mengharuskan akses lihat juga.
        return $action === 'view' ? $perm['view'] : ($perm['view'] && ($perm[$action] ?? false));
    }

    public static function canUser(?User $user, string $moduleKey, string $action = 'view'): bool
    {
        return $user !== null && self::can($user->role, $moduleKey, $action);
    }

    /** Modul untuk menu samping: yang aktif, tampil di menu, dan boleh dilihat role ini. */
    public static function menuFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return array_values(array_filter(
            self::data()['modules'],
            fn (array $m) => $m['show_in_menu'] && $m['is_active'] && $m['menu_route'] && self::can($user->role, $m['key'], 'view')
        ));
    }

    /** Cari modul yang memiliki rute ini (pola paling spesifik menang). Mengembalikan key atau null. */
    public static function moduleKeyForRoute(string $routeName): ?string
    {
        $best = null;
        $bestScore = -1;
        foreach (self::data()['modules'] as $key => $m) {
            foreach ($m['patterns'] as $pattern) {
                $score = self::matchScore($pattern, $routeName);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $key;
                }
            }
        }

        return $best;
    }

    /** Skor kecocokan: nama persis lebih spesifik dari pola berakhiran .*; awalan lebih panjang lebih spesifik. */
    private static function matchScore(string $pattern, string $name): int
    {
        if ($pattern === $name) {
            return 10000 + strlen($pattern);
        }
        if (str_contains($pattern, '*') && fnmatch($pattern, $name)) {
            return strlen(str_replace('*', '', $pattern));
        }

        return -1;
    }

    public static function isWhitelisted(string $routeName): bool
    {
        foreach (self::WHITELIST as $pattern) {
            if ($pattern === $routeName || (str_contains($pattern, '*') && fnmatch($pattern, $routeName))) {
                return true;
            }
        }

        return false;
    }

    /** Petakan nama rute dan metode HTTP ke aksi: view | create | edit | delete. */
    public static function actionFor(string $routeName, string $method): string
    {
        $method = strtoupper($method);
        $tokens = explode('.', $routeName);

        foreach ($tokens as $t) {
            if ($method === 'DELETE' || preg_match('/^(destroy|remove|delete)/', $t)) {
                return 'delete';
            }
        }
        foreach ($tokens as $t) {
            if (in_array($t, ['create', 'store', 'import', 'import-active'], true) || str_starts_with($t, 'add-')) {
                return 'create';
            }
        }
        if (in_array($method, ['PUT', 'PATCH', 'POST'], true) || in_array('edit', $tokens, true)) {
            return 'edit';
        }

        return 'view';
    }

    /** Modul untuk matriks pengaturan: semua modul, urut menu. */
    public static function allModules(): array
    {
        return array_values(self::data()['modules']);
    }

    /** Izin satu role (untuk matriks): key modul => [view, create, edit, delete]. */
    public static function permissionsOf(string $role): array
    {
        return self::data()['perms'][$role] ?? [];
    }
}
