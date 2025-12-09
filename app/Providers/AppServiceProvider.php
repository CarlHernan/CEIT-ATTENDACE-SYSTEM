<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // API rate limiter: unauthenticated 60/min, authenticated 300/min (by user id).
        RateLimiter::for('api', function (Request $request) {
            $max = $request->user() ? 300 : 60;
            return [
                Limit::perMinute($max)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}
