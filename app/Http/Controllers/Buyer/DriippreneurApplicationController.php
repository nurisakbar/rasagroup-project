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
            'ktp_file' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'npwp_file' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:100'],
        ], [
            'no_ktp.required' => 'Nomor KTP wajib diisi.',
            'no_ktp.size' => 'Nomor KTP harus 16 digit.',
            'no_ktp.regex' => 'Nomor KTP hanya boleh berisi angka.',
            'no_npwp.required' => 'Nomor NPWP wajib diisi.',
            'no_npwp.min' => 'Nomor NPWP minimal 15 karakter.',
            'ktp_file.required' => 'Foto KTP wajib diupload.',
            'ktp_file.image' => 'File KTP harus berupa gambar.',
            'ktp_file.max' => 'Ukuran file KTP maksimal 2MB.',
            'npwp_file.required' => 'Foto NPWP wajib diupload.',
            'npwp_file.image' => 'File NPWP harus berupa gambar.',
            'npwp_file.max' => 'Ukuran file NPWP maksimal 2MB.',
            'bank_name.required' => 'Nama Bank wajib diisi.',
            'bank_account_number.required' => 'Nomor Rekening wajib diisi.',
            'bank_account_name.required' => 'Nama Pemilik Rekening wajib diisi.',
        ]);

        $ktpPath = null;
        if ($request->hasFile('ktp_file')) {
            $ktpPath = $request->file('ktp_file')->store('verification', 'public');
        }

        $npwpPath = null;
        if ($request->hasFile('npwp_file')) {
            $npwpPath = $request->file('npwp_file')->store('verification', 'public');
        }

        $user->update([
            'no_ktp' => $validated['no_ktp'],
            'no_npwp' => $validated['no_npwp'],
            'ktp_file' => $ktpPath,
            'npwp_file' => $npwpPath,
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_name' => $validated['bank_account_name'],
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
