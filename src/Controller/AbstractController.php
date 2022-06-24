<?php

declare(strict_types=1);

namespace YogCloud\Framework\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     * @Inject
     */
    #[Inject]
    protected RequestInterface $request;

    /**
     * @Inject
     */
    #[Inject]
    protected ResponseInterface $response;
}
