<?php
// config/cors.php - Updated for Bearer token authentication

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
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
        'https://ms-lily-admin.koyeb.app', // Your production domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        '*' // Allow all headers including Authorization
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // Changed to false for Bearer token auth
];