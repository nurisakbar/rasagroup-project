<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminModule;
use App\Support\AdminAccess\AdminAccess;
use App\Support\AdminAccess\DefaultModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/**
 * Kelola modul panel admin: tambah, lihat, ubah, hapus. Modul bawaan (is_system) tidak bisa dihapus
 * dan pola rutenya dikunci.
 */
class AdminModuleController extends Controller
{
    public function index()
    {
        $modules = AdminModule::withCount(['permissions as roles_count' => fn ($q) => $q->where('can_view', true)])
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.access.modules.index', compact('modules'));
    }

    public function create()
    {
        return view('admin.access.modules.form', ['module' => new AdminModule(['icon' => 'fa-circle-o', 'sort_order' => 500, 'show_in_menu' => true, 'is_active' => true]), 'groups' => $this->groups()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $module = AdminModule::create($data + ['is_system' => false]);
        AdminAccess::flush();
        Log::info('Modul admin dibuat', ['by_user_id' => auth('admin')->id(), 'key' => $module->key]);

        return redirect()->route('admin.access.modules.index')
            ->with('success', "Modul \"{$module->name}\" dibuat. Belum ada role yang bisa mengaksesnya: atur di menu Hak Akses.");
    }

    public function edit(AdminModule $module)
    {
        return view('admin.access.modules.form', ['module' => $module, 'groups' => $this->groups()]);
    }

    public function update(Request $request, AdminModule $module): RedirectResponse
    {
        $data = $this->validated($request, $module);
        $module->update($data);
        AdminAccess::flush();
        Log::info('Modul admin diubah', ['by_user_id' => auth('admin')->id(), 'key' => $module->key]);

        return redirect()->route('admin.access.modules.index')->with('success', "Modul \"{$module->name}\" diperbarui.");
    }

    public function destroy(AdminModule $module): RedirectResponse
    {
        if ($module->is_system) {
            return back()->with('error', 'Modul bawaan tidak bisa dihapus. Nonaktifkan saja bila tidak dipakai.');
        }
        $name = $module->name;
        $module->delete(); // izin role ikut terhapus (cascade)
        AdminAccess::flush();
        Log::info('Modul admin dihapus', ['by_user_id' => auth('admin')->id(), 'key' => $module->key]);

        return redirect()->route('admin.access.modules.index')->with('success', "Modul \"{$name}\" dihapus beserta izin rolenya.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?AdminModule $module): array
    {
        $isSystem = (bool) $module?->is_system;

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'group' => ['nullable', 'string', 'max:60'],
            'icon' => ['required', 'regex:/^fa-[a-z0-9-]+$/'],
            'menu_route' => ['nullable', 'string', 'max:120', function ($attr, $value, $fail) {
                if ($value && (! str_starts_with($value, 'admin.') || ! Route::has($value))) {
                    $fail('Nama rute menu tidak ditemukan. Contoh: admin.orders.index');
                }
            }],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
        if (! $module) {
            $rules['key'] = ['required', 'regex:/^[a-z0-9_]+$/', 'max:60', Rule::unique('admin_modules', 'key')];
        }
        if (! $isSystem) {
            $rules['route_patterns'] = ['required', 'string', function ($attr, $value, $fail) {
                $patterns = $this->splitPatterns($value);
                if (! $patterns) {
                    $fail('Isi minimal satu pola rute.');
                }
                foreach ($patterns as $p) {
                    if (! preg_match('/^admin\.[a-z0-9.\-*]+$/', $p)) {
                        $fail("Pola \"{$p}\" tidak valid. Gunakan nama rute diawali admin. (boleh akhiran .*), contoh: admin.orders.*");
                    }
                }
            }];
        }

        $validated = $request->validate($rules, [
            'icon.regex' => 'Ikon harus berformat Font Awesome, contoh: fa-cube.',
            'key.regex' => 'Kunci hanya huruf kecil, angka, dan garis bawah.',
            'key.unique' => 'Kunci modul sudah dipakai.',
        ]);

        $data = [
            'name' => $validated['name'],
            'group' => ($validated['group'] ?? null) ?: null,
            'icon' => $validated['icon'],
            'menu_route' => ($validated['menu_route'] ?? null) ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'show_in_menu' => $request->boolean('show_in_menu'),
            'is_active' => $request->boolean('is_active'),
        ];
        if (! $module) {
            $data['key'] = $validated['key'];
        }
        if (! $isSystem) {
            $data['route_patterns'] = $this->splitPatterns($validated['route_patterns']);
        }
        // Modul pengelola hak akses tidak boleh dinonaktifkan atau disembunyikan agar tidak mengunci diri.
        if ($module && in_array($module->key, DefaultModules::SUPER_ADMIN_ONLY, true)) {
            $data['is_active'] = true;
            $data['show_in_menu'] = true;
        }

        return $data;
    }

    /** @return list<string> */
    private function splitPatterns(string $value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value)))));
    }

    /** @return list<string> */
    private function groups(): array
    {
        return AdminModule::whereNotNull('group')->distinct()->orderBy('group')->pluck('group')->all();
    }
}
