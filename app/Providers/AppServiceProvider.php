<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Render terminates TLS at its HTTPS edge, so force absolute asset and
        // pagination URLs to HTTPS there without trusting forwarded headers.
        if (env('RENDER')) {
            URL::forceScheme('https');
        }
    }
}
