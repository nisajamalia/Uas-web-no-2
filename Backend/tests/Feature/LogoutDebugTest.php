<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_logout_flow(): void
    {
        $user = User::factory()->create();
        
        // Create token
        $tokenResult = $user->createToken('test-token');
        $token = $tokenResult->plainTextToken;
        $tokenModel = $tokenResult->accessToken;
        
        echo "\nToken ID: " . $tokenModel->id;
        echo "\nToken: " . substr($token, 0, 10) . "...";
        
        // Verify token works
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');
        
        echo "\nProfile before logout: " . $response->getStatusCode();
        
        // Check database before logout
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenModel->id
        ]);
        
        // Debug: Check what currentAccessToken returns
        $authenticatedUser = $this->actingAs($user, 'sanctum')->get('/api/profile');
        echo "\nActing as user works: " . $authenticatedUser->getStatusCode();
        
        // Try to get current access token through the request
        $request = new \Illuminate\Http\Request();
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        // Manually authenticate the request to see what happens
        $guard = \Auth::guard('sanctum');
        $authenticatedUser = $guard->user();
        
        if ($authenticatedUser) {
            echo "\nUser authenticated via guard: YES";
            $currentToken = $authenticatedUser->currentAccessToken();
            echo "\nCurrent token exists: " . ($currentToken ? 'YES' : 'NO');
            if ($currentToken) {
                echo "\nCurrent token ID: " . $currentToken->id;
            }
        } else {
            echo "\nUser authenticated via guard: NO";
        }
        
        // Logout using the API endpoint
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
        
        echo "\nLogout response: " . $logoutResponse->getStatusCode();
        
        // Check database after logout
        $tokenExists = \DB::table('personal_access_tokens')->where('id', $tokenModel->id)->exists();
        echo "\nToken exists after logout: " . ($tokenExists ? 'YES' : 'NO');
        
        $this->assertTrue(true); // Just to make test pass for debugging
    }
}