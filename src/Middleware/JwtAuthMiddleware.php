<?php

declare(strict_types=1);

namespace YogCloud\Framework\Middleware;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\HyperfAuth\AuthMiddleware;
use YogCloud\Framework\Middleware\Traits\Route;

class JwtAuthMiddleware extends AuthMiddleware
{
    use Route;

    /**
     * @var string 路由白名单
     */
    protected $authWhiteRoutes;

    protected $guards = ['jwt'];

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->auth            = $container->get(AuthManager::class); // 父auth莫名无法注入成功
        $this->authWhiteRoutes = $config->get('framework.auth_white_routes', []);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->whiteListAuth($this->authWhiteRoutes)) {
            return $handler->handle($request);
        }
        return parent::process($request, $handler);
    }
}
