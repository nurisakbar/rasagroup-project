<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select('id', 'name', 'email', 'phone', 'role', 'created_at');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', function ($user) {
                    $badges = [
                        'super_admin' => '<span class="label label-danger">Super Admin</span>',
                        'agent' => '<span class="label label-primary">Agent</span>',
                        'reseller' => '<span class="label label-warning">Reseller</span>',
                        'buyer' => '<span class="label label-success">Pembeli</span>',
                    ];
                    return $badges[$user->role] ?? '<span class="label label-default">Unknown</span>';
                })
                ->addColumn('action', function ($user) {
                    $btn = '<a href="javascript:void(0)" class="btn btn-info btn-xs" title="Detail"><i class="fa fa-eye"></i></a> ';
                    $btn .= '<a href="javascript:void(0)" class="btn btn-warning btn-xs" title="Edit"><i class="fa fa-edit"></i></a> ';
                    return $btn;
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('d M Y H:i');
                })
                ->rawColumns(['role_badge', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }
}
