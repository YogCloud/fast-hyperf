<?php

declare(strict_types=1);
use Hyperf\Contract\StdoutLoggerInterface;
use YogCloud\Framework\Log\StdoutLoggerFactory;

$dependencies = [];

$appEnv = env('APP_ENV', 'production');
if ($appEnv !== 'dev') {
    $dependencies[StdoutLoggerInterface::class] = StdoutLoggerFactory::class;
}

return $dependencies + [
];
