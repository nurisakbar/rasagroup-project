<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PointWithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawal requests.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PointWithdrawal::with('user');

            // Status filter
            if ($request->filled('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->filled('start_date')) {
                try {
                    $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
                    $query->whereDate('requested_at', '>=', $startDate);
                } catch (\Exception $e) {}
            }
            if ($request->filled('end_date')) {
                try {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
                    $query->whereDate('requested_at', '<=', $endDate);
                } catch (\Exception $e) {}
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_info', function ($withdrawal) {
                    return '<strong>' . $withdrawal->user->name . '</strong><br><small class="text-muted">' . $withdrawal->user->email . '</small>';
                })
                ->addColumn('amount_formatted', function ($withdrawal) {
                    return number_format($withdrawal->amount, 0, ',', '.') . ' poin';
                })
                ->addColumn('bank_info', function ($withdrawal) {
                    return '<strong>' . $withdrawal->bank_name . '</strong><br>' .
                           '<small>' . $withdrawal->account_number . '</small><br>' .
                           '<small>' . $withdrawal->account_name . '</small>';
                })
                ->addColumn('status_badge', function ($withdrawal) {
                    $badges = [
                        'pending' => '<span class="label label-warning">Baru Pengajuan</span>',
                        'approved' => '<span class="label label-info">Sedang Diproses</span>',
                        'rejected' => '<span class="label label-danger">Ditolak</span>',
                        'completed' => '<span class="label label-success">Sudah Diproses</span>',
                    ];
                    return $badges[$withdrawal->status] ?? '<span class="label label-default">' . $withdrawal->status . '</span>';
                })
                ->addColumn('requested_at_formatted', function ($withdrawal) {
                    return $withdrawal->requested_at ? $withdrawal->requested_at->format('d-m-Y H:i') : '-';
                })
                ->addColumn('action', function ($withdrawal) {
                    $html = '<a href="' . route('admin.point-withdrawals.show', $withdrawal) . '" class="btn btn-info btn-xs" title="Detail">
                        <i class="fa fa-eye"></i> Detail
                    </a> ';
                    
                    if ($withdrawal->status === 'pending') {
                        $html .= '<form action="' . route('admin.point-withdrawals.approve', $withdrawal) . '" method="POST" style="display: inline-block;" class="approve-form">
                            ' . csrf_field() . '
                            ' . method_field('PUT') . '
                            <button type="submit" class="btn btn-success btn-xs" title="Setujui">
                                <i class="fa fa-check"></i> Setujui
                            </button>
                        </form> ';
                        
                        $html .= '<button type="button" class="btn btn-danger btn-xs reject-btn" data-withdrawal-id="' . $withdrawal->id . '" data-user-name="' . e($withdrawal->user->name) . '" title="Tolak">
                            <i class="fa fa-times"></i> Tolak
                        </button>';
                    } elseif ($withdrawal->status === 'approved') {
                        $html .= '<form action="' . route('admin.point-withdrawals.complete', $withdrawal) . '" method="POST" style="display: inline-block;" class="complete-form">
                            ' . csrf_field() . '
                            ' . method_field('PUT') . '
                            <button type="submit" class="btn btn-primary btn-xs" title="Selesaikan (Kurangi poin)">
                                <i class="fa fa-check-circle"></i> Selesaikan
                            </button>
                        </form>';
                    }
                    
                    return $html;
                })
                ->rawColumns(['user_info', 'bank_info', 'status_badge', 'action'])
                ->orderColumn('requested_at', function ($query, $order) {
                    $query->orderBy('requested_at', $order);
                })
                ->make(true);
        }

        // Get statistics
        $totalPending = PointWithdrawal::where('status', 'pending')->count();
        $totalApproved = PointWithdrawal::where('status', 'approved')->count();
        $totalCompleted = PointWithdrawal::where('status', 'completed')->count();
        $totalRejected = PointWithdrawal::where('status', 'rejected')->count();

        return view('admin.point-withdrawals.index', compact('totalPending', 'totalApproved', 'totalCompleted', 'totalRejected'));
    }

    /**
     * Show withdrawal request details.
     */
    public function show(PointWithdrawal $pointWithdrawal)
    {
        $pointWithdrawal->load('user');
        return view('admin.point-withdrawals.show', compact('pointWithdrawal'));
    }

    /**
     * Approve withdrawal request.
     */
    public function approve(PointWithdrawal $pointWithdrawal)
    {
        if ($pointWithdrawal->status !== 'pending') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Request tidak dapat disetujui.'], 400);
            }
            return back()->with('error', 'Request tidak dapat disetujui.');
        }

        // Check if user still has enough points
        $user = $pointWithdrawal->user;
        if ($user->points < $pointWithdrawal->amount) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Poin user tidak mencukupi untuk penarikan ini.'], 400);
            }
            return back()->with('error', 'Poin user tidak mencukupi untuk penarikan ini.');
        }

        $pointWithdrawal->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => 'Request penarikan poin berhasil disetujui.']);
        }

        return back()->with('success', 'Request penarikan poin berhasil disetujui.');
    }

    /**
     * Reject withdrawal request.
     */
    public function reject(Request $request, PointWithdrawal $pointWithdrawal)
    {
        if ($pointWithdrawal->status !== 'pending') {
            return back()->with('error', 'Request tidak dapat ditolak.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $pointWithdrawal->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Request penarikan poin ditolak.');
    }

    /**
     * Update withdrawal status.
     */
    public function updateStatus(Request $request, PointWithdrawal $pointWithdrawal)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,completed'],
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $pointWithdrawal->status;

        // If status didn't change, do nothing
        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status tidak berubah.');
        }

        $user = $pointWithdrawal->user;

        try {
            DB::beginTransaction();

            // Handle point deduction/addition
            // If changing to completed, deduct points
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                // Check if user still has enough points
                if ($user->points < $pointWithdrawal->amount) {
                    DB::rollBack();
                    return back()->with('error', 'Poin user tidak mencukupi untuk penarikan ini. Poin user saat ini: ' . number_format($user->points, 0, ',', '.') . ' poin.');
                }
                // Deduct points from user
                $user->decrement('points', $pointWithdrawal->amount);
            }

            // If changing from completed to other status, add points back
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                $user->increment('points', $pointWithdrawal->amount);
            }

            // Update status and timestamps
            $updateData = ['status' => $newStatus];

            // Set timestamps based on new status
            if ($newStatus === 'approved') {
                $updateData['approved_at'] = now();
                // Clear rejected timestamp if exists
                if ($pointWithdrawal->rejected_at) {
                    $updateData['rejected_at'] = null;
                }
            } elseif ($newStatus === 'rejected') {
                $updateData['rejected_at'] = now();
                // Clear approved timestamp if exists
                if ($pointWithdrawal->approved_at) {
                    $updateData['approved_at'] = null;
                }
            } elseif ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'pending') {
                // Reset all timestamps if going back to pending
                $updateData['approved_at'] = null;
                $updateData['rejected_at'] = null;
                $updateData['completed_at'] = null;
            }

            $pointWithdrawal->update($updateData);

            DB::commit();

            return back()->with('success', 'Status penarikan poin berhasil diubah dari "' . $this->getStatusLabel($oldStatus) . '" menjadi "' . $this->getStatusLabel($newStatus) . '".');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating withdrawal status: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Get status label.
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Baru Pengajuan',
            'approved' => 'Sedang Diproses',
            'completed' => 'Sudah Diproses',
            'rejected' => 'Ditolak',
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Complete withdrawal (deduct points from user).
     */
    public function complete(PointWithdrawal $pointWithdrawal)
    {
        if ($pointWithdrawal->status !== 'approved') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Request tidak dapat diselesaikan.'], 400);
            }
            return back()->with('error', 'Request tidak dapat diselesaikan.');
        }

        try {
            DB::beginTransaction();

            $user = $pointWithdrawal->user;

            // Check if user still has enough points
            if ($user->points < $pointWithdrawal->amount) {
                DB::rollBack();
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['error' => 'Poin user tidak mencukupi untuk penarikan ini.'], 400);
                }
                return back()->with('error', 'Poin user tidak mencukupi untuk penarikan ini.');
            }

            // Deduct points from user
            $user->decrement('points', $pointWithdrawal->amount);

            // Update withdrawal status
            $pointWithdrawal->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => 'Penarikan poin berhasil diselesaikan.']);
            }

            return back()->with('success', 'Penarikan poin berhasil diselesaikan. Poin telah dikurangi dari akun user.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan saat menyelesaikan penarikan.'], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyelesaikan penarikan.');
        }
    }
}
