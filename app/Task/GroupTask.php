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

namespace App\Task;

use App\Component\Server;
use App\Constants\WsMessage;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Task\Annotation\Task;
use function App\Helper\wsSuccess;

class GroupTask
{
    /**
     * @Inject()
     * @var \Hyperf\WebSocketServer\Sender
     */
    private $sender;

    /**
     * @Task()
     * @param $fds
     * @param $username
     * @param $avatar
     * @param $groupId
     * @param $type
     * @param $content
     * @param $cid
     * @param $mine
     * @param $fromId
     * @param $timestamp
     *
     * @return bool
     */
    public function sendMessage(
        $fds,
        $username,
        $avatar,
        $groupId,
        $type,
        $content,
        $cid,
        $mine,
        $fromId,
        $timestamp
    ) {
        if (!$fds) {
            return false;
        }
        $data   = [
            'username'  => $username,
            'avatar'    => $avatar,
            'id'        => $groupId,
            'type'      => $type,
            'content'   => $content,
            'cid'       => $cid,
            'mine'      => $mine,
            'fromid'    => $fromId,
            'timestamp' => $timestamp,
        ];
        $result = wsSuccess(WsMessage::WS_MESSAGE_CMD_EVENT, WsMessage::EVENT_GET_MESSAGE, $data);
        Server::sendToAll($result, $fds);
        return true;
    }

    /**
     * @Task()
     * @param int   $fd
     * @param array $data
     */
    public function agreeApply(int $fd, array $data)
    {
        $result = wsSuccess(WsMessage::WS_MESSAGE_CMD_EVENT, WsMessage::EVENT_GROUP_AGREE_APPLY, $data);
        $this->sender->push($fd, $result);
    }
}
