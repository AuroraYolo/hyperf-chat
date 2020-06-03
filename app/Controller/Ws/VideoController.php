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

use App\Component\MessageParser;
use App\Component\WsProtocol;
use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Hyperf\WebSocketServer\Sender;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class VideoController extends AbstractController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject()
     * @var Sender
     */
    private $sender;
    /**
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Websocket\Frame  $frame
     */

    public function onMessage(WebSocketServer $server, Frame $frame) : void
    {
        //处理消息
        $message = MessageParser::decode($frame->data);
        Context::set('request', new WsProtocol(
            $message['data'],
            $message['ext'],
            $frame->fd,
            $server->getClientInfo($frame->fd)['last_time'] ?? 0
        ));
        $dispatcher = $this->container
            ->get(DispatcherFactory::class)
            ->getDispatcher('ws');
        $controller = explode('.', $message['cmd'])[0] ?? '';
        $method     = explode('.', $message['cmd'])[1] ?? '';
        $dispatched = make(Dispatched::class, [
            $dispatcher->dispatch('GET', sprintf('/%s/%s', $controller, $method))
        ]);
        if ($dispatched->isFound()) {
            //路由处理
            $result = call_user_func([
                make($dispatched->handler->callback[0]),
                $dispatched->handler->callback[1],
            ]);
            if ($result !== NULL) {
                $receive = [
                    'cmd'  => $message['cmd'],
                    'data' => $result,
                    'ext'  => []
                ];
                $this->sender->push($frame->fd, MessageParser::encode($receive));
            }
        }
    }

    /**
     * @param \Swoole\Server $server
     * @param int            $fd
     * @param int            $reactorId
     */
    public function onClose(Server $server, int $fd, int $reactorId) : void
    {
        $userId  = TableManager::get(MemoryTable::SUBJECT_FD_TO_USER)->get((string)$fd, 'userId');
        $selfFd  = TableManager::get(MemoryTable::SUBJECT_USER_TO_FD)->get((string)$userId, 'fd');
        $subject = TableManager::get(MemoryTable::USER_TO_SUBJECT)->get((string)$userId, 'subject');
        if ($fd == $selfFd) {
            TableManager::get(MemoryTable::SUBJECT_USER_TO_FD)->del((string)$userId);
        }
        TableManager::get(MemoryTable::SUBJECT_FD_TO_USER)->del((string)$fd);
        TableManager::get(MemoryTable::USER_TO_SUBJECT)->del((string)$userId);
        TableManager::get(MemoryTable::SUBJECT_TO_USER)->del((string)$subject);
        WsContext::destroy('user');
    }

    /**
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request     $request
     */
    public function onOpen(WebSocketServer $server, Request $request) : void
    {
        /**
         * @var \App\Model\User $user
         */
        $user = WsContext::get('user');
        TableManager::get(MemoryTable::SUBJECT_FD_TO_USER)->set((string)$request->fd, ['userId' => $user->id]);
        TableManager::get(MemoryTable::SUBJECT_USER_TO_FD)->set((string)$user->id, ['fd' => $request->fd]);
    }
}

