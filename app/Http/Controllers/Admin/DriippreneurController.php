<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class DriippreneurController extends Controller
{
    /**
     * Display a listing of DRiiPPreneurs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::whereNotNull('driippreneur_status');

            // Status filter
            if ($request->filled('status') && $request->status != '') {
                $query->where('driippreneur_status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('phone', function ($query, $keyword) {
                    $query->whereRaw("COALESCE(phone, '') like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_ktp', function ($query, $keyword) {
                    $query->whereRaw("COALESCE(no_ktp, '') like ?", ["%{$keyword}%"]);
                })
                ->editColumn('phone', function ($user) {
                    return $user->phone ?? '-';
                })
                ->editColumn('no_ktp', function ($user) {
                    return $user->no_ktp ?? '-';
                })
                ->addColumn('status_badge', function ($user) {
                    if ($user->driippreneur_status === 'pending') {
                        return '<span class="label label-warning">Pending</span>';
                    } elseif ($user->driippreneur_status === 'approved') {
                        return '<span class="label label-success">Approved</span>';
                    } elseif ($user->driippreneur_status === 'rejected') {
                        return '<span class="label label-danger">Rejected</span>';
                    }
                    return '<span class="label label-default">' . $user->driippreneur_status . '</span>';
                })
                ->addColumn('applied_at_formatted', function ($user) {
                    return $user->driippreneur_applied_at ? $user->driippreneur_applied_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function ($user) {
                    $html = '<a href="' . route('admin.driippreneurs.show', $user) . '" class="btn btn-info btn-xs" title="Detail">
                        <i class="fa fa-eye"></i> Detail
                    </a> ';
                    
                    if ($user->driippreneur_status === 'pending') {
                        $html .= '<form action="' . route('admin.driippreneurs.approve', $user) . '" method="POST" style="display: inline-block;" class="approve-form">
                            ' . csrf_field() . '
                            ' . method_field('PUT') . '
                            <button type="submit" class="btn btn-success btn-xs" title="Setujui">
                                <i class="fa fa-check"></i> Setujui
                            </button>
                        </form> ';
                        
                        $html .= '<button type="button" class="btn btn-danger btn-xs reject-btn" data-user-id="' . $user->id . '" data-user-name="' . e($user->name) . '" title="Tolak">
                            <i class="fa fa-times"></i> Tolak
                        </button>';
                    }
                    
                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->orderColumn('driippreneur_applied_at', function ($query, $order) {
                    $query->orderBy('driippreneur_applied_at', $order);
                })
                ->make(true);
        }

        // Get statistics
        $totalPending = User::whereNotNull('driippreneur_status')->where('driippreneur_status', 'pending')->count();
        $totalApproved = User::whereNotNull('driippreneur_status')->where('driippreneur_status', 'approved')->count();
        $totalRejected = User::whereNotNull('driippreneur_status')->where('driippreneur_status', 'rejected')->count();
        $totalAll = User::whereNotNull('driippreneur_status')->count();

        return view('admin.driippreneurs.index', compact('totalPending', 'totalApproved', 'totalRejected', 'totalAll'));
    }

    /**
     * Show DRiiPPreneur details.
     */
    public function show(User $driippreneur)
    {
        if (!$driippreneur->driippreneur_status) {
            abort(404);
        }

        return view('admin.driippreneurs.show', compact('driippreneur'));
    }

    /**
     * Approve DRiiPPreneur application.
     */
    public function approve(User $driippreneur)
    {
        if (!$driippreneur->driippreneur_status || $driippreneur->driippreneur_status !== 'pending') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Aplikasi tidak dapat disetujui.'], 400);
            }
            return back()->with('error', 'Aplikasi tidak dapat disetujui.');
        }

        $driippreneur->update([
            'driippreneur_status' => 'approved',
            'driippreneur_approved_at' => now(),
            'driippreneur_rejected_at' => null,
            'driippreneur_rejection_reason' => null,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => 'Aplikasi DRiiPPreneur berhasil disetujui.']);
        }

        return back()->with('success', 'Aplikasi DRiiPPreneur berhasil disetujui.');
    }

    /**
     * Reject DRiiPPreneur application.
     */
    public function reject(Request $request, User $driippreneur)
    {
        if (!$driippreneur->driippreneur_status || $driippreneur->driippreneur_status !== 'pending') {
            return back()->with('error', 'Aplikasi tidak dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $driippreneur->update([
            'driippreneur_status' => 'rejected',
            'driippreneur_rejected_at' => now(),
            'driippreneur_rejection_reason' => $request->input('rejection_reason'),
        ]);

        return back()->with('success', 'Aplikasi DRiiPPreneur ditolak.');
    }
}

