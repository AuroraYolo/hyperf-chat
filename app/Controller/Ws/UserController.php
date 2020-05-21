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

use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use App\Service\UserService;
use App\Task\UserTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;
use App\Component\WsProtocol;

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

    /**
     * @RequestMapping(path="getUnreadApplicationCount",methods="GET")
     */
    public function getUnreadApplicationCount()
    {
        /**
         * @var WsProtocol $protocol
         */
        $protocol = Context::get('request');
        $userId   = TableManager::get(MemoryTable::FD_TO_USER)->get((string)$protocol->getFd(), 'userId') ?? '';
        $count    = UserService::getUnreadApplicationCount($userId);

        $this->container->get(UserTask::class)->unReadApplicationCount($protocol->getFd(), $count);
    }
}
