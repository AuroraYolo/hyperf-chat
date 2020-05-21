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
namespace App\Component;

use Hyperf\Utils\ApplicationContext;

class Server
{
    /**
     * @param       $data
     * @param array $fds
     */
    public static function sendToAll($data, array $fds = [])
    {
        /**
         * @var \Swoole\WebSocket\Server $server
         */
        $server = ApplicationContext::getContainer()->get(\Swoole\Server::class);
        foreach ($fds as $fd) {
            if ($server->isEstablished($fd)) {
                $server->push($fd, $data);
            }
        }
    }

    /**
     * Disconnect for client, will trigger onClose
     *
     * @param int    $fd
     * @param int    $code
     * @param string $reason
     *
     * @return bool|mixed
     */
    public static function disconnect(int $fd, int $code = 0, string $reason = '')
    {
        /**
         * @var \Swoole\WebSocket\Server $server
         */
        $server = ApplicationContext::getContainer()->get(Server::class);
        // If it's invalid fd
        if (!$server->isEstablished($fd)) {
            return false;
        }

        return $server->disconnect($fd, $code, $reason);
    }
}

