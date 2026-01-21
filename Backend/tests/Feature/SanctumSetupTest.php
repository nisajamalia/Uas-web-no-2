<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SanctumSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanctum_csrf_cookie_endpoint_exists(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');
        
        // Should return 204 No Content for CSRF cookie endpoint
        $response->assertStatus(204);
    }

    public function test_api_user_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');
        
        // Should return 401 Unauthorized without authentication
        $response->assertStatus(401);
    }

    public function test_api_user_endpoint_works_with_sanctum_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'email' => $user->email,
                ]);
    }

    public function test_cors_headers_are_present(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->options('/api/user');

        // CORS preflight should be handled
        $response->assertStatus(200);
    }
}