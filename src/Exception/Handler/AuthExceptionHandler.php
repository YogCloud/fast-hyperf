<?php

declare(strict_types=1);

namespace YogCloud\Framework\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Qbhy\HyperfAuth\Exception\AuthException;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\JWTException;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Throwable;
use YogCloud\Framework\Constants\ErrorCode;

class AuthExceptionHandler extends ExceptionHandler
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
        $code = ErrorCode::AUTH_FAILED;

        // format
        $throwable instanceof UnauthorizedException && $code                = ErrorCode::AUTH_UNAUTHORIZED;
        $throwable->getPrevious() instanceof TokenExpiredException && $code = ErrorCode::AUTH_SESSION_EXPIRED;
        $throwable->getPrevious() instanceof InvalidTokenException && $code = ErrorCode::AUTH_TOKEN_INVALID;

        $falseMsg = ErrorCode::getMessage($code);
        $httpCode = ErrorCode::getHttpCode($code);

        $data       = responseDataFormat($code, $falseMsg);
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
        return $throwable instanceof AuthException || $throwable instanceof JWTException;
    }
}
