<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevelChange;
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
            'tabLabels' => array_intersect_key(self::TAB_LABELS, array_flip($this->tabs())),
        ];
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), $this->tabs(), true) ? $request->query('tab') : 'outlet';

        if ($request->ajax()) {
            $query = $this->tabQuery($tab)
                ->with(['salesPerson', 'referringDistributor'])
                ->select('id', 'name', 'email', 'phone', 'role', 'created_at', 'sales_code', 'qad_customer_code', 'is_reseller', 'reseller_marked_at');

            if ($request->filled('sales_code')) {
                if ($request->sales_code === 'none') {
                    $query->whereNull('sales_code');
                } else {
                    $query->where('sales_code', $request->sales_code);
                }
            }

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
                ->addColumn('origin', function ($user) {
                    return $user->role === User::ROLE_OUTLET ? 'Outlet' : 'Ecom';
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

        $salesList = User::salesCodeOptions();
        $counts = collect($this->tabs())->mapWithKeys(fn ($key) => [$key => $this->tabQuery($key)->count()]);

        return view('admin.outlets.index', compact('salesList', 'tab', 'counts') + $this->panel());
    }

    /**
     * Query tiap tab:
     * - outlet: user yang mendaftar dengan kode sales (role outlet);
     * - reseller: outlet atau user biasa yang ditandai reseller;
     * - buyer: user biasa yang belum ditandai reseller.
     */
    private function tabQuery(string $tab)
    {
        return match ($tab) {
            'reseller' => User::whereIn('role', [User::ROLE_OUTLET, User::ROLE_BUYER])->where('is_reseller', true),
            'buyer' => User::where('role', User::ROLE_BUYER)->where('is_reseller', false),
            default => User::where('role', User::ROLE_OUTLET)->where('is_reseller', false),
        };
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
            && in_array($user->customerLevel(), $this->tabs(), true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salesList = User::salesCodeOptions();
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
            'sales_code' => ['nullable', 'string', $this->knownSalesCodeRule()],
            'qad_customer_code' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_OUTLET,
            'sales_code' => $request->sales_code,
            'qad_customer_code' => $request->qad_customer_code,
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
        $salesList = User::salesCodeOptions();
        $levelChanges = $user->levelChanges()->with('changer:id,name')->limit(20)->get();

        return view('admin.outlets.edit', compact('user', 'salesList', 'levelChanges') + $this->panel());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $outlet)
    {
        if (! $this->isManageable($outlet)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($outlet->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'sales_code' => ['nullable', 'string', $this->knownSalesCodeRule()],
            'qad_customer_code' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'sales_code' => $request->sales_code,
            'qad_customer_code' => $request->qad_customer_code,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Level reseller bisa diberikan ke user biasa maupun outlet.
        $fromLevel = $outlet->customerLevel();
        $markReseller = $request->boolean('is_reseller');
        if ($markReseller !== $outlet->isReseller()) {
            $data['is_reseller'] = $markReseller;
            $data['reseller_marked_at'] = $markReseller ? now() : null;
        }

        // Reseller tidak memakai kode sales untuk diskon: input sales tidak ditampilkan, kode sales lama
        // (sales/distributor perekrut outlet) tetap disimpan agar kembali berlaku bila level dikembalikan.
        if ($markReseller) {
            unset($data['sales_code']);
        }

        DB::transaction(function () use ($outlet, $data, $fromLevel) {
            $outlet->update($data);

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
