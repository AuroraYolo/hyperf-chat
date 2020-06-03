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

use App\Constants\WsMessage;
use Hyperf\Task\Annotation\Task;
use Hyperf\WebSocketServer\Sender;
use function App\Helper\wsSuccess;

class VideoTask
{
    /**
     * @Task()
     * @param int      $userId
     * @param null|int $fromFd
     * @param null|int $toFd
     * @param string   $formUserName
     * @param string   $toUserName
     */
    public function createFriendVideo(int $userId, ?int $fromFd, ?int $toFd, string $formUserName, string $toUserName)
    {
        $result = wsSuccess(WsMessage::WS_MESSAGE_CMD_EVENT, WsMessage::EVENT_FRIEND_VIDEO_ROOM, [
            'roomId'       => md5((string)$userId),
            'userId'       => $userId,
            'fromUserName' => $formUserName,
            'toUserName'   => $toUserName
        ]);
        $sender = make(Sender::class);
        $sender->push((int)$fromFd, $result);
        $sender->push((int)$toFd, $result);
    }
}
