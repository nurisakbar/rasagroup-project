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
            $sales = User::where('role', User::ROLE_SALES)->select('id', 'sales_code', 'name', 'email', 'monthly_target', 'is_active', 'created_at');

            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('action', function ($sale) {
                    $editUrl = route('admin.sales.edit', $sale);
                    $deleteUrl = route('admin.sales.destroy', $sale);
                    $viewOrdersUrl = route('admin.sales.orders', $sale);
                    
                    return '
                        <a href="' . $viewOrdersUrl . '" class="btn btn-info btn-xs" title="Lihat Order">
                            <i class="fa fa-eye"></i> Order
                        </a>
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
                ->addColumn('status', function ($sale) {
                    return $sale->is_active 
                        ? '<span class="label label-success">Aktif</span>' 
                        : '<span class="label label-danger">Tidak Aktif</span>';
                })
                ->editColumn('monthly_target', function ($sale) {
                    return $sale->monthly_target ? 'Rp ' . number_format($sale->monthly_target, 0, ',', '.') : '-';
                })
                ->editColumn('created_at', function ($sale) {
                    return $sale->created_at->format('d M Y');
                })
                ->rawColumns(['action', 'status'])
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
            'is_active' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'sales_code' => $request->sales_code,
            'monthly_target' => $request->monthly_target,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_SALES,
            'is_active' => $request->has('is_active') ? 1 : 0,
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
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'sales_code' => $request->sales_code,
            'monthly_target' => $request->monthly_target,
            'is_active' => $request->has('is_active') ? 1 : 0,
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

    public function orders(Request $request, User $sale)
    {
        if ($sale->role !== User::ROLE_SALES) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = \App\Models\Order::with(['user', 'expedition'])
                ->where('sales_code', $sale->sales_code);

            if ($request->filled('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $query->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($order) {
                    $html = '<strong>' . $order->order_number . '</strong>';
                    $html .= '<br><small class="text-muted">' . $order->created_at->format('d M Y H:i') . '</small>';
                    return $html;
                })
                ->addColumn('buyer_info', function ($order) {
                    return $order->user ? ($order->user->name . '<br><small class="text-muted">' . $order->user->email . '</small>') : '-';
                })
                ->addColumn('expedition_info', function ($order) {
                    if ($order->expedition) {
                        return $order->expedition->name . ' - ' . $order->expedition_service;
                    }
                    return '-';
                })
                ->editColumn('total_amount', function ($order) {
                    return 'Rp ' . number_format($order->total_amount, 0, ',', '.');
                })
                ->addColumn('status_badge', function ($order) {
                    return '<small class="text-muted">Order:</small> <span class="label label-' . $order->status_color . '">' . ucfirst($order->order_status) . '</span><br>' .
                           '<small class="text-muted mt-5" style="display:inline-block">Pembayaran:</small> <span class="label label-' . $order->payment_status_color . ' mt-5" style="display:inline-block">' . ucfirst($order->payment_status) . '</span>';
                })
                ->addColumn('action', function ($order) {
                    return '<a href="' . route('admin.orders.show', $order) . '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Detail</a>';
                })
                ->rawColumns(['order_info', 'buyer_info', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.sales.orders', compact('sale'));
    }
}
