<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize input data to prevent XSS and injection attacks
        $this->sanitizeInput($request);

        $response = $next($request);

        // Add security headers to the response
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Only add HSTS in production with HTTPS
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Sanitize input data to prevent injection attacks
     */
    private function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        if (!empty($input)) {
            $sanitized = $this->recursiveSanitize($input);
            $request->replace($sanitized);
        }
    }

    /**
     * Recursively sanitize array data
     */
    private function recursiveSanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->recursiveSanitize($value);
            } elseif (is_string($value)) {
                // Remove null bytes and control characters except newlines and tabs
                $data[$key] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                
                // Trim whitespace
                $data[$key] = trim($data[$key]);
            }
        }
        
        return $data;
    }
}