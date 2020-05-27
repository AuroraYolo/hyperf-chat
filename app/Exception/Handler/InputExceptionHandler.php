<?php
declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use App\Component\Log\RuntimeLog;
use App\Exception\InputException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class InputExceptionHandler extends ExceptionHandler
{
    /**
     * @param \Throwable                          $throwable
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof InputException) {
            RuntimeLog::error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            RuntimeLog::error($throwable->getTraceAsString());
            $data = Json::encode([
                'code' => -1,
                'msg'  => $throwable->getMessage(),
                'data' => NULL
            ], JSON_UNESCAPED_UNICODE);
            $this->stopPropagation();
            return $response->withStatus(200)
                            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
                            ->withBody(new SwooleStream($data));
        }
        return $response;
    }

    public function isValid(Throwable $throwable) : bool
    {
        return true;
    }
}
