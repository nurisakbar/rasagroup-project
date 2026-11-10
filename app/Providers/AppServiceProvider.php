<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Konteks harga per warehouse, baru untuk setiap request / job.
        $this->app->scoped(\App\Support\Pricing\PricingContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('distributor-api', function (\Illuminate\Http\Request $request) {
            $client = $request->attributes->get(\App\Http\Middleware\VerifyDistributorApiKey::CLIENT_ATTRIBUTE);

            return \Illuminate\Cache\RateLimiting\Limit::perMinute(120)->by($client?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('globalCategories', \App\Models\Category::forStorefrontSidebar());
        });

        \App\Jobs\SyncQadInventoryJob::ensureQueued();
        \App\Jobs\SyncJubelioStockJob::ensureQueued();
    }
}
