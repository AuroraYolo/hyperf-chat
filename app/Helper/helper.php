<?php
declare(strict_types = 1);

namespace App\Helper;

use App\Constants\ErrorCode;
use App\Constants\WsMessage;
use App\Exception\ApiException;
use App\Model\User;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\JWT;
use Psr\Http\Message\ServerRequestInterface;

if (!function_exists('autoHidSubstr')) {
    /**
     * 智能字符串模糊化
     *
     * @param string $str 被模糊的字符串
     * @param int    $len 模糊的长度
     *
     * @return string
     */
    function autoHidSubstr($str, $len = 3)
    {
        if (empty($str)) {
            return NULL;
        }
        $str = (string)$str;

        $sub_str = mb_substr($str, 0, 1, 'utf-8');
        for ($i = 0; $i < $len; $i++) {
            $sub_str .= '*';
        }
        if (mb_strlen($str, 'utf-8') <= 2) {
            $str = $sub_str;
        }
        $sub_str .= mb_substr($str, -1, 1, 'utf-8');
        return $sub_str;
    }
}

if (!function_exists('isEmptyParam')) {
    /**
     * 判断是否存在并且不为空
     *
     * @param $param
     *
     * @return bool
     */
    function isEmptyParam($param)
    {
        return (isset($param) && !empty($param));
    }
}

if (!function_exists('getRandStr')) {
    /**
     * 产生数字与字母混合随机字符串
     *
     * @param int $len 数值长度,默认6位
     *
     * @return string
     */
    function getRandStr($len = 6)
    {
        $chars    = [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ];
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = '';
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }

        return $output;
    }
}

if (!function_exists('getClientIp')) {
    function getClientIp()
    {
        try {
            /**
             * @var ServerRequestInterface $request
             */
            $request = Context::get(ServerRequestInterface::class);
            $ip_addr = $request->getHeaderLine('x-forwarded-for');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('remote-host');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('x-real-ip');
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
            if (verifyIp($ip_addr)) {
                return $ip_addr;
            }
        } catch (Throwable $e) {
            return '0.0.0.0';
        }
        return '0.0.0.0';
    }
}
if (!function_exists('verifyIp')) {
    function verifyIp($realip)
    {
        return filter_var($realip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}
if (!function_exists('p')) {
    function p($val, $title = NULL, $starttime = '')
    {
        print_r('[ ' . date("Y-m-d H:i:s") . ']:');
        if ($title != NULL) {
            print_r("[" . $title . "]:");
        }
        print_r($val);
        print_r("\r\n");
    }
}
if (!function_exists('uuid')) {
    function uuid($length)
    {
        if (function_exists('random_bytes')) {
            $uuid = bin2hex(random_bytes($length));
        } else {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $uuid = bin2hex(openssl_random_pseudo_bytes($length));
            } else {
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $uuid = substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
            }
        }
        return $uuid;
    }
}
if (!function_exists('filterEmoji')) {
    function filterEmoji($str)
    {
        $str     = preg_replace_callback(
            '/./u',
            function (array $match)
            {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        $cleaned = strip_tags($str);
        return htmlspecialchars(($cleaned));
    }
}

if (!function_exists('convertUnderline')) {
    function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches)
        {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }
}

/*
    * 驼峰转下划线
    */
if (!function_exists('humpToLine')) {
    function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches)
        {
            return '_' . strtolower($matches[0]);
        }, $str);
        return $str;
    }
}
if (!function_exists('convertHump')) {
    function convertHump(array $data)
    {
        $result = [];
        foreach ($data as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $result[humpToLine($key)] = convertHump((array)$item);
            } else {
                $result[humpToLine($key)] = $item;
            }
        }
        return $result;
    }
}

/**
 * @param $list
 * @param $x
 * @param $y
 *
 * @return bool
 */
if (!function_exists('inRegion')) {
    function inRegion($list, $x, $y)
    {
        $count = count($list);
        $c     = 0;
        for ($i = 0; $i < $count; $i++) {
            [$x1, $y1] = $list[$i];
            [$x2, $y2] = $list[($i + 1) % $count];
            if ($y1 == $y2) {
                continue;
            }//平行线不计算
            if ($y < \min($y1, $y2)) {
                continue;
            }//上下区域外不计算
            if ($y > \max($y1, $y2)) {
                continue;
            }//上下区域外不计算
            $j = $x2 + (($x1 - $x2) * ($y - $y2) / ($y1 - $y2));
            if ($j < $x) {
                continue;
            }
            if ($j == $x2 && $y == $y2) {
                continue;
            }//同一顶点只能计算一次
            if ($j == $x) {
                return true;
            }
            $c++;
        }
        return $c % 2 == 1;
    }
}
if (!function_exists('toArray()')) {
    /**
     * 任意对象转为数组，确保返回的结果是数组
     *
     * @param        $data
     * @param string $key   如果key不为空，重定义二维数组的key值为二维中的某一列值
     * @param string $value 如果value不为空，第二维数组变为一列值
     *
     * @return array|mixed
     */
    function toArray($data, $key = '', $value = '')
    {
        if (\is_array($data)) {
            $r = $data;
        } elseif (!$data) {
            $r = [];
        } elseif (\method_exists($data, 'toArray')) {
            $r = $data->toArray() ?? [];
        } elseif (\is_object($data)) {
            $json = \json_encode($data, \JSON_UNESCAPED_UNICODE);
            $r    = \json_decode($json, true) ?? [];
        } elseif (\is_string($data)) {
            $r = \json_decode($data, true) ?? [];
        } else {
            return [];
        }
        if ($key) {
            $r2 = [];
            foreach ($r as $k => $v) {
                if (!empty($v[$key])) {
                    $r2[$v[$key]] = $value ? ($v[$value] ?? '') : $v;
                }
            }
            return $r2;
        }
        return $r;
    }
}

if (!function_exists('checkAuth')) {
    /**
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    function checkAuth()
    {
        /**
         * @var ServerRequestInterface $request
         */
        $request = Context::get(ServerRequestInterface::class);
        $token   = $request->getCookieParams()['IM_TOKEN'] ?? '';
        if (strlen($token) > 0) {
            try {
                $jwt = make(JWT::class);
                if (strlen($token) > 0 && $jwt->checkToken($token)) {
                    $jwtData = $jwt->getParserData($token);
                    $user    = User::query()->where(['id' => $jwtData['uid']])->first();
                    if (empty($user)) {
                        throw new ApiException(ErrorCode::AUTH_ERROR);
                    }
                    return $user ?? NULL;
                }
            } catch (Throwable $e) {
                throw new ApiException(ErrorCode::AUTH_ERROR);
            }
        }
        return NULL;
    }
}

if (!function_exists('wsSuccess')) {
    function wsSuccess($cmd = WsMessage::WS_MESSAGE_CMD_EVENT, $method = '', $data = [], $msg = 'Success')
    {
        $result = [
            'cmd'    => $cmd,
            'method' => $method,
            'msg'    => $msg,
            'data'   => $data
        ];

        return Json::encode($result);
    }
}

if (!function_exists('wsError')) {
    function wsError($msg = 'Error', $cmd = WsMessage::WS_MESSAGE_CMD_ERROR, $data = [])
    {
        $result = [
            'cmd'  => $cmd,
            'msg'  => $msg,
            'data' => $data
        ];
        return Json::encode($result);
    }
}

