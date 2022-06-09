<?php

declare(strict_types=1);

namespace YogCloud\Framework\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Throwable;
use YogCloud\Framework\Constants\ErrorCode;
use YogCloud\Framework\Exception\CommonException;

/**
 * General error message returned.
 */
class CommonExceptionHandler extends ExceptionHandler
{
    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Throwable $throwable, \Psr\Http\Message\ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        // format
        $data     = responseDataFormat($throwable->getCode(), $throwable->getMessage());
        $httpCode = ErrorCode::getHttpCode($data['code']);
        if (! $httpCode && class_exists(\App\Constants\AppErrCode::class)) {
            $httpCode = \App\Constants\AppErrCode::getHttpCode($data['code']);
        }
        $dataStream = new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE));

        // Stop exception bubbling
        $this->stopPropagation();
        return $response->withHeader('Server', 'Hyperf')
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($httpCode)
            ->withBody($dataStream);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof CommonException;
    }
}
