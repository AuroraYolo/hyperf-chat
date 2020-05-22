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

namespace App\Controller\Http;

use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;

/**
 * Class TestController
 * @package App\Controller\Http
 * @Controller(prefix="test")
 */
class TestController extends AbstractController
{
    /**
     * @RequestMapping(path="table",methods="GET")
     */
    public function table()
    {
        dump(111);
        $table1 = (TableManager::get(MemoryTable::USER_TO_FD));
        dump($table1->count());
        foreach ($table1 as $row) {
            dump($row);
        }

        $table2 = (TableManager::get(MemoryTable::FD_TO_USER));
        dump($table2->count());
        foreach ($table2 as $item) {
            dump($item);
        }
    }
}
