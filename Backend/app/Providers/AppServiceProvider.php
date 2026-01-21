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
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Login rate limiting with progressive delays for brute-force protection
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            
            // Rate limit by IP address (5 attempts per minute)
            $ipLimit = Limit::perMinute(5)->by($request->ip());
            
            // Additional rate limiting by email if provided (3 attempts per minute per email)
            if ($email) {
                $emailLimit = Limit::perMinute(3)->by($email);
                return [$ipLimit, $emailLimit];
            }
            
            return $ipLimit;
        });

        // Registration rate limiting to prevent spam accounts
        RateLimiter::for('register', function (Request $request) {
            // Rate limit by IP address (3 registrations per hour)
            $ipLimit = Limit::perHour(3)->by($request->ip());
            
            // Additional rate limiting by email domain to prevent bulk registrations
            $email = $request->input('email');
            if ($email && str_contains($email, '@')) {
                $domain = substr($email, strpos($email, '@'));
                $domainLimit = Limit::perHour(10)->by($domain);
                return [$ipLimit, $domainLimit];
            }
            
            return $ipLimit;
        });

        // API rate limiting for general endpoints
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // Stricter rate limiting for authenticated endpoints
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
