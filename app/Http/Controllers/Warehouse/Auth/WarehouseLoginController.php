<?php

namespace App\Http\Controllers\Warehouse\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseLoginController extends Controller
{
    /**
     * Show the warehouse login form.
     */
    public function create()
    {
        return view('warehouse.auth.login');
    }

    /**
     * Handle warehouse login request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is warehouse staff
            if ($user->role !== 'warehouse' || !$user->warehouse_id) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun staff warehouse.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('warehouse.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle warehouse logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('warehouse.login');
    }
}

