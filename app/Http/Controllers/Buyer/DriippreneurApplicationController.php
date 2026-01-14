<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriippreneurApplicationController extends Controller
{
    /**
     * Show the driippreneur application form.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user already applied or is already a driippreneur
        if ($user->role !== 'buyer') {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda tidak dapat mendaftar sebagai DRiiPPreneur.');
        }

        return view('buyer.driippreneur.apply', compact('user'));
    }

    /**
     * Store the driippreneur application.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user can apply
        if (!$user->canApplyAsDriippreneur()) {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda tidak dapat mendaftar sebagai DRiiPPreneur saat ini.');
        }

        $validated = $request->validate([
            'no_ktp' => ['required', 'string', 'size:16', 'regex:/^[0-9]+$/'],
            'no_npwp' => ['required', 'string', 'min:15', 'max:20'],
        ], [
            'no_ktp.required' => 'Nomor KTP wajib diisi.',
            'no_ktp.size' => 'Nomor KTP harus 16 digit.',
            'no_ktp.regex' => 'Nomor KTP hanya boleh berisi angka.',
            'no_npwp.required' => 'Nomor NPWP wajib diisi.',
            'no_npwp.min' => 'Nomor NPWP minimal 15 karakter.',
        ]);

        $user->update([
            'no_ktp' => $validated['no_ktp'],
            'no_npwp' => $validated['no_npwp'],
            'driippreneur_status' => 'pending',
            'driippreneur_applied_at' => now(),
        ]);

        return redirect()->route('buyer.driippreneur.status')
            ->with('success', 'Pengajuan DRiiPPreneur berhasil dikirim. Mohon tunggu proses verifikasi oleh admin.');
    }

    /**
     * Show the application status.
     */
    public function status()
    {
        $user = Auth::user();
        return view('buyer.driippreneur.status', compact('user'));
    }
}
