<?php

declare(strict_types=1);

namespace YogCloud\Framework\Exception\Handler;

use GuzzleHttp\Exception\RequestException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Throwable;
use YogCloud\Framework\Constants\ErrorCode;

/**
 * guzzle request error.
 */
class GuzzleRequestExceptionHandler extends ExceptionHandler
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
        if ($throwable->getResponse()) {
            $rawResponse = \GuzzleHttp\Psr7\get_message_body_summary($throwable->getResponse());
            $rawResData  = json_decode($rawResponse, true);
        } else {
            $rawResData['msg'] = $throwable->getMessage();
        }

        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        // format
        $falseMsg   = isset($rawResData['msg']) ? $rawResData['msg'] : 'RequestError';
        $falseMsg   = ErrorCode::getMessage(ErrorCode::THIRD_API_ERROR) . $falseMsg;
        $data       = responseDataFormat(ErrorCode::THIRD_API_ERROR, $falseMsg);
        $httpCode   = ErrorCode::getHttpCode(ErrorCode::THIRD_API_ERROR);
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
        return $throwable instanceof RequestException;
    }
}
