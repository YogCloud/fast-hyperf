<?php

declare(strict_types=1);

namespace YogCloud\Framework\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ServiceCacheEvict extends AbstractAnnotation
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
     * @var bool
     */
    public $all = false;

    /**
     * @var string
     */
    public $group = 'default';

    /**
     * @var bool
     */
    public $collect = false;
}
