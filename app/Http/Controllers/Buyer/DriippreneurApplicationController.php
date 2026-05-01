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

        // Bank info is managed in profile, not in this form
        if (empty($user->bank_name) || empty($user->bank_account_number) || empty($user->bank_account_name)) {
            return redirect()->route('buyer.profile.edit')
                ->with('warning', 'Lengkapi informasi rekening bank di Profil terlebih dahulu sebelum mendaftar sebagai Affiliator.');
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
            'ktp_file' => [$request->filled('ktp_base64') ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'npwp_file' => [$request->filled('npwp_base64') ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'selfie_file' => [$request->filled('selfie_base64') ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'no_ktp.required' => 'Nomor KTP wajib diisi.',
            'no_ktp.size' => 'Nomor KTP harus 16 digit.',
            'no_ktp.regex' => 'Nomor KTP hanya boleh berisi angka.',
            'no_npwp.required' => 'Nomor NPWP wajib diisi.',
            'no_npwp.min' => 'Nomor NPWP minimal 15 karakter.',
            'ktp_file.required' => 'Foto KTP wajib diambil menggunakan kamera.',
            'ktp_file.image' => 'File KTP harus berupa gambar.',
            'ktp_file.max' => 'Ukuran file KTP maksimal 2MB.',
            'npwp_file.required' => 'Foto NPWP wajib diambil menggunakan kamera.',
            'npwp_file.image' => 'File NPWP harus berupa gambar.',
            'npwp_file.max' => 'Ukuran file NPWP maksimal 2MB.',
            'selfie_file.required' => 'Foto Selfie wajib diambil menggunakan kamera.',
            'selfie_file.image' => 'File Selfie harus berupa gambar.',
            'selfie_file.max' => 'Ukuran file Selfie maksimal 2MB.',
        ]);

        // Ensure bank info is present on profile (not submitted from form)
        if (empty($user->bank_name) || empty($user->bank_account_number) || empty($user->bank_account_name)) {
            return back()->with('error', 'Informasi rekening bank belum lengkap. Silakan lengkapi di Profil terlebih dahulu.');
        }

        $ktpPath = null;
        if ($request->filled('ktp_base64')) {
            $imageData = $request->input('ktp_base64');
            $imageData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', ' '], ['', '', '+'], $imageData);
            $imageName = 'ktp_' . auth()->id() . '_' . time() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put('verification/' . $imageName, base64_decode($imageData));
            $ktpPath = 'verification/' . $imageName;
        } elseif ($request->hasFile('ktp_file')) {
            $ktpPath = $request->file('ktp_file')->store('verification', 'public');
        }

        $npwpPath = null;
        if ($request->filled('npwp_base64')) {
            $imageData = $request->input('npwp_base64');
            $imageData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', ' '], ['', '', '+'], $imageData);
            $imageName = 'npwp_' . auth()->id() . '_' . time() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put('verification/' . $imageName, base64_decode($imageData));
            $npwpPath = 'verification/' . $imageName;
        } elseif ($request->hasFile('npwp_file')) {
            $npwpPath = $request->file('npwp_file')->store('verification', 'public');
        }

        $selfiePath = null;
        if ($request->filled('selfie_base64')) {
            $imageData = $request->input('selfie_base64');
            $imageData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', ' '], ['', '', '+'], $imageData);
            $imageName = 'selfie_' . auth()->id() . '_' . time() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put('verification/' . $imageName, base64_decode($imageData));
            $selfiePath = 'verification/' . $imageName;
        } elseif ($request->hasFile('selfie_file')) {
            $selfiePath = $request->file('selfie_file')->store('verification', 'public');
        }

        $user->update([
            'no_ktp' => $validated['no_ktp'],
            'no_npwp' => $validated['no_npwp'],
            'ktp_file' => $ktpPath,
            'npwp_file' => $npwpPath,
            'selfie_file' => $selfiePath,
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
