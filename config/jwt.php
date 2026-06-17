<?php

return [
    'algo' => env('JWT_ALGO', 'HS256'),
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'access_ttl' => (int) env('JWT_ACCESS_TTL', 3600),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 1209600),
    'api_key_header' => env('API_KEY_HEADER', 'X-API-KEY'),
    'api_key' => env('API_KEY'),
];
