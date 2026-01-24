<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

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
        Vite::prefetch(concurrency: 3);

        try {
            Gate::before(function ($user, $ability) {
                return $user->hasRole('superadmin') ? true : null;
            });

            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                Permission::all()->map(function ($permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                });
            }

            // Register Observers
            \App\Models\Permohonan::observe(\App\Observers\PermohonanObserver::class);

        } catch (\Exception $e) {
            //
        }
    }
}
