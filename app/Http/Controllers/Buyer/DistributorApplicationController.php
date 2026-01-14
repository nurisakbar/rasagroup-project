<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributorApplicationController extends Controller
{
    /**
     * Show the distributor application form.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user already applied or is already a distributor
        if ($user->role !== 'buyer') {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda tidak dapat mendaftar sebagai distributor.');
        }

        $provinces = Province::orderBy('name')->get();

        return view('buyer.distributor.apply', compact('user', 'provinces'));
    }

    /**
     * Store the distributor application.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user can apply
        if (!$user->canApplyAsDistributor()) {
            return redirect()->route('buyer.dashboard')
                ->with('error', 'Anda tidak dapat mendaftar sebagai distributor saat ini.');
        }

        $validated = $request->validate([
            'no_ktp' => ['required', 'string', 'size:16', 'regex:/^[0-9]+$/'],
            'no_npwp' => ['required', 'string', 'min:15', 'max:20'],
            'distributor_province_id' => ['required', 'exists:provinces,id'],
            'distributor_regency_id' => ['required', 'exists:regencies,id'],
            'distributor_address' => ['required', 'string', 'min:10'],
        ], [
            'no_ktp.required' => 'Nomor KTP wajib diisi.',
            'no_ktp.size' => 'Nomor KTP harus 16 digit.',
            'no_ktp.regex' => 'Nomor KTP hanya boleh berisi angka.',
            'no_npwp.required' => 'Nomor NPWP wajib diisi.',
            'no_npwp.min' => 'Nomor NPWP minimal 15 karakter.',
            'distributor_province_id.required' => 'Provinsi wajib dipilih.',
            'distributor_regency_id.required' => 'Kabupaten/Kota wajib dipilih.',
            'distributor_address.required' => 'Alamat lengkap wajib diisi.',
            'distributor_address.min' => 'Alamat lengkap minimal 10 karakter.',
        ]);

        $user->update([
            'no_ktp' => $validated['no_ktp'],
            'no_npwp' => $validated['no_npwp'],
            'distributor_province_id' => $validated['distributor_province_id'],
            'distributor_regency_id' => $validated['distributor_regency_id'],
            'distributor_address' => $validated['distributor_address'],
            'distributor_status' => 'pending',
            'distributor_applied_at' => now(),
        ]);

        return redirect()->route('buyer.distributor.status')
            ->with('success', 'Pengajuan distributor berhasil dikirim. Mohon tunggu proses verifikasi oleh admin.');
    }

    /**
     * Show the application status.
     */
    public function status()
    {
        $user = Auth::user()->load(['distributorProvince', 'distributorRegency']);
        return view('buyer.distributor.status', compact('user'));
    }

    /**
     * Get regencies by province.
     */
    public function getRegencies(Request $request)
    {
        $regencies = Regency::where('province_id', $request->province_id)->orderBy('name')->get();
        return response()->json($regencies);
    }
}

