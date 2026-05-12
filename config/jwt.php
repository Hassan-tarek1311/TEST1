<?php

return [
    // مفتاح التشفير — بيتعمل بـ: php artisan jwt:secret
    'secret' => env('JWT_SECRET'),

    'algo' => env('JWT_ALGO', 'HS256'),

    // مدة صلاحية الـ Token — 1440 دقيقة = 24 ساعة
    'ttl' => env('JWT_TTL', 1440),

    // مدة السماح بـ refresh — 20160 دقيقة = 14 يوم
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    'leeway' => env('JWT_LEEWAY', 0),

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    'decrypt_cookies' => false,

    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
