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
use App\Constants\ErrorCode;
use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Model\UserApplication;
use App\Service\GroupService;
use App\Service\UserService;
use App\Task\GroupTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\Context;

/**
 * Class GroupController
 * @package App\Controller\Ws
 * @Controller(prefix="group",server="ws")
 */
class GroupController extends AbstractController
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

        $check = GroupService::checkNotGroupRelation((int)$data['from_user_id'], (int)$data['to_id']);

        if (!$check) {
            throw new ApiException(ErrorCode::GROUP_NOT_MEMBER, $data['message_id'],);
        }
        $groupChatHistoryInfo = GroupService::createGroupChatHistory($data['message_id'], (int)$data['from_user_id'], (int)$data['to_id'], (string)$data['content']);

        $userInfo = UserService::findUserInfoById((int)$data['from_user_id']);

        $userIds = GroupService::getGroupRelationUserIdsById((int)$data['to_id']);
        $userIds = array_column($userIds, 'uid');

        $fds = [];

        $selfFd = $protocol->getFd();

        foreach ($userIds as $userId) {
            $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$userId, 'fd') ?? '';
            if ($fd && ($fd != $selfFd)) {
                array_push($fds, $fd);
            }
        }
        $this->container->get(GroupTask::class)->sendMessage($fds,
            $userInfo->username,
            $userInfo->avatar,
            $data['to_id'],
            UserApplication::APPLICATION_TYPE_GROUP,
            $data['content'],
            $data['message_id'],
            false,
            $data['from_user_id'],
            $groupChatHistoryInfo->created_at->getTimestamp() * 1000);

        return ['message_id' => $data['message_id'] ?? ''];
    }
}
