<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::whereIn('role', ['super_admin', 'ecommerce', 'brand_marketing', 'finance', 'sales_admin', 'customer_service', 'it_application', 'inventory_manager'])
                ->select('id', 'name', 'email', 'phone', 'role', 'created_at');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', function ($user) {
                    $labels = [
                        'super_admin' => '<span class="label label-danger">Super Admin</span>',
                        'ecommerce' => '<span class="label label-info">eCommerce</span>',
                        'brand_marketing' => '<span class="label label-primary">Brand Marketing</span>',
                        'finance' => '<span class="label label-warning">Finance</span>',
                        'sales_admin' => '<span class="label label-success">Sales Admin</span>',
                        'customer_service' => '<span class="label label-info">Customer Service</span>',
                        'it_application' => '<span class="label label-default">IT App</span>',
                        'inventory_manager' => '<span class="label label-warning">Inventory</span>',
                    ];
                    return $labels[$user->role] ?? '<span class="label label-default">' . ucfirst($user->role) . '</span>';
                })
                ->addColumn('action', function ($user) {
                    $editUrl = route('admin.users.edit', $user);
                    $deleteUrl = route('admin.users.destroy', $user);
                    
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
                    return $user->created_at->format('d M Y H:i');
                })
                ->rawColumns(['role_badge', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
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
            'role' => 'required|in:super_admin,ecommerce,brand_marketing,finance,sales_admin,customer_service,it_application,inventory_manager',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $allowedRoles = ['super_admin', 'ecommerce', 'brand_marketing', 'finance', 'sales_admin', 'customer_service', 'it_application', 'inventory_manager'];
        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->route('admin.users.index')->with('error', 'Role user ini tidak dapat diubah di sini.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $allowedRoles = ['super_admin', 'ecommerce', 'brand_marketing', 'finance', 'sales_admin', 'customer_service', 'it_application', 'inventory_manager'];
        if (!in_array($user->role, $allowedRoles)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,ecommerce,brand_marketing,finance,sales_admin,customer_service,it_application,inventory_manager',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $allowedRoles = ['super_admin', 'ecommerce', 'brand_marketing', 'finance', 'sales_admin', 'customer_service', 'it_application', 'inventory_manager'];
        if (!in_array($user->role, $allowedRoles)) {
            abort(403);
        }

        // Prevent self-deletion if needed, but for now just allow deleting other admins
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
