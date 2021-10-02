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
 * guzzle请求异常.
 */
class GuzzleRequestExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function handle(Throwable $throwable, \Psr\Http\Message\ResponseInterface $response)
    {
        if ($throwable->getResponse()) {
            $rawResponse = \GuzzleHttp\Psr7\get_message_body_summary($throwable->getResponse());
            $rawResData  = json_decode($rawResponse, true);
        } else {
            $rawResData['msg'] = $throwable->getMessage();
        }
        ## 记录日志
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        ## 格式化输出
        $falseMsg   = isset($rawResData['msg']) ? $rawResData['msg'] : '请求错误';
        $falseMsg   = ErrorCode::getMessage(ErrorCode::THIRD_API_ERROR) . $falseMsg;
        $data       = responseDataFormat(ErrorCode::THIRD_API_ERROR, $falseMsg);
        $httpCode   = ErrorCode::getHttpCode(ErrorCode::THIRD_API_ERROR);
        $dataStream = new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE));

        ## 阻止异常冒泡
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
