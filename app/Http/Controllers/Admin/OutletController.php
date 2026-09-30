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
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::where('role', User::ROLE_OUTLET)
                ->with('salesPerson')
                ->select('id', 'name', 'email', 'phone', 'role', 'created_at', 'sales_code', 'qad_customer_code');

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
                    return $user->salesPerson ? $user->salesPerson->name : '<span class="text-muted">-</span>';
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
                ->filterColumn('sales_name', function($query, $keyword) {
                    $query->whereHas('salesPerson', function($q) use($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['sales_name', 'action'])
                ->make(true);
        }

        $salesList = User::where('role', User::ROLE_SALES)->get();
        return view('admin.outlets.index', compact('salesList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salesList = User::where('role', User::ROLE_SALES)->get();
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
            'sales_code' => 'nullable|string|exists:users,sales_code',
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
        if ($outlet->role !== User::ROLE_OUTLET) {
            return redirect()->route('admin.outlets.index')->with('error', 'Role user ini tidak dapat diubah di sini.');
        }

        $user = $outlet; // to match view variable
        $salesList = User::where('role', User::ROLE_SALES)->get();
        return view('admin.outlets.edit', compact('user', 'salesList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $outlet)
    {
        if ($outlet->role !== User::ROLE_OUTLET) {
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
            'sales_code' => 'nullable|string|exists:users,sales_code',
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

        $outlet->update($data);

        return redirect()->route('admin.outlets.index')->with('success', 'Data Outlet berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $outlet)
    {
        if ($outlet->role !== User::ROLE_OUTLET) {
            abort(403);
        }

        $outlet->delete();

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet berhasil dihapus.');
    }
}
