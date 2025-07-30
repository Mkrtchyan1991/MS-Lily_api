<?php
// config/cors.php - Updated for Sanctum SPA authentication

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */
    
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'email/verify/*'
    ],

    'allowed_methods' => ['*'], // Allow all HTTP methods

    'allowed_origins' => [
        'http://localhost:5173',  // Vite default port
        'http://localhost:3000',  // Common React port
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'https://front.koyeb.app', // Your production domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        '*' // Allow all headers for simplicity, or specify:
        // 'Content-Type',
        // 'X-Requested-With',
        // 'Authorization',
        // 'X-CSRF-TOKEN',
        // 'X-XSRF-TOKEN',
        // 'Accept',
        // 'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // CRITICAL: This enables cookie-based auth
];