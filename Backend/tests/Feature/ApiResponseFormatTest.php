<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResponseFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_response_format(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'token',
                    ],
                    'timestamp',
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Login successful',
                ]);
    }

    public function test_validation_error_response_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors',
                    'timestamp',
                ])
                ->assertJson([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                ]);
    }

    public function test_authentication_error_response_format(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors',
                    'timestamp',
                ])
                ->assertJson([
                    'success' => false,
                    'message' => 'Authentication required.',
                ]);
    }

    public function test_not_found_error_response_format(): void
    {
        $response = $this->getJson('/api/nonexistent-endpoint');

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors',
                    'timestamp',
                ])
                ->assertJson([
                    'success' => false,
                    'message' => 'The requested endpoint was not found.',
                ]);
    }

    public function test_profile_response_format(): void
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
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'timestamp',
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile retrieved successfully',
                ]);
    }

    public function test_logout_response_format(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'timestamp',
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout successful',
                ]);
    }
}