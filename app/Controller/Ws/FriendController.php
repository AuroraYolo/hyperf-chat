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

use App\Component\WsProtocol;
use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use App\Model\FriendChatHistory;
use App\Model\UserApplication;
use App\Service\FriendService;
use App\Service\UserService;
use App\Task\FriendTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;

/**
 * Class FriendController
 * @package App\Controller\Ws
 * @Controller(prefix="friend",server="ws")
 */
class FriendController extends AbstractController
{
    /**
     * @RequestMapping(path="send",methods="GET")
     */
    public function sendMessage()
    {
        /**
         * @var WsProtocol $protocol
         */
        $protocol = Context::get('request');
        $data     = $protocol->getData();

        $friendChatHistoryInfo = FriendService::createFriendChatHistory($data['message_id'], $data['from_user_id'], $data['to_id'], $data['content']);

        $userInfo = UserService::findUserInfoById($data['from_user_id']);
        $fd       = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$data['to_id'], 'fd') ?? '';
        $this->container->get(FriendTask::class)->sendMessage(
            $fd,
            $userInfo->username,
            $userInfo->avatar,
            $data['from_user_id'],
            UserApplication::APPLICATION_TYPE_FRIEND,
            $data['content'],
            $data['message_id'],
            false,
            $data['from_user_id'],
            $friendChatHistoryInfo->created_at->getTimestamp() * 1000
        );

        return ['message_id' => $data['message_id'] ?? ''];
    }

    /**
     * @RequestMapping(path="getUnreadMessage",methods="GET")
     */
    public function getUnreadMessage()
    {
        /**
         * @var WsProtocol $request
         */
        $request = Context::get('request');
        $fd      = $request->getFd();

        $userId   = TableManager::get(MemoryTable::FD_TO_USER)->get((string)$fd, 'userId') ?? '';
        $messages = FriendService::getUnreadMessageByToUserId((int)$userId);
        foreach ($messages as $message) {
            $this->container->get(FriendTask::class)->sendMessage(
                $fd,
                $message['username'],
                $message['avatar'],
                $message['from_uid'],
                UserApplication::APPLICATION_TYPE_FRIEND,
                $message['content'],
                $message['message_id'],
                false,
                $message['from_uid'],
                $message['timestamp']);
        }
    }

    /**
     * @RequestMapping(path="read",methods="GET")
     */
    public function read()
    {
        /**
         * @var WsProtocol $request
         */
        $request = Context::get('request');
        $data    = $request->getData();
        FriendService::setFriendChatHistoryReceptionStateByMessageId($data['message_id'], FriendChatHistory::RECEIVED);

        return ['message_id' => $data['message_id'] ?? ''];
    }
}

