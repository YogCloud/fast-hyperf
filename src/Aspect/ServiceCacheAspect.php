<?php

declare(strict_types=1);

namespace YogCloud\Framework\Aspect;

use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Cache\Exception\CacheException;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Codec\Json;
use YogCloud\Framework\Annotation\ServiceCache;

/**
 * @Aspect
 */
#[Aspect]
class ServiceCacheAspect extends AbstractAspect
{
    public array $annotations = [
        ServiceCache::class,
    ];

    /**
     * @var CacheManager
     */
    protected CacheManager $manager;

    /**
     * @var AnnotationManager
     */
    protected AnnotationManager $annotationManager;

    public function __construct(CacheManager $manager, AnnotationManager $annotationManager)
    {
        $this->manager           = $manager;
        $this->annotationManager = $annotationManager;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method    = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        $argv = ':' . 'id' . ':' . $arguments['id'] . ':' . 'columns' . Json::encode($arguments['columns']);

        $key = str_replace('\\', '', $className) . ':' . $method . $argv;

        /** @var ServiceCache $annotation */
        $annotation = $this->getAnnotation(ServiceCache::class, $className, $method);

        $driver = $this->manager->getDriver();

        [$has, $result] = $driver->fetch($key);

        if ($has) {
            return $result;
        }

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, 9000);
        if ($driver instanceof KeyCollectorInterface && $annotation instanceof ServiceCache && $annotation->collect) {
            $driver->addKey($annotation->prefix . 'MEMBERS', $key);
        }

        return $result;
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result    = $collector['_m'][$method][$annotation] ?? null;
        if (! $result instanceof $annotation) {
            throw new CacheException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }
}
