<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a success response
     */
    protected function successResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(string $message, array $errors, int $statusCode = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Return an authentication error response
     */
    protected function authenticationErrorResponse(string $message = 'Authentication required'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['auth' => ['Please log in to access this resource.']],
            'timestamp' => now()->toISOString(),
        ], 401);
    }

    /**
     * Return an authorization error response
     */
    protected function authorizationErrorResponse(string $message = 'Access denied'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['authorization' => ['You do not have permission to access this resource.']],
            'timestamp' => now()->toISOString(),
        ], 403);
    }

    /**
     * Return a not found error response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['resource' => ['The requested resource was not found.']],
            'timestamp' => now()->toISOString(),
        ], 404);
    }

    /**
     * Return a rate limit error response
     */
    protected function rateLimitResponse(string $message = 'Too many requests'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['rate_limit' => ['You have exceeded the maximum number of requests. Please try again later.']],
            'timestamp' => now()->toISOString(),
        ], 429);
    }

    /**
     * Return a server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => ['server' => ['An error occurred while processing your request. Please try again later.']],
            'timestamp' => now()->toISOString(),
        ], 500);
    }
}