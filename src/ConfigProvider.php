<?php

declare(strict_types=1);

namespace YogCloud\Framework;

use YogCloud\Framework\Exception\Handler\AuthExceptionHandler;
use YogCloud\Framework\Exception\Handler\CommonExceptionHandler;
use YogCloud\Framework\Exception\Handler\GuzzleRequestExceptionHandler;
use YogCloud\Framework\Exception\Handler\ValidationExceptionHandler;
use YogCloud\Framework\Middleware\CorsMiddleware;
use YogCloud\Framework\Middleware\ResponseMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        $serviceMap = $this->serviceMap();

        return [
            'dependencies' => array_merge($serviceMap, [
            ]),
            'exceptions' => [
                'handler' => [
                    'http' => [
                        CommonExceptionHandler::class,
                        GuzzleRequestExceptionHandler::class,
                        ValidationExceptionHandler::class,
                        AuthExceptionHandler::class,
                    ],
                ],
            ],
            'middlewares' => [
                'http' => [
                    CorsMiddleware::class,
                    ResponseMiddleware::class,
                ],
            ],
            'commands' => [
            ],
            'listeners' => [
                \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id'          => 'framework',
                    'description' => 'framework configuration',
                    'source'      => __DIR__ . '/../publish/framework.php',
                    'destination' => BASE_PATH . '/config/autoload/framework.php',
                ],
                [
                    'id'          => 'dependencies',
                    'description' => 'Dependent configuration',
                    'source'      => __DIR__ . '/../publish/dependencies.php',
                    'destination' => BASE_PATH . '/config/autoload/dependencies.php',
                ],
            ],
        ];
    }

    /**
     * Dependency configuration of model services and contracts.
     * @param string $path Relative path between contract and service
     * @return array Dependent data
     */
    protected function serviceMap(string $path = 'app'): array
    {
        $services    = readFileName(BASE_PATH . '/' . $path . '/Service');
        $spacePrefix = ucfirst($path);

        $dependencies = [];
        foreach ($services as $service) {
            $dependencies[$spacePrefix . '\\Contract\\' . $service . 'Interface'] = $spacePrefix . '\\Service\\' . $service;
        }

        return $dependencies;
    }
}
