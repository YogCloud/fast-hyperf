<?php

declare(strict_types=1);

namespace YogCloud\Framework\Lock\Exception;

use Throwable;

class LockTimeoutException extends \RuntimeException
{
    public function __construct($message = '当前锁已经被占用', $code = 0, Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
