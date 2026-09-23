<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
        $sessionId = $request->session()->getId();

        $request->authenticate();

        $request->session()->regenerate();

        \App\Models\Cart::mergeSessionCartToUser(Auth::id(), $sessionId);

        return $this->redirectAfterLogin();
    }

    public function createByEmail(): View
    {
        return view('auth.login-email');
    }

    public function storeByEmail(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Akun dengan email tersebut tidak ditemukan.',
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        \App\Models\Cart::mergeSessionCartToUser(Auth::id(), $sessionId);

        return $this->redirectAfterLogin();
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        $adminRoles = ['agent', 'super_admin', 'ecommerce', 'brand_marketing', 'sales', 'finance'];

        if (in_array(Auth::user()->role, $adminRoles, true)) {
            Auth::guard('admin')->login(Auth::user());

            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        $intendedUrl = redirect()->getIntendedUrl();
        if ($intendedUrl && strpos($intendedUrl, '/admin') !== false) {
            return redirect()->route('home');
        }

        return redirect()->intended(route('home', absolute: false));
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
