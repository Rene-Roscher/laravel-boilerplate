<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Model::preventLazyLoading();

        // Force HTTPS in production for all generated URLs
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Use our custom PersonalAccessToken model with Snowflake IDs
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
