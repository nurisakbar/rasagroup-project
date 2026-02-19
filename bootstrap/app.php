<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'agent' => \App\Http\Middleware\EnsureUserIsAgent::class,
            'warehouse' => \App\Http\Middleware\WarehouseAccess::class,
            'driippreneur' => \App\Http\Middleware\DriippreneurAccess::class,
            'distributor' => \App\Http\Middleware\DistributorAccess::class,
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
        //
    })->create();
