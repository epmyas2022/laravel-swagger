<?php

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
        ['url' => 'http://localhost:8000'],
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
