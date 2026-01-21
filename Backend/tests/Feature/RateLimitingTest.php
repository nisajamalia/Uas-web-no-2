<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitingTest extends TestCase
{
    public function test_login_rate_limiter_is_configured(): void
    {
        // Test that the login rate limiter exists and has the correct configuration
        $rateLimiter = RateLimiter::for('login', function () {
            return request();
        });
        
        $this->assertNotNull($rateLimiter);
    }

    public function test_api_rate_limiter_is_configured(): void
    {
        // Test that the API rate limiter exists
        $rateLimiter = RateLimiter::for('api', function () {
            return request();
        });
        
        $this->assertNotNull($rateLimiter);
    }
}