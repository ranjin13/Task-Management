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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only enable Debugbar in local development
        if (app()->environment('local')) {
            if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
                \Barryvdh\Debugbar\Facades\Debugbar::enable();
            }
        } else {
            // Explicitly disable Debugbar in non-local environments
            if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
                \Barryvdh\Debugbar\Facades\Debugbar::disable();
            }
        }
    }
}
