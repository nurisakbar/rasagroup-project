<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        if (config('jubelio.status_poll.enabled', true)) {
            $minutes = max(1, (int) config('jubelio.status_poll.interval_minutes', 5));
            $schedule->command('jubelio:poll-order-status')
                ->cron("*/{$minutes} * * * *")
                ->withoutOverlapping();
        }
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Di belakang nginx host yang terminate TLS (X-Forwarded-Proto).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'agent' => \App\Http\Middleware\EnsureUserIsAgent::class,
            'warehouse' => \App\Http\Middleware\WarehouseAccess::class,
            'driippreneur' => \App\Http\Middleware\DriippreneurAccess::class,
            'distributor' => \App\Http\Middleware\DistributorAccess::class,
            'wa.verified' => \App\Http\Middleware\EnsureWhatsAppIsVerified::class,
        ]);
        
        // Add affiliate tracking to web middleware
        $middleware->web(append: [
            \App\Http\Middleware\AffiliateReferralMiddleware::class,
            \App\Http\Middleware\CheckHubSelection::class,
        ]);
        
        // API routes do not require authentication by default in Laravel 11
        // All routes in routes/api.php are public
        
        // Exclude Xendit webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/xendit',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if (! $request->routeIs('cart.*')) {
                return null;
            }

            $message = 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.';
            $loginUrl = route('login', ['reason' => 'add_to_cart']);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $message,
                    'redirect' => $loginUrl,
                ], 401);
            }

            return redirect()->guest($loginUrl)->with('error', $message);
        });
    })->create();
