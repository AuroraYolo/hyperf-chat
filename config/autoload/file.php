<?php

declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Filesystem\Adapter\AliyunOssAdapterFactory;
use Hyperf\Filesystem\Adapter\FtpAdapterFactory;
use Hyperf\Filesystem\Adapter\LocalAdapterFactory;
use Hyperf\Filesystem\Adapter\MemoryAdapterFactory;
use Hyperf\Filesystem\Adapter\QiniuAdapterFactory;
use Hyperf\Filesystem\Adapter\S3AdapterFactory;

return [
    'default' => 'local',
    'storage' => [
        'local'  => [
            'driver' => LocalAdapterFactory::class,
            'root'   => __DIR__ . '/../../public/storage',
        ],
        'ftp'    => [
            'driver'   => FtpAdapterFactory::class,
            'host'     => 'ftp.example.com',
            'username' => 'username',
            'password' => 'password',
            // 'port' => 21,
            // 'root' => '/path/to/root',
            // 'passive' => true,
            // 'ssl' => true,
            // 'timeout' => 30,
            // 'ignorePassiveAddress' => false,
        ],
        'memory' => [
            'driver' => MemoryAdapterFactory::class,
        ],
        's3'     => [
            'driver'                  => S3AdapterFactory::class,
            'credentials'             => [
                'key'    => env('S3_KEY'),
                'secret' => env('S3_SECRET'),
            ],
            'region'                  => env('S3_REGION'),
            'version'                 => 'latest',
            'bucket_endpoint'         => false,
            'use_path_style_endpoint' => false,
            'endpoint'                => env('S3_ENDPOINT'),
            'bucket_name'             => env('S3_BUCKET'),
        ],
        'minio'  => [
            'driver'                  => S3AdapterFactory::class,
            'credentials'             => [
                'key'    => env('S3_KEY'),
                'secret' => env('S3_SECRET'),
            ],
            'region'                  => env('S3_REGION'),
            'version'                 => 'latest',
            'bucket_endpoint'         => false,
            'use_path_style_endpoint' => true,
            'endpoint'                => env('S3_ENDPOINT'),
            'bucket_name'             => env('S3_BUCKET'),
        ],
        'oss'    => [
            'driver'       => AliyunOssAdapterFactory::class,
            'accessId'     => env('OSS_ACCESS_ID'),
            'accessSecret' => env('OSS_ACCESS_SECRET'),
            'bucket'       => env('OSS_BUCKET'),
            'endpoint'     => env('OSS_ENDPOINT'),
            // 'timeout'        => 3600,
            // 'connectTimeout' => 10,
            // 'isCName'        => false,
            // 'token'          => '',
        ],
        'qiniu'  => [
            'driver'    => QiniuAdapterFactory::class,
            'accessKey' => env('QINIU_ACCESS_KEY'),
            'secretKey' => env('QINIU_SECRET_KEY'),
            'bucket'    => env('QINIU_BUCKET'),
            'domain'    => env('QINBIU_DOMAIN'),
        ],
    ],
];
