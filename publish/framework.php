<?php

declare(strict_types=1);
return [
    // YogCloud\Framework\Middleware\JwtAuthMiddleware jwt路由验证白名单
    'auth_white_routes' => [
    ],

    // YogCloud\Framework\Middleware\ResponseMiddleware 原生响应格式的路由
    'response_raw_routes' => [
    ],

    'wework' => [
        'config' => [
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
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
