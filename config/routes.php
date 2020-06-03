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

use App\Middleware\AuthMiddleware;
use App\Middleware\VideoAuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\Http\IndexController@login');
Router::addServer('ws', function ()
{
    Router::get('/ws', 'App\Controller\Ws\WebSocketController', [
        'middleware' => [AuthMiddleware::class]
    ]);
    Router::get('/video', 'App\Controller\Ws\VideoController', [
        'middleware' => [VideoAuthMiddleware::class]
    ]);
});

