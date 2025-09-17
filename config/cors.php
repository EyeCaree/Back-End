<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'register', 'users', 'foto-mata', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],
    // [
    // 'http://localhost:5173',
    // 'http://192.168.1.136:3000',
    // 'http://794a-36-66-204-109.ngrok-free.app'
    // ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
