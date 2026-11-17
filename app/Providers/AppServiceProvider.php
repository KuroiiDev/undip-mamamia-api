<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
{
    // Note: Strict mode only non-production env
    Model::shouldBeStrict(! app()->isProduction());

    // Note: Rate limit for API 120 per minute
    RateLimiter::for('api', fn (Request $r) =>
        Limit::perMinute(120)->by($r->user()?->id ?: $r->ip())
    );

    // Note: Rate limit for Auth 10 per minute
    RateLimiter::for('auth', fn (Request $r) =>
        Limit::perMinute(10)->by($r->ip())
    );
}
}
