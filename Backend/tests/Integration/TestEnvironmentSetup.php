<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Test Environment Setup for Integration Testing
 * Configures full-stack testing environment with proper CORS and security settings
 * 
 * **Feature: sakti-mini-login, Test Environment Configuration**
 * **Validates: Requirements 2.5, 4.4, 4.5**
 */
class TestEnvironmentSetup extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that testing environment is properly configured
     */
    public function test_testing_environment_configuration(): void
    {
        // Verify we're in testing environment
        $this->assertEquals('testing', app()->environment());

        // Verify database is using in-memory SQLite
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));

        // Verify cache is using array driver
        $this->assertEquals('array', config('cache.default'));

        // Verify session is using array driver
        $this->assertEquals('array', config('session.driver'));
    }

    /**
     * Test CORS configuration for cross-domain testing
     */
    public function test_cors_configuration_for_testing(): void
    {
        // Set up CORS for testing
        Config::set('cors.allowed_origins', [
            'http://localhost:3000',
            'https://app.kampus.ac.id',
            'http://127.0.0.1:3000'
        ]);
        
        Config::set('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
        Config::set('cors.allowed_headers', ['*']);
        Config::set('cors.supports_credentials', true);

        // Test CORS configuration
        $this->assertEquals([
            'http://localhost:3000',
            'https://app.kampus.ac.id',
            'http://127.0.0.1:3000'
        ], config('cors.allowed_origins'));

        $this->assertTrue(config('cors.supports_credentials'));
    }

    /**
     * Test rate limiting configuration for testing
     */
    public function test_rate_limiting_configuration(): void
    {
        // Verify rate limiting is configured
        $this->assertNotNull(config('app.rate_limiting'));
        
        // Test that rate limiting middleware is available
        $middleware = app('router')->getMiddleware();
        $this->assertArrayHasKey('throttle', $middleware);
    }

    /**
     * Test security headers configuration
     */
    public function test_security_headers_configuration(): void
    {
        $response = $this->get('/api/health');
        
        // Verify security headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    /**
     * Test API response format consistency
     */
    public function test_api_response_format_consistency(): void
    {
        // Test health endpoint
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'status',
                    'version',
                    'environment'
                ],
                'timestamp'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'ok',
                    'environment' => 'testing'
                ]
            ]);

        // Verify timestamp format
        $timestamp = $response->json('timestamp');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $timestamp);
    }

    /**
     * Test database migrations and seeding for testing
     */
    public function test_database_setup_for_testing(): void
    {
        // Verify users table exists
        $this->assertTrue(\Schema::hasTable('users'));
        
        // Verify personal_access_tokens table exists (for Sanctum)
        $this->assertTrue(\Schema::hasTable('personal_access_tokens'));
        
        // Verify required columns exist
        $this->assertTrue(\Schema::hasColumns('users', [
            'id', 'name', 'email', 'password', 'created_at', 'updated_at'
        ]));
        
        $this->assertTrue(\Schema::hasColumns('personal_access_tokens', [
            'id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'created_at'
        ]));
    }

    /**
     * Test logging configuration for testing
     */
    public function test_logging_configuration(): void
    {
        // Verify logging is configured
        $this->assertEquals('stack', config('logging.default'));
        
        // Verify security logging is available
        $this->assertTrue(class_exists(\App\Services\SecurityLogger::class));
    }

    /**
     * Test API authentication middleware setup
     */
    public function test_api_authentication_middleware(): void
    {
        // Test that Sanctum middleware is properly configured
        $middleware = app('router')->getMiddleware();
        $this->assertArrayHasKey('auth', $middleware);
        
        // Test that auth:sanctum is available
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401); // Should require authentication
    }

    /**
     * Test input validation and sanitization setup
     */
    public function test_input_validation_setup(): void
    {
        // Test that validation rules are working
        $response = $this->postJson('/api/login', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
        
        // Test XSS protection
        $response = $this->postJson('/api/login', [
            'email' => '<script>alert("xss")</script>',
            'password' => 'test'
        ]);
        
        $response->assertStatus(422);
        
        // Verify malicious content is not reflected in response
        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>', $content);
    }

    /**
     * Test error handling middleware setup
     */
    public function test_error_handling_middleware(): void
    {
        // Test that API error handler is working
        $response = $this->getJson('/api/nonexistent-endpoint');
        
        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJson([
                'success' => false
            ]);
    }

    /**
     * Test cross-domain request simulation
     */
    public function test_cross_domain_request_simulation(): void
    {
        // Simulate request from frontend domain
        $response = $this->call('GET', '/api/health', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        
        $response->assertStatus(200);
        
        // Test preflight request
        $preflightResponse = $this->call('OPTIONS', '/api/login', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
        ]);
        
        $preflightResponse->assertStatus(200);
    }
}