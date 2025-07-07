<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class IgnitionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Only register Ignition in local environment and if the class exists
        if ($this->app->environment('local')) {
            try {
                if (class_exists('Spatie\\LaravelIgnition\\IgnitionServiceProvider')) {
                    $this->app->register('Spatie\\LaravelIgnition\\IgnitionServiceProvider');
                }
            } catch (\Throwable $e) {
                // Silently ignore if Ignition is not available
                // This prevents the application from crashing in production
                \Log::debug('Ignition not available: ' . $e->getMessage());
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
