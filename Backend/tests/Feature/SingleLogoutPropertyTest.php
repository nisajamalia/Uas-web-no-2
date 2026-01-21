<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleLogoutPropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_logout_property(): void
    {
        // Generate random user data
        $user = User::factory()->create([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(8, 20),
        ]);

        // Create token for authentication
        $tokenResult = $user->createToken('test-token-' . fake()->uuid());
        $token = $tokenResult->plainTextToken;
        $tokenModel = $tokenResult->accessToken;

        echo "\nCreated token ID: " . $tokenModel->id;
        echo "\nUser ID: " . $user->id;

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

        echo "\nProfile access before logout: SUCCESS";

        // Perform logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/logout');

        echo "\nLogout response status: " . $logoutResponse->getStatusCode();

        // Verify logout response is successful
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        // Check if token still exists
        $tokenExists = \DB::table('personal_access_tokens')->where('id', $tokenModel->id)->exists();
        echo "\nToken exists after logout: " . ($tokenExists ? 'YES' : 'NO');

        // Property verification: Token should be completely invalidated
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenModel->id
        ]);
    }
}