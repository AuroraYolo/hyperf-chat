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

return [
    [
        'title' => '好友管理',
        'child' => [
            [
                'title'  => '创建分组',
                'id'     => 'createFriendGroup',
                'url'    => '/index/createFriendGroup',
                'width'  => '550px',
                'height' => '400px',
            ],
            [
                'title'  => '查找好友',
                'id'     => 'findUser',
                'url'    => '/index/findUser',
                'width'  => '1000px',
                'height' => '520px',
            ]
        ]
    ],
    [
        'title' => '群管理',
        'child' => [
            [
                'title'  => '创建群',
                'id'     => 'createGroup',
                'url'    => '/index/createGroup',
                'width'  => '550px',
                'height' => '480px',
            ],
            [
                'title'  => '查找群',
                'id'     => 'findGroup',
                'url'    => '/index/findGroup',
                'width'  => '1000px',
                'height' => '520px',
            ]
        ]
    ],
    [
        'title' => '其它',
        'child' => [
            [
                'title'  => '作者博客',
                'id'     => 'blog',
                'url'    => 'https://hy.jayjay.cn',
                'width'  => '1000px',
                'height' => '520px',
            ],
            [
                'title'  => '关于',
                'id'     => 'about',
                'url'    => '/index/about',
                'width'  => '1000px',
                'height' => '520px',
            ]
        ]
    ]
];
