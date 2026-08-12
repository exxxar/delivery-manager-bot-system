<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

        $this->app->singleton(\App\Services\UserLogger::class, function ($app) {
            return new \App\Services\UserLogger();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Inertia::share('appVersion', env('APP_VERSION', '1.0.0'));
        Inertia::share('forceUpdate', env('APP_FORCE_UPDATE', false));
    }
}
