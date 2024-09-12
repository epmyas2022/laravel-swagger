<?php

use Illuminate\Support\Facades\App;

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$port = $_SERVER['SERVER_PORT'] ?? env('APP_PORT', '8000');

// Verificamos si el puerto ya está en el host (en muchos casos cuando no es el puerto por defecto)
if (strpos($host, ':') === false && $port != 80 && $port != 443) {
    $host .= ":{$port}";
}

//Config file for swagger
return [
    'openapi' => '3.0.0',
    'info' => [
        'title' => env('APP_NAME', 'API'),
        'version' => env('APP_VERSION', '1.0.0'),
        'description' => env('APP_DESCRIPTION', 'API Documentation'),
        'contact' => [
            'email' => 'castillo20182017@gmail.com',
        ],
    ],
    'servers' => [
        ['url' => "{$scheme}://{$host}", 'description' => env('APP_ENV', 'default')],
    ],
    "components" =>  [
        "securitySchemes" =>  [
            "bearerAuth" =>  [
                "type" =>  "http",
                "scheme" =>  "bearer",
                "bearerFormat" =>  "JWT"
            ],
            'basicAuth' => [
                'type' => 'http',
                'scheme' => 'basic'
            ]
        ]
    ],
    'paths' => [],

];
