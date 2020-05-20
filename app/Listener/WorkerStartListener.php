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

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Memory\AtomicManager;
use Hyperf\Memory\TableManager;

class WorkerStartListener implements ListenerInterface
{
    public function listen() : array
    {
        return [
            AfterWorkerStart::class
        ];
    }

    public function process(object $event)
    {
        $tableConfig = config('table');
        foreach ($tableConfig as $key => $item) {
            TableManager::initialize($key, $item['size']);
            foreach ($item['columns'] as $columnKey => $column) {
                TableManager::get($key)->column($columnKey, $column['type'], $column['size']);
            }
            TableManager::get($key)->create();
        }
        AtomicManager::initialize('atomic');
    }
}
