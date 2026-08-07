<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sales = User::where('role', User::ROLE_SALES)->select('id', 'name', 'sales_code', 'email', 'monthly_target', 'created_at');

            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('action', function ($sale) {
                    $editUrl = route('admin.sales.edit', $sale);
                    $deleteUrl = route('admin.sales.destroy', $sale);
                    
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
                ->editColumn('monthly_target', function ($sale) {
                    return $sale->monthly_target ? 'Rp ' . number_format($sale->monthly_target, 0, ',', '.') : '-';
                })
                ->editColumn('created_at', function ($sale) {
                    return $sale->created_at->format('d M Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.sales.index');
    }

    public function create()
    {
        return view('admin.sales.create');
    }

    public function store(Request $request)
    {
        // Strip dots from monthly_target
        if ($request->has('monthly_target')) {
            $request->merge([
                'monthly_target' => str_replace('.', '', $request->monthly_target)
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'sales_code' => 'nullable|string|max:255',
            'monthly_target' => 'nullable|numeric',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'sales_code' => $request->sales_code,
            'monthly_target' => $request->monthly_target,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_SALES,
        ]);

        return redirect()->route('admin.sales.index')->with('success', 'Data Sales berhasil ditambahkan.');
    }

    public function edit(User $sale)
    {
        if ($sale->role !== User::ROLE_SALES) {
            return redirect()->route('admin.sales.index')->with('error', 'Hanya dapat mengubah data Sales.');
        }

        return view('admin.sales.edit', compact('sale'));
    }

    public function update(Request $request, User $sale)
    {
        if ($sale->role !== User::ROLE_SALES) {
            abort(403);
        }

        // Strip dots from monthly_target
        if ($request->has('monthly_target')) {
            $request->merge([
                'monthly_target' => str_replace('.', '', $request->monthly_target)
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($sale->id),
            ],
            'sales_code' => 'nullable|string|max:255',
            'monthly_target' => 'nullable|numeric',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'sales_code' => $request->sales_code,
            'monthly_target' => $request->monthly_target,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $sale->update($data);

        return redirect()->route('admin.sales.index')->with('success', 'Data Sales berhasil diperbarui.');
    }

    public function destroy(User $sale)
    {
        if ($sale->role !== User::ROLE_SALES) {
            abort(403);
        }

        $sale->delete();

        return redirect()->route('admin.sales.index')->with('success', 'Data Sales berhasil dihapus.');
    }
}
