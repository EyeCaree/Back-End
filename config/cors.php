<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'register', 'users', 'foto-mata', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5173'], // domain temanmu

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
