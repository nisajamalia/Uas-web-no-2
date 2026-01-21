<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Log registration attempt for security monitoring
        SecurityLogger::logSecurityEvent('registration_attempt', [
            'email' => $validated['email'],
            'name' => $validated['name'],
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        try {
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create token for the new user
            $tokenName = 'auth-token-' . now()->timestamp;
            $token = $user->createToken($tokenName, ['*'], now()->addDays(30))->plainTextToken;

            // Log successful registration
            SecurityLogger::logSecurityEvent('registration_success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            return $this->successResponse(
                'Registration successful',
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ],
                201
            );
        } catch (\Exception $e) {
            // Log registration failure
            SecurityLogger::logSecurityEvent('registration_failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            return $this->errorResponse('Registration failed', 500);
        }
    }
    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Log login attempt for security monitoring
        SecurityLogger::logSecurityEvent('login_attempt', [
            'email' => $credentials['email'],
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Log failed login attempt
            SecurityLogger::logFailedLogin(
                $request,
                $credentials['email'],
                !$user ? 'user_not_found' : 'invalid_password'
            );

            // Return uniform error message to prevent user enumeration
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token for the user with device information
        $tokenName = 'auth-token-' . now()->timestamp;
        $token = $user->createToken($tokenName, ['*'], now()->addDays(30))->plainTextToken;

        // Log successful login
        SecurityLogger::logSuccessfulLogin($request, $user->id, $user->email);

        return $this->successResponse(
            'Login successful',
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ]
        );
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $ip = $request->ip();
        
        // Log logout attempt
        SecurityLogger::logLogout($request, $user->id);
        
        // Get the authorization header to extract the token
        $authHeader = $request->header('Authorization');
        
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $plainToken = substr($authHeader, 7);
            
            // Extract the token ID from the plain token (format: ID|hash)
            $tokenParts = explode('|', $plainToken, 2);
            
            if (count($tokenParts) === 2) {
                $tokenId = $tokenParts[0];
                $tokenValue = $tokenParts[1];
                
                // Hash the token value (this is how Sanctum stores it)
                $hashedToken = hash('sha256', $tokenValue);
                
                // Find the token by ID and hash to ensure it belongs to this user
                $tokenModel = $user->tokens()
                    ->where('id', $tokenId)
                    ->where('token', $hashedToken)
                    ->first();
                
                if ($tokenModel) {
                    $tokenModel->delete();
                    
                    Log::info('Token deleted successfully', [
                        'user_id' => $user->id,
                        'token_id' => $tokenModel->id,
                    ]);
                } else {
                    Log::warning('Token not found for deletion', [
                        'user_id' => $user->id,
                        'token_id' => $tokenId,
                    ]);
                }
            } else {
                Log::warning('Invalid token format', [
                    'user_id' => $user->id,
                ]);
            }
        } else {
            Log::warning('No authorization header found', [
                'user_id' => $user->id,
            ]);
        }

        return $this->successResponse('Logout successful');
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Log profile access for security monitoring
        SecurityLogger::logProfileAccess($request, $user->id);

        return $this->successResponse(
            'Profile retrieved successfully',
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]
        );
    }
}