<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevelChange;
use App\Models\Warehouse;
use App\Support\DataTables\ColumnControlFilter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /** Tab halaman: outlet (daftar dengan kode sales), reseller (outlet/user biasa bertanda reseller), user biasa. */
    public const TABS = ['outlet', 'reseller', 'buyer'];

    /** Label tab (huruf kapital). Tab "buyer" = user biasa / pembeli ecommerce. */
    public const TAB_LABELS = ['outlet' => 'OUTLET', 'reseller' => 'RESELLER', 'buyer' => 'ECOM'];

    /** Prefix nama route & layout panel; dipakai ulang oleh panel Admin Hub. */
    protected string $routePrefix = 'admin.outlets';

    protected string $layout = 'layouts.admin';

    protected bool $canDelete = true;

    /** Tab yang boleh diakses di panel ini. */
    protected function tabs(): array
    {
        return self::TABS;
    }

    /** User yang melakukan perubahan (untuk riwayat level). */
    protected function actorId(): ?string
    {
        return auth('admin')->id();
    }

    /** Data tampilan panel untuk semua view outlet. */
    protected function panel(): array
    {
        return [
            'routePrefix' => $this->routePrefix,
            'layout' => $this->layout,
            'canDelete' => $this->canDelete,
            'canChooseHub' => $this->canChooseHub(),
            'hubOptions' => $this->canChooseHub() ? Warehouse::outletHubs()->orderBy('name')->get(['id', 'name']) : collect(),
            'tabLabels' => array_intersect_key(self::TAB_LABELS, array_flip($this->tabs())),
        ];
    }

    /** Batasi data sesuai panel (admin pusat: semua data). */
    protected function scopeToPanel($query)
    {
        return $query;
    }

    /** Admin pusat boleh memilih hub outlet; Admin Hub tidak. */
    protected function canChooseHub(): bool
    {
        return true;
    }

    /** Pilihan sales di form. */
    protected function salesOptions(?User $user = null): \Illuminate\Support\Collection
    {
        return User::salesCodeOptions();
    }

    /** Aturan validasi kode sales. */
    protected function salesCodeRules(?User $user = null): array
    {
        return ['nullable', 'string', $this->knownSalesCodeRule()];
    }

    /**
     * Hub outlet yang disimpan: pilihan admin bila diisi, selain itu hub milik sales,
     * dan bila sales belum punya hub tetap memakai hub yang sudah tercatat.
     */
    protected function resolveHubId(Request $request, ?User $user, ?string $salesCode): ?string
    {
        if ($request->filled('hub_id')) {
            return (string) $request->hub_id;
        }

        return User::hubIdForSalesCode($salesCode) ?? $user?->hub_id;
    }

    private function hubRules(?User $user = null): array
    {
        if (! $this->canChooseHub()) {
            return ['prohibited'];
        }
        $allowed = Warehouse::outletHubs()->pluck('id')->map(fn ($id) => (string) $id)->all();
        if ($user?->hub_id) {
            $allowed[] = (string) $user->hub_id;
        }

        return ['nullable', Rule::in($allowed)];
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), $this->tabs(), true) ? $request->query('tab') : 'outlet';

        if ($request->ajax()) {
            $query = $this->tabQuery($tab)
                ->with(['salesPerson', 'referringDistributor', 'hub:id,name'])
                ->select('id', 'name', 'email', 'phone', 'role', 'created_at', 'sales_code', 'is_reseller', 'reseller_marked_at', 'hub_id');

            // Pencarian per kolom dari DataTables ColumnControl.
            ColumnControlFilter::apply($query, $request, [
                'name' => 'name',
                'email' => 'email',
                'phone' => 'phone',
                'hub' => ['hub' => 'name'],
                'sales_name' => ['salesPerson' => 'name', 'referringDistributor' => 'name'],
            ]);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('sales_name', function ($user) {
                    if ($user->salesPerson) {
                        return e($user->salesPerson->name);
                    }

                    if ($user->referringDistributor) {
                        return e($user->referringDistributor->name) . ' <span class="label label-info">Distributor</span>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('action', function ($user) {
                    $editUrl = route($this->routePrefix . '.edit', $user);
                    $editButton = '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>';
                    if (! $this->canDelete) {
                        return $editButton;
                    }
                    $deleteUrl = route($this->routePrefix . '.destroy', $user);

                    return $editButton . '
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </form>
                    ';
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('d-m-Y');
                })
                ->addColumn('hub', function ($user) {
                    return $user->hub?->name ?? '-';
                })
                ->filterColumn('hub', function ($query, $keyword) {
                    $query->whereHas('hub', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
                })
                ->addColumn('reseller_marked', function ($user) {
                    return $user->reseller_marked_at ? $user->reseller_marked_at->timezone(\App\Support\Wib::TZ)->format('d-m-Y') : '-';
                })
                ->filterColumn('sales_name', function($query, $keyword) {
                    $query->where(function ($inner) use ($keyword) {
                        $inner->whereHas('salesPerson', fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('referringDistributor', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
                    });
                })
                ->rawColumns(['sales_name', 'action'])
                ->make(true);
        }

        $counts = collect($this->tabs())->mapWithKeys(fn ($key) => [$key => $this->tabQuery($key)->count()]);

        return view('admin.outlets.index', compact('tab', 'counts') + $this->panel());
    }

    /**
     * Query tiap tab:
     * - outlet: user yang mendaftar dengan kode sales (role outlet);
     * - reseller: outlet atau user biasa yang ditandai reseller;
     * - buyer: user biasa yang belum ditandai reseller.
     */
    private function tabQuery(string $tab)
    {
        return $this->scopeToPanel(match ($tab) {
            'reseller' => User::whereIn('role', [User::ROLE_OUTLET, User::ROLE_BUYER])->where('is_reseller', true),
            'buyer' => User::where('role', User::ROLE_BUYER)->where('is_reseller', false),
            default => User::where('role', User::ROLE_OUTLET)->where('is_reseller', false),
        });
    }

    /**
     * Tab tempat user berada setelah disimpan.
     */
    private function tabFor(User $user): string
    {
        return $user->customerLevel();
    }

    protected function isManageable(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OUTLET, User::ROLE_BUYER], true)
            && in_array($user->customerLevel(), $this->tabs(), true)
            && $this->scopeToPanel(User::whereKey($user->getKey()))->exists();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salesList = $this->salesOptions();
        return view('admin.outlets.create', compact('salesList') + $this->panel());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'sales_code' => $this->salesCodeRules(),
            'hub_id' => $this->hubRules(),
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_OUTLET,
            'sales_code' => $request->sales_code,
            'hub_id' => $this->resolveHubId($request, null, $request->sales_code),
        ]);

        return redirect()->route($this->routePrefix . '.index')->with('success', 'Outlet berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $outlet)
    {
        if (! $this->isManageable($outlet)) {
            return redirect()->route($this->routePrefix . '.index')->with('error', 'User ini tidak dapat diubah di sini.');
        }

        $user = $outlet; // to match view variable

        return view('admin.outlets.edit', compact('user') + $this->panel());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $outlet)
    {
        if (! $this->isManageable($outlet)) {
            abort(403);
        }

        // Form edit hanya mengubah level reseller; data lain (nama, email, telepon, sales, hub, kode QAD) read-only.
        $request->validate([
            'is_reseller' => ['nullable', 'boolean'],
        ]);

        $data = [];

        // Level reseller bisa diberikan ke user biasa maupun outlet.
        $fromLevel = $outlet->customerLevel();
        $markReseller = $request->boolean('is_reseller');
        if ($markReseller !== $outlet->isReseller()) {
            $data['is_reseller'] = $markReseller;
            $data['reseller_marked_at'] = $markReseller ? now() : null;
        }

        // Kode sales perekrut & hub tetap tersimpan saat level berubah (kembali berlaku bila dikembalikan ke outlet).

        DB::transaction(function () use ($outlet, $data, $fromLevel) {
            if ($data !== []) {
                $outlet->update($data);
            }

            $toLevel = $outlet->customerLevel();
            if ($toLevel !== $fromLevel) {
                UserLevelChange::create([
                    'user_id' => $outlet->id,
                    'from_level' => $fromLevel,
                    'to_level' => $toLevel,
                    'changed_by' => $this->actorId(),
                ]);
            }
        });

        $tab = $this->tabFor($outlet->fresh());
        $message = 'Data ' . $outlet->name . ' berhasil diperbarui (tab ' . self::TAB_LABELS[$tab] . ').';
        if (! in_array($tab, $this->tabs(), true)) {
            // Mis. Admin Hub mencabut reseller dari user ecom: user pindah ke tab yang tidak tampil di panel ini.
            return redirect()->route($this->routePrefix . '.index', ['tab' => 'reseller'])->with('success', $message);
        }

        return redirect()->route($this->routePrefix . '.index', ['tab' => $tab])->with('success', $message);
    }

    private function knownSalesCodeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! User::isKnownSalesCode($value)) {
                $fail('Kode sales tidak ditemukan.');
            }
        };
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $outlet)
    {
        if (! $this->canDelete || ! $this->isManageable($outlet)) {
            abort(403);
        }

        $tab = $this->tabFor($outlet);
        $outlet->delete();

        return redirect()->route($this->routePrefix . '.index', ['tab' => $tab])->with('success', 'User berhasil dihapus.');
    }
}
