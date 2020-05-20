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

use Swoole\Table;

return [
    'fdToUser'        => [
        'size'    => 1024 * 5,
        'columns' => [
            'userId' => [
                'type' => Table::TYPE_INT,
                'size' => 4
            ]
        ]
    ],
    'userToFd'        => [
        'size'    => 1024 * 5,
        'columns' => [
            'fd' => [
                'type' => Table::TYPE_INT,
                'size' => 4
            ]
        ]
    ],
    'subjectFdToUser' => [
        'size'    => 1024 * 5,
        'columns' => [
            'userId' => [
                'type' => Table::TYPE_INT,
                'size' => 4
            ]
        ]
    ],
    'subjectUserToFd' => [
        'size'    => 1024 * 5,
        'columns' => [
            'fd' => [
                'type' => Table::TYPE_INT,
                'size' => 4
            ]
        ]
    ],
    'subjectToUser'   => [
        'size'    => 1024 * 5,
        'columns' => [
            'userId' => [
                'type' => Table::TYPE_STRING,
                'size' => 40
            ]
        ]
    ],
    'userToSubject'   => [
        'size'    => 1024 * 5,
        'columns' => [
            'subject' => [
                'type' => Table::TYPE_STRING,
                'size' => 32
            ]
        ]
    ]
];
