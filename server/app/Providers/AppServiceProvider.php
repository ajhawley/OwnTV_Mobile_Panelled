<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No third-party providers are required for the first release.
    }

    public function boot(): void
    {
        // Intentionally empty; feature-specific configuration lives with its owning service.
    }
}
