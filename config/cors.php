<?php

return [
    'supports_credentials' => true,
    'allowed_origins' => ['http://localhost:3000'], // Allow your frontend URL
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'allowed_methods' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'hosts' => [],
];

