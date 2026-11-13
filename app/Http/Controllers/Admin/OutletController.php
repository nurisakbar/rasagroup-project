<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /** Tab halaman: outlet (daftar dengan kode sales), reseller (user biasa bertanda reseller), user biasa. */
    public const TABS = ['outlet', 'reseller', 'buyer'];

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'outlet';

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
                    $editUrl = route('admin.outlets.edit', $user);
                    $deleteUrl = route('admin.outlets.destroy', $user);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
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
        $counts = collect(self::TABS)->mapWithKeys(fn ($key) => [$key => $this->tabQuery($key)->count()]);

        return view('admin.outlets.index', compact('salesList', 'tab', 'counts'));
    }

    /**
     * Query tiap tab:
     * - outlet: user yang mendaftar dengan kode sales (role outlet);
     * - reseller: user biasa yang ditandai reseller;
     * - buyer: user biasa yang belum ditandai reseller.
     */
    private function tabQuery(string $tab)
    {
        return match ($tab) {
            'reseller' => User::where('role', User::ROLE_BUYER)->where('is_reseller', true),
            'buyer' => User::where('role', User::ROLE_BUYER)->where('is_reseller', false),
            default => User::where('role', User::ROLE_OUTLET),
        };
    }

    /**
     * Tab tempat user berada setelah disimpan.
     */
    private function tabFor(User $user): string
    {
        if ($user->role === User::ROLE_BUYER) {
            return $user->isReseller() ? 'reseller' : 'buyer';
        }

        return 'outlet';
    }

    private function isManageable(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OUTLET, User::ROLE_BUYER], true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salesList = User::salesCodeOptions();
        return view('admin.outlets.create', compact('salesList'));
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

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $outlet)
    {
        if (! $this->isManageable($outlet)) {
            return redirect()->route('admin.outlets.index')->with('error', 'Role user ini tidak dapat diubah di sini.');
        }

        $user = $outlet; // to match view variable
        $salesList = User::salesCodeOptions();
        return view('admin.outlets.edit', compact('user', 'salesList'));
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

        // Penanda reseller hanya untuk user biasa (buyer).
        if ($outlet->role === User::ROLE_BUYER) {
            $markReseller = $request->boolean('is_reseller');
            if ($markReseller !== $outlet->isReseller()) {
                $data['is_reseller'] = $markReseller;
                $data['reseller_marked_at'] = $markReseller ? now() : null;
            }
        }

        $outlet->update($data);

        $tab = $this->tabFor($outlet->fresh());
        $label = ['outlet' => 'Outlet', 'reseller' => 'Reseller', 'buyer' => 'User Biasa'][$tab];

        return redirect()->route('admin.outlets.index', ['tab' => $tab])
            ->with('success', 'Data ' . $outlet->name . ' berhasil diperbarui (tab ' . $label . ').');
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
        if (! $this->isManageable($outlet)) {
            abort(403);
        }

        $tab = $this->tabFor($outlet);
        $outlet->delete();

        return redirect()->route('admin.outlets.index', ['tab' => $tab])->with('success', 'User berhasil dihapus.');
    }
}
