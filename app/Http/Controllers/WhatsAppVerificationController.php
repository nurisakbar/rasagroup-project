<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WhatsAppVerificationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Bypass for admin and distributor
        if ($user->isSuperAdmin() || $user->isDistributor()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->wa_verified_at) {
            return redirect()->route('wa.verify.success');
        }

        // OTP dikirim saat halaman verifikasi dibuka (bukan saat registrasi), dengan jeda agar refresh
        // halaman tidak mengirim ulang terus-menerus.
        $otpSent = $this->sendOtpIfDue($user);

        return view('auth.verify-wa', ['otpSent' => $otpSent]);
    }

    /** Jeda minimal antar pengiriman OTP otomatis saat membuka halaman verifikasi. */
    private const OTP_AUTO_SEND_COOLDOWN_SECONDS = 120;

    /**
     * Kirim OTP bila nomor sudah ada dan belum dikirim dalam jeda terakhir.
     * Mengembalikan true bila OTP sudah/baru dikirim (pengguna tinggal memasukkan kode).
     */
    private function sendOtpIfDue(User $user): bool
    {
        if (! $user->phone) {
            return false;
        }

        $cacheKey = 'wa_otp_auto_sent:' . $user->id;
        if (Cache::has($cacheKey)) {
            return true;
        }

        if (! $user->wa_verification_code) {
            $user->wa_verification_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->save();
        }

        \App\Jobs\SendWhatsAppVerificationJob::dispatch($user, $user->wa_verification_code);
        Cache::put($cacheKey, true, self::OTP_AUTO_SEND_COOLDOWN_SECONDS);

        return true;
    }

    /**
     * Halaman konfirmasi setelah nomor WhatsApp berhasil diverifikasi.
     */
    public function success(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin() || $user->isDistributor()) {
            return redirect()->intended(route('dashboard'));
        }

        if (! $user->wa_verified_at) {
            return redirect()->route('wa.verify');
        }

        // Halaman asal sebelum dialihkan ke verifikasi (mis. halaman produk saat tambah ke keranjang).
        $continueUrl = $request->session()->pull('url.intended');
        if ($continueUrl && str_contains($continueUrl, '/verify-wa')) {
            $continueUrl = null;
        }

        return view('auth.verify-wa-success', ['continueUrl' => $continueUrl]);
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

            Cache::forget('wa_otp_auto_sent:' . $user->id);

            return redirect()->route('wa.verify.success');
        }

        return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if (!$user->phone) {
            return back()->with('error', 'Nomor WhatsApp belum terdaftar. Silakan masukkan nomor WhatsApp Anda.');
        }
        
        // Generate new code if not exists
        if (!$user->wa_verification_code) {
            $user->wa_verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->save();
        }

        \App\Jobs\SendWhatsAppVerificationJob::dispatch($user, $user->wa_verification_code);
        Cache::put('wa_otp_auto_sent:' . $user->id, true, self::OTP_AUTO_SEND_COOLDOWN_SECONDS);

        return back()->with('status', 'verification-link-sent');
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

        \App\Jobs\SendWhatsAppVerificationJob::dispatch($user, $user->wa_verification_code);
        Cache::put('wa_otp_auto_sent:' . $user->id, true, self::OTP_AUTO_SEND_COOLDOWN_SECONDS);

        return back()->with('status', 'verification-link-sent')->with('success', 'Nomor WhatsApp berhasil diperbarui.');
    }
}
