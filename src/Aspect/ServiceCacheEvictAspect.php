<?php

declare(strict_types=1);

namespace YogCloud\Framework\Aspect;

use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Exception\CacheException;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use YogCloud\Framework\Annotation\ServiceCacheEvict;

/**
 * @Aspect
 */
#[Aspect]
class ServiceCacheEvictAspect extends AbstractAspect
{
    public $annotations = [
        ServiceCacheEvict::class,
    ];

    /**
     * @var CacheManager
     */
    protected $manager;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

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

        $method = str_replace('delete', 'get', $method) . 'ById';

        $driver = $this->manager->getDriver();
        $key    = str_replace('\\', '', $className) . ':' . $method . ':' . 'id' . ':' . $arguments['id'] . ':';
        $driver->clearPrefix($key);

        return $proceedingJoinPoint->process();
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
