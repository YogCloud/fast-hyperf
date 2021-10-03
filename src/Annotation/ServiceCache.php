<?php

declare(strict_types=1);

namespace YogCloud\Framework\Annotation;

use Hyperf\Cache\CacheListenerCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ServiceCache extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var string
     */
    public $listener;

    /**
     * The max offset for ttl.
     * @var int
     */
    public $offset = 0;

    /**
     * @var string
     */
    public $group = 'default';

    /**
     * @var bool
     */
    public $collect = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->ttl    = (int) $this->ttl;
        $this->offset = (int) $this->offset;
    }

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
