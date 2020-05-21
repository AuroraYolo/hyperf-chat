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

namespace App\Controller\Ws;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class UserController
 * @package App\Controller\Ws
 * @Controller(prefix="user",server="ws")
 */
class UserController extends AbstractController
{
    /**
     * @RequestMapping(path="ping",methods="GET")
     * @return int
     */
    public function index()
    {
        return WEBSOCKET_OPCODE_PONG;
    }

    public function getUnreadApplicationCount()
    {

    }
}
