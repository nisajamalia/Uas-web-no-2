<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Attempt login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
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
    }

    public function test_login_with_invalid_credentials(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_errors(): void
    {
        // Test missing email
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test invalid email format
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test short password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_profile_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_profile_endpoint_with_authentication(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at']
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully'
            ]);
    }

    public function test_logout_endpoint(): void
    {
        $user = User::factory()->create();
        
        // Create token and get the actual token model
        $tokenResult = $user->createToken('test-token');
        $token = $tokenResult->plainTextToken;
        $tokenModel = $tokenResult->accessToken;

        // First verify the token works
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');
        $response->assertStatus(200);

        // Logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        // Verify the token was deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenModel->id
        ]);
    }
}