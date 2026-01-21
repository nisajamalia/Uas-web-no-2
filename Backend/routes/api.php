<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'SAKTI Mini API is running',
        'data' => [
            'status' => 'ok',
            'version' => '1.0.0',
            'environment' => app()->environment(),
        ],
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication endpoints
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:60,1'); // 60 requests per minute for development

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:60,1'); // 60 requests per minute for development

Route::middleware(['auth:sanctum', 'throttle:auth'])->group(function () {
    // Logout endpoint (requires authentication)
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile endpoint (requires authentication)
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Legacy user endpoint (keeping for backward compatibility)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Mahasiswa endpoints (public for now, can be protected later)
Route::apiResource('mahasiswa', MahasiswaController::class);