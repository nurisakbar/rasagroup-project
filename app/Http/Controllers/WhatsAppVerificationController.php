<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\QadWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppVerificationController extends Controller
{
    protected $whatsappService;

    public function __construct(QadWhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function show(Request $request)
    {
        $user = $request->user();

        // Bypass for admin and distributor
        if ($user->isSuperAdmin() || $user->isDistributor()) {
            return redirect()->intended(route('dashboard'));
        }

        return $user->wa_verified_at
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-wa');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($request->code === $user->wa_verification_code) {
            $user->update([
                'wa_verified_at' => now(),
                'wa_verification_code' => null,
            ]);

            return redirect()->intended(route('dashboard'))->with('success', 'Nomor WhatsApp berhasil diverifikasi.');
        }

        return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        
        // Generate new code if not exists
        if (!$user->wa_verification_code) {
            $user->wa_verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->save();
        }

        $message = "Halo {$user->name}, kode verifikasi Anda adalah: {$user->wa_verification_code}. Segera masukkan kode ini untuk mengaktifkan akun Anda.";
        
        $result = $this->whatsappService->sendText($user->phone, $message);

        if ($result['success']) {
            return back()->with('status', 'verification-link-sent');
        }

        return back()->with('error', 'Gagal mengirim kode verifikasi. Pastikan nomor Anda benar.');
    }

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|unique:users,phone,' . Auth::id(),
        ]);

        $user = $request->user();
        $user->phone = $request->phone;
        $user->wa_verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->save();

        $message = "Halo {$user->name}, kode verifikasi WhatsApp Anda adalah: {$user->wa_verification_code}. Segera masukkan kode ini untuk mengaktifkan akun Anda.";
        $this->whatsappService->sendText($user->phone, $message);

        return back()->with('status', 'verification-link-sent')->with('success', 'Nomor WhatsApp berhasil diperbarui.');
    }
}
