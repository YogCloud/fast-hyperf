<?php

declare(strict_types=1);

namespace YogCloud\Framework\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ServiceCacheEvict extends AbstractAnnotation
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
     * @var bool
     */
    public bool $all = false;

    /**
     * @var string
     */
    public string $group = 'default';

    /**
     * @var bool
     */
    public bool $collect = false;
}
