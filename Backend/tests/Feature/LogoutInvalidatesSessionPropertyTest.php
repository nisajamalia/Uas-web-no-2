<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutInvalidatesSessionPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * **Feature: sakti-mini-login, Property 5: Logout invalidates session completely**
     * **Validates: Requirements 1.5, 4.2**
     * 
     * Property: For any authenticated user, logout should invalidate the session/token 
     * and prevent further access to protected resources
     */
    public function test_logout_invalidates_session_completely_property(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            // Create a fresh user for each iteration
            $user = User::factory()->create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@test.com',
                'password' => 'password123',
            ]);

            // Create token for authentication
            $tokenResult = $user->createToken('test-token-' . $i);
            $token = $tokenResult->plainTextToken;
            $tokenModel = $tokenResult->accessToken;

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

            // Perform logout
            $logoutResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->postJson('/api/logout');

            // Verify logout response is successful
            $logoutResponse->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout successful'
                ]);

            // Property verification: Token should be completely invalidated
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $tokenModel->id
            ]);

            // Clear authentication guards to ensure fresh authentication check
            $this->app['auth']->forgetGuards();

            // Verify that the token can no longer be used to access protected resources
            $protectedResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->getJson('/api/profile');

            $protectedResponse->assertStatus(401);
        }
    }

    /**
     * Property: Multiple tokens for same user should be independently invalidated
     */
    public function test_logout_invalidates_only_current_token_property(): void
    {
        for ($i = 0; $i < 50; $i++) {
            // Clear authentication state
            $this->app['auth']->forgetGuards();
            
            $user = User::factory()->create([
                'email' => 'multitoken' . $i . rand(1000, 9999) . '@test.com'
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

            // Clear authentication guards
            $this->app['auth']->forgetGuards();

            // Verify second token still works
            $this->withHeaders(['Authorization' => 'Bearer ' . $token2, 'Accept' => 'application/json'])
                ->getJson('/api/profile')
                ->assertStatus(200);

            // Clear authentication state before second logout
            $this->app['auth']->forgetGuards();

            // Logout with second token
            $this->withHeaders(['Authorization' => 'Bearer ' . $token2, 'Accept' => 'application/json'])
                ->postJson('/api/logout')
                ->assertStatus(200);

            // Now both tokens should be gone
            $this->assertDatabaseMissing('personal_access_tokens', [
                'tokenable_id' => $user->id
            ]);
        }
    }
}