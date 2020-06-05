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

namespace App\Listener;

use App\Component\Log\RuntimeLog;
use App\Constants\Atomic;
use App\Constants\Message;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Memory\AtomicManager;
use Hyperf\Memory\TableManager;

class BeforeMainServerStartListener implements ListenerInterface
{

    private $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen() : array
    {
        return [
            BeforeMainServerStart::class
        ];
    }

    public function process(object $event)
    {
        $this->logger->debug("Hyperf-im Starting................");
        $this->logger->debug("\r\n");
        $this->logger->debug(Message::TITLE);
        $this->logger->debug("\r\n");
        $tableConfig = config('table');
        foreach ($tableConfig as $key => $item) {
            TableManager::initialize($key, $item['size']);
            RuntimeLog::debug(sprintf('TableManager [%s] initialize...', $key));
            foreach ($item['columns'] as $columnKey => $column) {
                TableManager::get($key)->column($columnKey, $column['type'], $column['size']);
            }
            TableManager::get($key)->create();
        }
        AtomicManager::initialize(Atomic::NAME);
        RuntimeLog::debug(sprintf('AtomicManager [%s] initialize...', Atomic::NAME));
        $this->logger->debug("Hyperf-im Success.................");
    }
}
