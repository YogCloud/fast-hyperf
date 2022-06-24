<?php

declare(strict_types=1);
return [
    // YogCloud\Framework\Middleware\JwtAuthMiddleware JWT route verification white list
    'auth_white_routes' => [
    ],

    // YogCloud\Framework\Middleware\ResponseMiddleware Routing in native response format
    'response_raw_routes' => [
    ],

    'wework' => [
        'config' => [
            // Specifies the type of result returned by the API call：array(default)/collection/object/raw/Custom class name
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file'  => BASE_PATH . '/runtime/logs/wechat.log',
            ],
        ],
        'default' => [
        ],
    ],
];
