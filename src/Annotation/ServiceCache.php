<?php

declare(strict_types=1);

namespace YogCloud\Framework\Annotation;

use Attribute;
use Hyperf\Cache\CacheListenerCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ServiceCache extends AbstractAnnotation
{
    /**
     * @var string
     */
    public string $prefix;

    /**
     * @var string
     */
    public string $value;

    /**
     * @var int
     */
    public int $ttl;

    /**
     * @var string
     */
    public string $listener;

    /**
     * The max offset for ttl.
     * @var int
     */
    public int $offset = 0;

    /**
     * @var string
     */
    public string $group = 'default';

    /**
     * @var bool
     */
    public bool $collect = false;

    public function collectMethod(string $className, ?string $target): void
    {
        if (isset($this->listener)) {
            CacheListenerCollector::setListener($this->listener, [
                'className' => $className,
                'method'    => $target,
            ]);
        }

        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }
}
