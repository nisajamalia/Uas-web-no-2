<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiErrorHandler
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request and catch any exceptions
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            return $this->handleException($exception, $request);
        }
    }

    /**
     * Handle different types of exceptions
     */
    private function handleException(Throwable $exception, Request $request): Response
    {
        // Log the exception for debugging (but not in production for security)
        if (!app()->environment('production')) {
            Log::error('API Exception', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Handle specific exception types
        switch (true) {
            case $exception instanceof ValidationException:
                return $this->handleValidationException($exception, $request);
                
            case $exception instanceof AuthenticationException:
                return $this->handleAuthenticationException($exception, $request);
                
            case $exception instanceof AccessDeniedHttpException:
                return $this->handleAuthorizationException($exception, $request);
                
            case $exception instanceof ThrottleRequestsException:
                return $this->handleThrottleException($exception, $request);
                
            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundHttpException:
                return $this->handleNotFoundException($exception, $request);
                
            default:
                return $this->handleGenericException($exception, $request);
        }
    }

    /**
     * Handle validation exceptions
     */
    private function handleValidationException(ValidationException $exception, Request $request): Response
    {
        Log::info('Validation error', [
            'errors' => $exception->errors(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        return $this->validationErrorResponse(
            'The given data was invalid.',
            $exception->errors()
        );
    }

    /**
     * Handle authentication exceptions
     */
    private function handleAuthenticationException(AuthenticationException $exception, Request $request): Response
    {
        Log::warning('Authentication error', [
            'message' => $exception->getMessage(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->authenticationErrorResponse('Authentication required.');
    }

    /**
     * Handle authorization exceptions
     */
    private function handleAuthorizationException(AccessDeniedHttpException $exception, Request $request): Response
    {
        Log::warning('Authorization error', [
            'message' => $exception->getMessage(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        return $this->authorizationErrorResponse('Access denied.');
    }

    /**
     * Handle throttle exceptions
     */
    private function handleThrottleException(ThrottleRequestsException $exception, Request $request): Response
    {
        Log::warning('Rate limit exceeded', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ]);

        $response = $this->rateLimitResponse('Too many requests. Please try again later.');
        
        // Add retry-after header if available
        if (method_exists($exception, 'getHeaders')) {
            $headers = $exception->getHeaders();
            if (isset($headers['Retry-After'])) {
                $response->header('Retry-After', $headers['Retry-After']);
            }
        }

        return $response;
    }

    /**
     * Handle not found exceptions
     */
    private function handleNotFoundException(Throwable $exception, Request $request): Response
    {
        Log::info('Resource not found', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        return $this->notFoundResponse('The requested resource was not found.');
    }

    /**
     * Handle generic exceptions
     */
    private function handleGenericException(Throwable $exception, Request $request): Response
    {
        // Log security-related errors
        Log::error('Unhandled exception', [
            'exception' => get_class($exception),
            'message' => app()->environment('production') ? 'Internal server error' : $exception->getMessage(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ]);

        // In production, don't leak sensitive information
        if (app()->environment('production')) {
            return $this->serverErrorResponse('An error occurred while processing your request.');
        }

        // In development, provide more details
        return $this->serverErrorResponse($exception->getMessage());
    }
}