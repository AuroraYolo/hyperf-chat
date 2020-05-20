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

use Symfony\Component\Finder\Finder;

return [
    'scan' => [
        'paths'              =>
            value(function ()
            {
                $paths = [];
                $dirs  = Finder::create()->in(BASE_PATH . '/app')
                               ->depth('< 1')
                               ->exclude(['Model']) // 此处按照实际情况进行修改
                               ->directories();
                /** @var \SplFileInfo $dir */
                foreach ($dirs as $dir) {
                    $paths[] = $dir->getRealPath();
                }
                return $paths;
            }),
        //            [
        //            BASE_PATH . '/app',
        //        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
