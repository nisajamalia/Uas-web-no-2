<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add CORS middleware to handle cross-origin requests
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Add security headers middleware to all API routes
        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        $middleware->alias([
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
        
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'errors' => ['auth' => ['Please log in to access this resource.']],
                    'timestamp' => now()->toISOString(),
                ], 401);
            }
        });

        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                    'timestamp' => now()->toISOString(),
                ], 422);
            }
        });

        // Handle rate limiting exceptions
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'errors' => ['rate_limit' => ['You have exceeded the maximum number of requests.']],
                    'timestamp' => now()->toISOString(),
                ], 429);
                
                // Add retry-after header if available
                if (method_exists($e, 'getHeaders')) {
                    $headers = $e->getHeaders();
                    if (isset($headers['Retry-After'])) {
                        $response->header('Retry-After', $headers['Retry-After']);
                    }
                }
                
                return $response;
            }
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'errors' => ['resource' => ['The requested resource does not exist.']],
                    'timestamp' => now()->toISOString(),
                ], 404);
            }
        });

        // Handle authorization exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                    'errors' => ['authorization' => ['You do not have permission to access this resource.']],
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        });

        // Handle 404 not found exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested endpoint was not found.',
                    'errors' => ['endpoint' => ['The requested API endpoint does not exist.']],
                    'timestamp' => now()->toISOString(),
                ], 404);
            }
        });

        // Handle method not allowed exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed.',
                    'errors' => ['method' => ['The HTTP method is not allowed for this endpoint.']],
                    'timestamp' => now()->toISOString(),
                ], 405);
            }
        });

        // Handle general exceptions in production
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                // Log the error for debugging
                \Illuminate\Support\Facades\Log::error('Unhandled API exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $request->user()?->id,
                ]);

                // Don't leak sensitive information in production
                if (app()->environment('production')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An error occurred while processing your request.',
                        'errors' => ['server' => ['Please try again later or contact support.']],
                        'timestamp' => now()->toISOString(),
                    ], 500);
                } else {
                    // In development, provide more details
                    return response()->json([
                        'success' => false,
                        'message' => 'Server error: ' . $e->getMessage(),
                        'errors' => ['server' => [$e->getMessage()]],
                        'timestamp' => now()->toISOString(),
                        'debug' => [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ],
                    ], 500);
                }
            }
        });
    })->create();
