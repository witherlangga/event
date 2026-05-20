<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    // token ttl in seconds
    'ttl' => env('JWT_TTL', 3600),
];
