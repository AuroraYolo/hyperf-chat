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
use App\Model\User;
use App\Service\UserService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController extends AbstractController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage(WebSocketServer $server, Frame $frame) : void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId) : void
    {
        var_dump('closed');
    }

    public function onOpen(WebSocketServer $server, Request $request) : void
    {
        /**
         * @var \App\Model\User $user
         */
        $user        = Context::get('user');
        $checkOnline = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$user->id, 'fd');
        if ($checkOnline) {
            \App\Component\Server::disconnect($request->fd, 0, '你的帐号在别的地方登录!');
        }

        TableManager::get(MemoryTable::FD_TO_USER)->set((string)$request->fd, ['userId' => $user->id]);
        TableManager::get(MemoryTable::USER_TO_FD)->set((string)$user->id, ['fd' => $request->fd]);

        //TODO 保存用户状态
        UserService::setUserStatus($user->id, User::STATUS_ONLINE);
    }
}
