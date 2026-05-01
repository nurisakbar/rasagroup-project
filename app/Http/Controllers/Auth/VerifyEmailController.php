<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Verify a user's email address from a signed URL.
     *
     * This endpoint must work for guest users (no session).
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = User::query()->find($id);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Link verifikasi tidak valid.');
        }

        $expectedHash = sha1($user->getEmailForVerification());

        if (!hash_equals($expectedHash, (string) $hash)) {
            return redirect()->route('login')->with('error', 'Link verifikasi tidak valid.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('verification.success')->with('status', 'Email Anda sudah terverifikasi.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('verification.success');
    }
}
