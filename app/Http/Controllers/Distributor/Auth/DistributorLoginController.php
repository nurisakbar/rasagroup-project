<?php

namespace App\Http\Controllers\Distributor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributorLoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return view('distributor.auth.login');
    }

    /**
     * Handle login request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is Distributor
            if (!$user->isDistributor()) {
                // Check if user has approved distributor application
                if ($user->isDistributorApproved() && $user->warehouse_id) {
                    // User was approved but role might not be updated - this shouldn't happen but handle it
                    $request->session()->regenerate();
                    return redirect()->intended(route('distributor.dashboard'));
                }

                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun Distributor. Silakan daftar sebagai distributor terlebih dahulu.',
                ])->withInput();
            }

            // Check if assigned to warehouse
            if (!$user->warehouse_id) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda belum terdaftar di hub manapun. Hubungi admin.',
                ])->withInput();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('distributor.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    /**
     * Handle logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('distributor.login');
    }
}

