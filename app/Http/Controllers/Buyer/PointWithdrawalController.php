<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\PointWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointWithdrawalController extends Controller
{
    /**
     * Display a listing of the withdrawal requests.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Only DRiiPPreneurs who are approved can withdraw points
        if (!$user->isDriippreneurApproved()) {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda belum terdaftar sebagai DRiiPPreneur atau aplikasi belum disetujui.');
        }

        $withdrawals = PointWithdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('buyer.point-withdrawals.index', compact('withdrawals', 'user'));
    }

    /**
     * Show the form for creating a new withdrawal request.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only DRiiPPreneurs who are approved can withdraw points
        if (!$user->isDriippreneurApproved()) {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda belum terdaftar sebagai DRiiPPreneur atau aplikasi belum disetujui.');
        }

        // Check if user has pending withdrawal
        $pendingWithdrawal = PointWithdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return redirect()->route('buyer.point-withdrawals.index')
                ->with('error', 'Anda masih memiliki request penarikan yang pending. Harap tunggu hingga request tersebut diproses.');
        }

        return view('buyer.point-withdrawals.create', compact('user'));
    }

    /**
     * Store a newly created withdrawal request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only DRiiPPreneurs who are approved can withdraw points
        if (!$user->isDriippreneurApproved()) {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda belum terdaftar sebagai DRiiPPreneur atau aplikasi belum disetujui.');
        }

        // Check if user has pending withdrawal
        $pendingWithdrawal = PointWithdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return redirect()->route('buyer.point-withdrawals.index')
                ->with('error', 'Anda masih memiliki request penarikan yang pending. Harap tunggu hingga request tersebut diproses.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:100'],
        ], [
            'amount.required' => 'Jumlah poin harus diisi.',
            'amount.integer' => 'Jumlah poin harus berupa angka.',
            'amount.min' => 'Jumlah poin minimal 1.',
            'bank_name.required' => 'Nama bank harus diisi.',
            'account_number.required' => 'Nomor rekening harus diisi.',
            'account_name.required' => 'Nama pemilik rekening harus diisi.',
        ]);

        // Check if user has enough points
        if ($user->points < $validated['amount']) {
            return back()->withInput()
                ->with('error', 'Poin Anda tidak mencukupi. Poin saat ini: ' . number_format($user->points, 0, ',', '.'));
        }

        try {
            DB::beginTransaction();

            // Create withdrawal request
            $withdrawal = PointWithdrawal::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('buyer.point-withdrawals.index')
                ->with('success', 'Request penarikan poin berhasil diajukan. Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat mengajukan request. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified withdrawal request.
     */
    public function show(PointWithdrawal $pointWithdrawal)
    {
        $user = Auth::user();
        
        // Check ownership
        if ($pointWithdrawal->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        return view('buyer.point-withdrawals.show', compact('pointWithdrawal'));
    }
}
