<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutDebugMultiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_multi_token_logout(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create multiple tokens for the same user
        $token1Result = $user->createToken('token-1');
        $token1 = $token1Result->plainTextToken;
        $token1Model = $token1Result->accessToken;

        $token2Result = $user->createToken('token-2');
        $token2 = $token2Result->plainTextToken;
        $token2Model = $token2Result->accessToken;

        echo "\nToken 1 ID: " . $token1Model->id;
        echo "\nToken 2 ID: " . $token2Model->id;
        echo "\nToken 1 hash: " . hash('sha256', $token1);
        echo "\nToken 2 hash: " . hash('sha256', $token2);

        // Verify both tokens work
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->getJson('/api/profile')
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->getJson('/api/profile')
            ->assertStatus(200);

        echo "\nBoth tokens work initially";

        // Check database before logout
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token1Model->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token2Model->id]);

        // Logout with first token
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->postJson('/api/logout');

        echo "\nLogout 1 status: " . $response->getStatusCode();

        // Check what tokens remain
        $token1Exists = \DB::table('personal_access_tokens')->where('id', $token1Model->id)->exists();
        $token2Exists = \DB::table('personal_access_tokens')->where('id', $token2Model->id)->exists();
        
        echo "\nAfter logout 1 - Token 1 exists: " . ($token1Exists ? 'YES' : 'NO');
        echo "\nAfter logout 1 - Token 2 exists: " . ($token2Exists ? 'YES' : 'NO');

        $this->assertTrue(true); // Just to make test pass for debugging
    }
}