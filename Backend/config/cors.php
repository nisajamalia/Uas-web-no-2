<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://localhost:3000', // Default frontend port
        'http://localhost:3001', // Alternative frontend port
        'http://localhost:3002', // Alternative frontend port
        'http://localhost:5173', // Vite dev server
        'http://localhost:4173', // Vite preview server
        'https://app.kampus.ac.id', // Production frontend domain
    ],

    'allowed_origins_patterns' => [
        // Allow subdomains of kampus.ac.id in production
        '/^https:\/\/.*\.kampus\.ac\.id$/',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

];
