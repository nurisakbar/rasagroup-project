<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminModule;
use App\Models\AdminRolePermission;
use App\Models\User;
use App\Support\AdminAccess\AdminAccess;
use App\Support\AdminAccess\DefaultModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Hak akses modul per role: matriks lihat/tambah/ubah/hapus. Hanya super_admin (dijaga middleware
 * dan modul access_control yang tetap).
 */
class AccessControlController extends Controller
{
    /** Role yang bisa diatur (super_admin selalu penuh dan tidak ditampilkan). */
    public static function manageableRoles(): array
    {
        return array_values(array_filter(User::ADMIN_PANEL_ROLES, fn ($r) => $r !== User::ROLE_SUPER_ADMIN));
    }

    public static function roleLabel(string $role): string
    {
        $u = new User;
        $u->role = $role;

        return $u->role_label;
    }

    public function index()
    {
        $modules = AdminModule::where('is_active', true)->count();
        $roles = collect(self::manageableRoles())->map(function (string $role) {
            $perms = AdminAccess::permissionsOf($role);

            return [
                'role' => $role,
                'label' => self::roleLabel($role),
                'users' => User::where('role', $role)->count(),
                'view' => count(array_filter($perms, fn ($p) => $p['view'])),
                'full' => count(array_filter($perms, fn ($p) => $p['view'] && $p['create'] && $p['edit'] && $p['delete'])),
                'changed' => DefaultModules::changedCount($role, $perms),
            ];
        });

        return view('admin.access.index', compact('roles', 'modules'));
    }

    public function editRole(string $role)
    {
        $this->assertManageable($role);
        $modules = AdminModule::whereNotIn('key', DefaultModules::SUPER_ADMIN_ONLY)
            ->orderBy('sort_order')->orderBy('name')->get();
        $perms = AdminRolePermission::where('role', $role)->get()->keyBy('module_id');

        return view('admin.access.role', [
            'role' => $role,
            'roleLabel' => self::roleLabel($role),
            'modules' => $modules,
            'perms' => $perms,
            'users' => User::where('role', $role)->count(),
            'defaults' => collect(DefaultModules::definitions())->map(fn ($d, $key) => DefaultModules::isDefaultGranted($role, $key))->all(),
            'changedCount' => DefaultModules::changedCount($role, AdminAccess::permissionsOf($role)),
        ]);
    }

    public function updateRole(Request $request, string $role): RedirectResponse
    {
        $this->assertManageable($role);

        $modules = AdminModule::whereNotIn('key', DefaultModules::SUPER_ADMIN_ONLY)->get();
        $input = (array) $request->input('perm', []);
        $before = AdminRolePermission::where('role', $role)->count();
        $granted = 0;

        DB::transaction(function () use ($modules, $input, $role, &$granted) {
            foreach ($modules as $module) {
                $row = (array) ($input[$module->id] ?? []);
                $create = ! empty($row['create']);
                $edit = ! empty($row['edit']);
                $delete = ! empty($row['delete']);
                // Tambah/ubah/hapus mengharuskan akses lihat.
                $view = ! empty($row['view']) || $create || $edit || $delete;

                if (! $view) {
                    AdminRolePermission::where('role', $role)->where('module_id', $module->id)->delete();

                    continue;
                }
                AdminRolePermission::updateOrCreate(
                    ['role' => $role, 'module_id' => $module->id],
                    ['can_view' => true, 'can_create' => $create, 'can_edit' => $edit, 'can_delete' => $delete]
                );
                $granted++;
            }
        });

        AdminAccess::flush();
        Log::info('Hak akses role diperbarui', [
            'by_user_id' => auth('admin')->id(), 'role' => $role, 'modul_diakses_sebelum' => $before, 'modul_diakses_sesudah' => $granted,
        ]);

        return redirect()->route('admin.access.roles.edit', $role)
            ->with('success', 'Hak akses role ' . self::roleLabel($role) . " disimpan ({$granted} modul dapat diakses). Berlaku langsung.");
    }

    public function resetRole(string $role): RedirectResponse
    {
        $this->assertManageable($role);
        DefaultModules::resetRole($role);
        AdminAccess::flush();
        Log::info('Hak akses role dikembalikan ke bawaan', ['by_user_id' => auth('admin')->id(), 'role' => $role]);

        return redirect()->route('admin.access.roles.edit', $role)
            ->with('success', 'Hak akses role ' . self::roleLabel($role) . ' dikembalikan ke pengaturan bawaan.');
    }

    private function assertManageable(string $role): void
    {
        abort_unless(in_array($role, self::manageableRoles(), true), 404);
    }
}
