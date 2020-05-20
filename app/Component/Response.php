<?php
declare(strict_types = 1);
namespace App\Component;

use App\Constants\ErrorCode;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\HttpServer\Response as HyperfResponse;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response extends HyperfResponse
{

    /**
     * @param null   $data
     * @param int    $code
     * @param string $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function success($data = NULL, int $code = 0, string $message = 'success')
    {
        $result = [
            'code'    => $code,
            'msg' => $message,
            'data'    => $data
        ];
        return $this->json($result);
    }

    /**
     *
     * @param int    $code
     * @param string $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function error(int $code = -1, string $message = '')
    {
        $code   = ($code == 0) ? -1 : $code;
        $msg    = ErrorCode::$errorMessages[$code] ?? $message;
        $result = [
            'code'    => $code,
            'msg' => $msg,
        ];
        return $this->json($result);
    }

    /**
     * @param array|\Hyperf\Utils\Contracts\Arrayable|\Hyperf\Utils\Contracts\Jsonable $result
     *
     * @param int                                                                      $statusCode
     *
     * @param int                                                                      $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function json($result, int $statusCode = 200, $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : PsrResponseInterface
    {
        $data = $this->toJson($result);
        return $this->getResponse()
                    ->withStatus($statusCode)
                    ->withAddedHeader('content-type', 'application/json; charset=utf-8')
                    ->withBody(new SwooleStream($data));
    }

    /**
     * @param array|\Hyperf\Utils\Contracts\Arrayable|\Hyperf\Utils\Contracts\Jsonable $data
     * @param int                                                                      $options
     *
     * @return string
     */
    protected function toJson($data, $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : string
    {
        try {
            $result = Json::encode($data, $options);
        } catch (\Throwable $exception) {
            throw new EncodingException($exception->getMessage(), $exception->getCode());
        }

        return $result;
    }

    /**
     * @param string $xml
     * @param int    $statusCode
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toWechatXML(string $xml, int $statusCode = 200) : PsrResponseInterface
    {
        return $this->getResponse()
                    ->withStatus($statusCode)
                    ->withAddedHeader('content-type', 'application/xml; charset=utf-8')
                    ->withBody(new SwooleStream($xml));
    }
}
