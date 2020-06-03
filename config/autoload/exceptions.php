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

use App\Exception\Handler\AppExceptionHandler;
use App\Exception\Handler\HttpExceptionHandler;
use App\Exception\Handler\InputExceptionHandler;

return [
    'handler' => [
        'http' => [
            InputExceptionHandler::class,
            HttpExceptionHandler::class,
            AppExceptionHandler::class,
        ],
    ],
];
