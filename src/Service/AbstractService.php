<?php

declare(strict_types=1);

namespace YogCloud\Framework\Service;

use Hyperf\Database\Model\Model;

abstract class AbstractService
{
    /**
     * @var Model
     */
    protected $model;

    public function __construct()
    {
        $modelClass  = str_replace(['\Service', 'Service'], ['\Model', ''], get_class($this));
        $this->model = make($modelClass);
    }
}
