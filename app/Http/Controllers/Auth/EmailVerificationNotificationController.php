<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $debugId = (string) Str::uuid();

        Log::info('Email verification resend requested', [
            'debug_id' => $debugId,
            'route' => optional($request->route())->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->getKey(),
            'email' => $user?->email,
            'email_verified_at' => $user?->email_verified_at,
            'mail' => [
                'default' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username'),
                'from' => config('mail.from.address'),
            ],
            'queue' => [
                'default' => config('queue.default'),
                'connection' => config('queue.connections.' . config('queue.default') . '.driver'),
            ],
        ]);

        if ($user->hasVerifiedEmail()) {
            Log::info('Email verification resend skipped: already verified', [
                'debug_id' => $debugId,
                'user_id' => $user->getKey(),
                'email' => $user->email,
            ]);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $user->sendEmailVerificationNotification();

            Log::info('Email verification resend dispatched', [
                'debug_id' => $debugId,
                'user_id' => $user->getKey(),
                'email' => $user->email,
            ]);

            return back()->with('status', 'verification-link-sent');
        } catch (Throwable $e) {
            Log::error('Email verification resend failed', [
                'debug_id' => $debugId,
                'user_id' => $user->getKey(),
                'email' => $user->email,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim email verifikasi.')->with('debug_id', $debugId);
        }
    }
}
