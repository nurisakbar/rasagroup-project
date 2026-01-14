<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Merge cart dari session ke user_id saat login
        $this->mergeCart();

        // Redirect berdasarkan role
        if (in_array(Auth::user()->role, ['agent', 'super_admin'])) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('buyer.dashboard', absolute: false));
    }

    private function mergeCart(): void
    {
        $sessionId = session()->getId();
        $sessionCarts = \App\Models\Cart::where('session_id', $sessionId)->get();

        if ($sessionCarts->isEmpty()) {
            return;
        }

        foreach ($sessionCarts as $sessionCart) {
            $userCart = \App\Models\Cart::where('user_id', Auth::id())
                ->where('product_id', $sessionCart->product_id)
                ->first();

            if ($userCart) {
                // Merge quantity
                $userCart->quantity += $sessionCart->quantity;
                $userCart->save();
                $sessionCart->delete();
            } else {
                // Transfer cart ke user
                $sessionCart->user_id = Auth::id();
                $sessionCart->session_id = null;
                $sessionCart->save();
            }
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
