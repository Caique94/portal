<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração segura de CORS para a aplicação
    |
    */

    // Paths que aceitam CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Métodos HTTP permitidos
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Origens permitidas
    // ⚠️ NUNCA use '*' em produção!
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('APP_URL', 'http://localhost:8000'),
    ],

    // Padrões de origens permitidas (regex)
    'allowed_origins_patterns' => [],

    // Headers permitidos nas requisições
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-CSRF-TOKEN',
    ],

    // Headers expostos nas respostas
    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    // Tempo de cache para preflight (OPTIONS)
    'max_age' => 3600,

    // Permitir credenciais (cookies, auth headers)
    'supports_credentials' => true,

];
