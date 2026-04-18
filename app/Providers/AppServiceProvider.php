<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('verify-reset-otp', function (Request $request): Limit {
            return Limit::perMinute(6)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('resend-reset-otp', function (Request $request): Limit {
            return Limit::perMinute(4)->by(mb_strtolower((string) $request->input('email_or_phone')).'|'.$request->ip());
        });

        RateLimiter::for('resend-verification', function (Request $request): Limit {
            return Limit::perMinute(3)->by((string) $request->user()?->id.'|'.$request->ip());
        });

        RateLimiter::for('refresh-token', function (Request $request): Limit {
            return Limit::perMinute(10)->by(hash('sha256', (string) $request->input('refresh_token')).'|'.$request->ip());
        });
    }
}
