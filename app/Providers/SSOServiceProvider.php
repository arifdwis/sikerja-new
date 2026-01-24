<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SSOServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge SSO config tanpa memuat routes dari package
        $this->mergeConfigFrom(config_path('sso.php'), 'sso');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
