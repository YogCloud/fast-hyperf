<?php

declare(strict_types=1);

namespace YogCloud\Framework\Middleware\Traits;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\ApplicationContext;
use YogCloud\Framework\Constants\ErrorCode;
use YogCloud\Framework\Exception\CommonException;

trait Route
{
    protected function formatRoute(): string
    {
        $request    = ApplicationContext::getContainer()->get(RequestInterface::class);
        $dispatched = $request->getAttribute(Dispatched::class);
        $dynRoute   = $request->getUri()->getPath();

        switch ($dispatched->status) {
            case Dispatcher::NOT_FOUND:
                throw new CommonException(ErrorCode::URI_NOT_FOUND);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new CommonException(ErrorCode::INVALID_HTTP_METHOD);
            case Dispatcher::FOUND:
                $dynRoute = $dispatched->handler->route;
                if (strpos($dynRoute, '{') === false) {
                    break;
                }
                $dynRoute = preg_replace('/:.*?}($|\/)/', '}/', $dispatched->handler->route);
                $dynRoute = rtrim($dynRoute, '/');
        }

        return $dynRoute;
    }

    protected function whiteListAuth(array $whiteList = []): bool
    {
        if (empty($whiteList)) {
            return false;
        }

        $route = $this->formatRoute();
        if (in_array($route, $whiteList)) {
            return true;
        }

        return false;
    }
}
