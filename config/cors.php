<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for cross-origin resource sharing
    | or "CORS". This determines which cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        // <-- CAMBIO 1: Aseguramos que TODAS las rutas ('*') sean manejadas por CORS.
        // Esto es útil en desarrollo para cubrir rutas como /face-login.
        '*',
        'api/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // <-- CAMBIO 2: Añadimos las URLs del servidor de desarrollo de Vite.
        // Esto le dice a Laravel que confíe en las peticiones que vienen de tu frontend.
        // El puerto por defecto es 5173, ajústalo si el tuyo es diferente.
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // <-- CAMBIO 3: Ponemos esto en 'true' para permitir que el navegador envíe cookies de sesión.

];