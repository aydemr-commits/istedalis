<?php

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => filter_var(env('SESSION_EXPIRE_ON_CLOSE', false), FILTER_VALIDATE_BOOL),
    'encrypt' => filter_var(env('SESSION_ENCRYPT', false), FILTER_VALIDATE_BOOL),
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'iste_dalis_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOL),
    'http_only' => true,
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
];
