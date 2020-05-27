<?php
declare(strict_types = 1);
namespace App\Component\Log;

class RuntimeLog extends Log
{
    public const LOG_NAME = 'runtime';

    public const GROUP = 'runtime';

    public static function __callStatic($name, $arguments)
    {
        if (!method_exists(static::get(self::LOG_NAME, self::GROUP), $name)) {
            throw new \RuntimeException('Logger method not found!');
        }
        return call_user_func([self::get(self::LOG_NAME, self::GROUP), $name], ...$arguments);
    }
}
