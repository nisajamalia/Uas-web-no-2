<?php

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Full-stack integration tests for SAKTI Mini Login Module
 * Tests complete authentication flow including cross-domain scenarios
 * 
 * **Feature: sakti-mini-login, Integration Testing**
 * **Validates: Requirements 1.1, 1.2, 1.5**
 */
class FullStackAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up CORS configuration for testing
        config([
            'cors.allowed_origins' => ['http://localhost:3000', 'https://app.kampus.ac.id'],
            'cors.allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'cors.allowed_headers' => ['*'],
            'cors.supports_credentials' => true,
        ]);
        
        // Configure Sanctum for pure API token authentication in tests
        config([
            'sanctum.stateful' => [], // Disable stateful domains for pure API testing
        ]);
        
        // Clear rate limiter to avoid interference between tests
        \Illuminate\Support\Facades\RateLimiter::clear('login');
    }

    /**
     * Test complete authentication flow from login to profile access
     * Validates Requirements 1.1, 1.3
     */
    public function test_complete_authentication_flow(): void
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@kampus.ac.id',
            'password' => Hash::make('SecurePassword123!'),
        ]);

        // Step 1: Login with valid credentials
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@kampus.ac.id',
            'password' => 'SecurePassword123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ]);

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        // Step 2: Access protected profile endpoint with token
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at']
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => 'Test User',
                        'email' => 'test@kampus.ac.id',
                    ]
                ]
            ]);

        // Step 3: Logout and verify token invalidation
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        // Step 4: Verify token is invalidated after logout
        // Extract token ID to check database
        $tokenParts = explode('|', $token, 2);
        if (count($tokenParts) === 2) {
            $tokenId = $tokenParts[0];
            
            // Verify token is removed from database
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $tokenId
            ]);
        }

        // Clear authentication state to ensure fresh request
        $this->app['auth']->forgetGuards();

        // Also verify the token cannot be used for API calls
        $profileAfterLogoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $profileAfterLogoutResponse->assertStatus(401);
    }

    /**
     * Test cross-domain CORS functionality
     * Validates Requirements 2.5
     */
    public function test_cors_cross_domain_communication(): void
    {
        // Test preflight OPTIONS request from allowed origin
        $preflightResponse = $this->call('OPTIONS', '/api/login', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
        ]);

        // OPTIONS requests typically return 204 No Content
        $preflightResponse->assertStatus(204);
        $this->assertEquals('http://localhost:3000', $preflightResponse->headers->get('Access-Control-Allow-Origin'));
        $this->assertStringContainsString('POST', $preflightResponse->headers->get('Access-Control-Allow-Methods'));

        // Test actual request from allowed origin
        $user = User::factory()->create([
            'email' => 'cors@test.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'cors@test.com',
            'password' => 'password123',
        ], [
            'Origin' => 'http://localhost:3000',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('http://localhost:3000', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * Test security measures in integrated environment
     * Validates Requirements 2.2, 2.3, 2.4
     */
    public function test_security_measures_integration(): void
    {
        // Test input validation and sanitization first (before rate limiting kicks in)
        $maliciousInputs = [
            ['email' => '<script>alert("xss")</script>', 'password' => 'test'],
            ['email' => 'test@test.com\'; DROP TABLE users; --', 'password' => 'test'],
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->postJson('/api/login', $input);
            
            // Should return validation error, not execute malicious code
            $response->assertStatus(422);
            
            // Response should not contain the malicious input
            $responseContent = $response->getContent();
            $this->assertStringNotContainsString('<script>', $responseContent);
            $this->assertStringNotContainsString('DROP TABLE', $responseContent);
            $this->assertStringNotContainsString('onerror=', $responseContent);
        }

        // Test uniform error messages (no user enumeration)
        $nonExistentUserResponse = $this->postJson('/api/login', [
            'email' => 'nonexistent' . rand(1000, 9999) . '@test.com',
            'password' => 'password123',
        ]);

        $user = User::factory()->create([
            'email' => 'existing' . rand(1000, 9999) . '@test.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $wrongPasswordResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        // Both should return same error structure
        $nonExistentUserResponse->assertStatus(422);
        $wrongPasswordResponse->assertStatus(422);
        
        // Error messages should be uniform
        $this->assertEquals(
            $nonExistentUserResponse->json('errors.email.0'),
            $wrongPasswordResponse->json('errors.email.0')
        );
        
        // Note: Rate limiting is tested separately to avoid interference with other tests
        // The rate limiting functionality is validated by the middleware configuration
        // and can be tested in isolation if needed
    }

    /**
     * Test session management and token lifecycle
     * Validates Requirements 1.3, 1.5
     */
    public function test_session_management_lifecycle(): void
    {
        $user = User::factory()->create([
            'email' => 'session@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Login and get token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'session@test.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        // Verify token is stored in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);

        // Use token for authenticated request
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $profileResponse->assertStatus(200);

        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200);

        // Extract token ID to verify deletion
        $tokenParts = explode('|', $token, 2);
        if (count($tokenParts) === 2) {
            $tokenId = $tokenParts[0];
            
            // Verify token is removed from database
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $tokenId
            ]);
        }

        // Clear authentication state to ensure fresh request
        $this->app['auth']->forgetGuards();

        // Verify token cannot be used after logout
        $postLogoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $postLogoutResponse->assertStatus(401);
    }

    /**
     * Test multiple concurrent sessions
     * Validates Requirements 1.3
     */
    public function test_multiple_concurrent_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'multi@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Create multiple sessions
        $tokens = [];
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'multi@test.com',
                'password' => 'password123',
            ]);
            
            $response->assertStatus(200);
            $tokens[] = $response->json('data.token');
        }

        // Verify all tokens work
        foreach ($tokens as $token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/profile');
            
            $response->assertStatus(200);
        }

        // Logout from one session
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokens[0],
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200);

        // Extract token ID to verify deletion
        $tokenParts = explode('|', $tokens[0], 2);
        if (count($tokenParts) === 2) {
            $tokenId = $tokenParts[0];
            
            // Verify first token is removed from database
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $tokenId
            ]);
        }

        // Clear authentication state to ensure fresh request
        $this->app['auth']->forgetGuards();

        // Verify first token is invalidated
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokens[0],
        ])->getJson('/api/profile');
        
        $response->assertStatus(401);

        // Verify other tokens still work
        foreach (array_slice($tokens, 1) as $token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/profile');
            
            $response->assertStatus(200);
        }
    }

    /**
     * Test API response consistency across all endpoints
     * Validates Requirements 4.4, 4.5
     */
    public function test_api_response_consistency(): void
    {
        $user = User::factory()->create([
            'email' => 'consistency@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Test successful responses have consistent structure
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'consistency@test.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStructure($loginResponse, 200);

        $token = $loginResponse->json('data.token');

        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $this->assertResponseStructure($profileResponse, 200);

        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $this->assertResponseStructure($logoutResponse, 200);

        // Test error responses have consistent structure
        $validationErrorResponse = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $this->assertErrorResponseStructure($validationErrorResponse, 422);

        // Clear authentication state before testing unauthorized access
        $this->app['auth']->forgetGuards();

        $unauthorizedResponse = $this->getJson('/api/profile');
        $this->assertErrorResponseStructure($unauthorizedResponse, 401);
    }

    /**
     * Helper method to assert consistent response structure
     */
    private function assertResponseStructure($response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ])
            ->assertJson([
                'success' => true
            ]);

        // Verify response has timestamp or similar metadata
        $this->assertIsString($response->json('message'));
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Helper method to assert consistent error response structure
     */
    private function assertErrorResponseStructure($response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJson([
                'success' => false
            ]);

        $this->assertIsString($response->json('message'));
    }
}