<?php

namespace App\Http\Controllers\Driippreneur\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriippreneurLoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return view('driippreneur.auth.login');
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
            // Check if user is DRiiPPreneur
            if (!Auth::user()->isDriippreneur()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun DRiiPPreneur.',
                ])->withInput();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('driippreneur.dashboard'));
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

        return redirect()->route('driippreneur.login');
    }
}

