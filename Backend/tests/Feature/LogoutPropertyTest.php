<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutPropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_invalidates_session_completely_property(): void
    {
        // Run property test with fewer iterations for debugging
        for ($i = 0; $i < 5; $i++) {
            echo "\n=== Iteration $i ===";
            
            // Clear any existing authentication state
            $this->app['auth']->forgetGuards();
            
            // Generate random user data with unique email using timestamp
            $timestamp = now()->timestamp . $i . rand(1000, 9999);
            $user = User::factory()->create([
                'name' => 'User ' . $timestamp,
                'email' => 'user' . $timestamp . '@test.com',
                'password' => 'password123',
            ]);

            // Create token for authentication
            $tokenResult = $user->createToken('test-token-' . $timestamp);
            $token = $tokenResult->plainTextToken;
            $tokenModel = $tokenResult->accessToken;

            echo "\nCreated user {$user->id} with token {$tokenModel->id}";

            // Verify the user can access protected resources before logout
            $profileResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->getJson('/api/profile');

            $profileResponse->assertStatus(200);
            $this->assertDatabaseHas('personal_access_tokens', [
                'id' => $tokenModel->id,
                'tokenable_id' => $user->id,
            ]);

            // Clear authentication state before logout to ensure fresh request
            $this->app['auth']->forgetGuards();

            // Perform logout
            $logoutResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->postJson('/api/logout');

            echo "\nLogout status: " . $logoutResponse->getStatusCode();

            // Verify logout response is successful
            $logoutResponse->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout successful'
                ]);

            // Check if token exists after logout
            $tokenExists = \DB::table('personal_access_tokens')->where('id', $tokenModel->id)->exists();
            echo "\nToken exists after logout: " . ($tokenExists ? 'YES' : 'NO');

            // Property verification: Token should be completely invalidated
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $tokenModel->id
            ]);
            
            echo "\nIteration $i completed successfully";
        }
    }

    /**
     * Property test: Multiple tokens for same user should be independently invalidated
     */
    public function test_logout_invalidates_only_current_token_property(): void
    {
        for ($i = 0; $i < 50; $i++) {
            // Clear authentication state
            $this->app['auth']->forgetGuards();
            
            $user = User::factory()->create([
                'email' => 'user' . $i . rand(1000, 9999) . '@test.com'
            ]);

            // Create multiple tokens for the same user
            $token1Result = $user->createToken('token-1-' . $i);
            $token1 = $token1Result->plainTextToken;
            $token1Model = $token1Result->accessToken;

            $token2Result = $user->createToken('token-2-' . $i);
            $token2 = $token2Result->plainTextToken;
            $token2Model = $token2Result->accessToken;

            // Verify both tokens work
            $this->withHeaders(['Authorization' => 'Bearer ' . $token1, 'Accept' => 'application/json'])
                ->getJson('/api/profile')
                ->assertStatus(200);

            $this->withHeaders(['Authorization' => 'Bearer ' . $token2, 'Accept' => 'application/json'])
                ->getJson('/api/profile')
                ->assertStatus(200);

            // Clear authentication state before logout
            $this->app['auth']->forgetGuards();

            // Logout with first token
            $this->withHeaders(['Authorization' => 'Bearer ' . $token1, 'Accept' => 'application/json'])
                ->postJson('/api/logout')
                ->assertStatus(200);

            // Property: Only the current token should be invalidated
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $token1Model->id
            ]);

            $this->assertDatabaseHas('personal_access_tokens', [
                'id' => $token2Model->id
            ]);

            // Clear authentication state before second logout
            $this->app['auth']->forgetGuards();

            // Verify second token still exists and can be used for logout
            $this->withHeaders(['Authorization' => 'Bearer ' . $token2, 'Accept' => 'application/json'])
                ->postJson('/api/logout')
                ->assertStatus(200);

            // Now both tokens should be gone
            $this->assertDatabaseMissing('personal_access_tokens', [
                'tokenable_id' => $user->id
            ]);
        }
    }

    /**
     * Property test: Logout without authentication should fail consistently
     */
    public function test_logout_requires_authentication_property(): void
    {
        for ($i = 0; $i < 25; $i++) {
            // Test various invalid authentication scenarios
            $invalidTokens = [
                '', // Empty token
                'invalid-token', // Invalid format
                'Bearer invalid', // Invalid bearer token
                fake()->uuid(), // Random UUID
                fake()->sha256(), // Random hash
            ];

            foreach ($invalidTokens as $invalidToken) {
                $headers = ['Accept' => 'application/json'];
                if (!empty($invalidToken)) {
                    $headers['Authorization'] = 'Bearer ' . $invalidToken;
                }
                
                $response = $this->withHeaders($headers)->postJson('/api/logout');
                
                // Property: All unauthenticated logout attempts should fail with 401
                $response->assertStatus(401);
            }
        }
    }

    /**
     * Property test: Logout endpoint should always return consistent JSON format
     */
    public function test_logout_response_format_property(): void
    {
        for ($i = 0; $i < 25; $i++) {
            
            $user = User::factory()->create();
            $token = $user->createToken('test-token')->plainTextToken;

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->postJson('/api/logout');

            // Property: Logout should always return consistent JSON structure
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout successful'
                ]);
        }
    }
}