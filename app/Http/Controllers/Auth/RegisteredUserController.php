<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, \App\Services\QadWhatsAppService $whatsappService): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
        ]);

        $waCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'buyer',
            'wa_verification_code' => $waCode,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Send WhatsApp Verification Code
        $message = "Halo {$user->name}, selamat datang di Rasagroup! Kode verifikasi Anda adalah: {$waCode}. Segera masukkan kode ini untuk mengaktifkan akun Anda.";
        $whatsappService->sendText($user->phone, $message);

        return redirect(route('wa.verify', absolute: false));
    }
}
