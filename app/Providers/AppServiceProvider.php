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
        if (config('app.env') === 'local' || config('app.debug') === true) {
            if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
                \Barryvdh\Debugbar\Facades\Debugbar::enable();
            }
        }
    }
}
