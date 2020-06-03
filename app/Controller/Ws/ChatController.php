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
use App\Constants\ErrorCode;
use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Service\UserService;
use App\Task\VideoTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Sender;

/**
 * Class ChatController
 * @package App\Controller\Ws
 * @Controller(prefix="video",server="ws")
 */
class ChatController extends AbstractController
{
    /**
     * @RequestMapping(path="friendBusy",methods="GET")
     */
    public function friendBusy()
    {
        /**
         * @var WsProtocol $protocol
         */
        $protocol    = Context::get('request');
        $data        = $protocol->getData();
        $selfUserID  = TableManager::get(MemoryTable::FD_TO_USER)->get((string)$protocol->getFd(), 'userId') ?? '';
        $selfSubject = TableManager::get(MemoryTable::USER_TO_SUBJECT)->get((string)$selfUserID, 'subject');
        if ($selfSubject) {
            throw new ApiException(ErrorCode::USER_IN_VIDEO_CALL);
        }
        $toFd      = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$data['to_user_id'], 'fd') ?? '';
        $toSubject = TableManager::get(MemoryTable::USER_TO_SUBJECT)->get((string)$data['to_user_id'], 'subject') ?? '';
        if ($toSubject) {
            throw new ApiException(ErrorCode::FRIEND_CALL_IN_PROGRESS);
        }
        /**
         * @var \App\Model\User $selfUserInfo
         */
        $selfUserInfo = UserService::findUserInfoById((int)$selfUserID);
        /**
         * @var \App\Model\User $toUserInfo
         */
        $toUserInfo = UserService::findUserInfoById((int)$data['to_user_id']);
        $this->container->get(VideoTask::class)->createFriendVideo((int)$selfUserID,
            (int)$protocol->getFd(),
            (int)$toFd,
            (string)$selfUserInfo->username,
            (string)$toUserInfo->username);
    }

    /**
     * @RequestMapping(path="friendSubscribe",methods="GET")
     */
    public function friendSubscribe()
    {

        /**
         * @var WsProtocol $protocol
         */
        $protocol = Context::get('request');
        $data     = $protocol->getData();
        $fd       = $protocol->getFd();
        $subject  = $data['subject'];
        $userId   = TableManager::get(MemoryTable::SUBJECT_FD_TO_USER)->get((string)$fd, 'userId');
        $userIds  = TableManager::get(MemoryTable::SUBJECT_TO_USER)->get((string)$subject, 'userId') ?? [];
        if ($userIds) {
            $userIds = explode(',', (string)$userIds);
            array_push($userIds, $userId);
        } else {
            $userIds = [$userId];
        }
        TableManager::get(MemoryTable::USER_TO_SUBJECT)->set((string)$fd, ['subject' => $subject]);
        TableManager::get(MemoryTable::SUBJECT_TO_USER)->set((string)$subject, ['userId' => implode(',', $userIds)]);

        $sender = make(Sender::class);
        if (count($userIds) == 2) {
            foreach ($userIds as $user_id) {
                $toFd = TableManager::get(MemoryTable::SUBJECT_USER_TO_FD)->get((string)$user_id, 'fd');
                $sender->push((int)$toFd, MessageParser::encode([
                    'event' => 'accept'
                ]));
            }
        }
    }

    /**
     * @RequestMapping(path="friendPublish",methods="GET")
     */
    public function friendPublish()
    {
        /**
         * @var WsProtocol $protocol
         */
        $protocol   = Context::get('request');
        $data       = $protocol->getData();
        $fd         = $protocol->getFd();
        $selfUserId = TableManager::get(MemoryTable::SUBJECT_FD_TO_USER)->get((string)$fd, 'userId');
        $subject    = $data['subject'];
        $event      = $data['event'];
        $data       = $data['data'];
        $userIds    = TableManager::get(MemoryTable::SUBJECT_TO_USER)->get((string)$subject, 'userId') ?? [];
        $sender     = make(Sender::class);
        $userIds    = explode(',', $userIds);
        foreach ($userIds as $user_id) {
            if ($selfUserId == $user_id) {
                continue;
            }
            $toFd = TableManager::get(MemoryTable::SUBJECT_USER_TO_FD)->get((string)$user_id, 'fd');
            $sender->push((int)$toFd, MessageParser::encode([
                'event' => $event,
                'data'  => $data
            ]));
        }
    }
}
