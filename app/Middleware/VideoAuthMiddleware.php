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

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\WsHandshakeException;
use App\Model\User;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Hyperf\WebSocketServer\Security;
use Phper666\JWTAuth\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VideoAuthMiddleware implements MiddlewareInterface
{
    private const HANDLE_SUCCESS_CODE = 101;

    private const HANDLE_FAIL_CODE = 401;

    private const HANDLE_BAD_REQUEST_CODE = 400;

    protected $jwt;

    public function __construct(Jwt $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * auth认证的中间件
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws  \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $isValidToken = false;
        /** @var Response $response */
        $response = Context::get(ResponseInterface::class);
        /**
         * @var \Hyperf\HttpMessage\Server\Request $request
         */
        $request   = Context::get(ServerRequestInterface::class);
        $container = ApplicationContext::getContainer();
        $key       = $request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
        $token     = $request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL);
        try {
            if (strlen($token) > 0 && $this->jwt->checkToken($token)) {
                $isValidToken = true;
            }
        } catch (Throwable $e) {
            return $response
                ->withStatus(self::HANDLE_BAD_REQUEST_CODE);
        }
        if ($isValidToken) {
            $jwtData = $this->jwt->getParserData($token);
            $user    = User::query()->where(['id' => $jwtData['uid']])->first();
            if (empty($user)) {
                throw new WsHandshakeException(ErrorCode::USER_NOT_FOUND);
            }
            WsContext::set('user', $user);
            $uri        = $request->getUri();
            $dispatcher = $container
                ->get(DispatcherFactory::class)
                ->getDispatcher('ws');
            $routes     = $dispatcher->dispatch($request->getMethod(), $uri->getPath());
            $controller = $routes[1]->callback;
            $security   = $container->get(Security::class);
            $headers    = $security->handShakeHeaders($key);
            $swResponse = $response->getSwooleResponse();
            foreach ($headers as $key => $value) {
                $swResponse->header($key, $value);
            }
            $swResponse->header(Security::SEC_WEBSOCKET_PROTOCOL, $token);
            return $response
                ->withStatus(self::HANDLE_SUCCESS_CODE)
                ->setSwooleResponse($swResponse)
                ->withAttribute('class', $controller);
        }
        return $response
            ->withStatus(self::HANDLE_FAIL_CODE);
    }

}
