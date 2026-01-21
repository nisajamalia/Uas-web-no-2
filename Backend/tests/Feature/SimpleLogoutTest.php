<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_logout(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('test-token');
        $token = $tokenResult->plainTextToken;
        $tokenModel = $tokenResult->accessToken;
        
        echo "\nBefore logout - Token ID: " . $tokenModel->id;
        
        // Verify token exists
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenModel->id]);
        
        // Logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
        
        echo "\nLogout status: " . $response->getStatusCode();
        
        // Check if token is deleted
        $tokenExists = \DB::table('personal_access_tokens')->where('id', $tokenModel->id)->exists();
        echo "\nAfter logout - Token exists: " . ($tokenExists ? 'YES' : 'NO');
        
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenModel->id]);
    }
}