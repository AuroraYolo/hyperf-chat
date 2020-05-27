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

namespace App\Component\Log;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LoggerInterface;

/**
 * Class Log.
 * @method static bool log($level, $message, array $context = [])
 * @method static bool debug($message, array $context = [])  debug详情
 * @method static bool info($message, array $context = [])   重要事件  例如：用户登录和SQL记录
 * @method static bool notice($message, array $context = [])  一般重要的事件
 * @method static bool warning($message, array $context = [])  出现非错误性的异常。
 * @method static bool error($message, array $context = [])     运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测
 * @method static bool crit($message, array $context = [])
 * @method static bool critical($message, array $context = []) 紧急情况 例如:程序组件不可用或者出现非预期的异常
 * @method static bool alert($message, array $context = [])     必须**立刻采取行动 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下，**应该**发送一条警报短信把你叫醒。
 * @method static bool emerg($message, array $context = [])
 * @method static bool emergency($message, array $context = [])   系统不可用
 */
abstract class Log
{
    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (!method_exists(static::get(), $name)) {
            throw new \RuntimeException('Logger method not found!');
        }
        return call_user_func([self::get(), $name], ...$arguments);
    }

    /**
     * @param string $name
     *
     * @param string $group
     *
     * @return LoggerInterface
     */
    public static function get(string $name = 'app', string $group = 'default')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name, $group);
    }
}
