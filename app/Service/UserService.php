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

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Model\User;
use App\Model\UserApplication;
use App\Model\UserLoginLog;
use Nette\Utils\Json;
use function App\Helper\getClientIp;

class UserService
{
    /**
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public static function register(string $email, string $password) : bool
    {
        $user = self::findUserByEmail($email);
        if ($user) {
            throw new ApiException(ErrorCode::USER_EMAIL_ALREADY_USE);
        }
        return User::query()->insert([
            'email'    => $email,
            'password' => password_hash($password, CRYPT_BLOWFISH),
            'username' => $email,
            'sign'     => '',
            'status'   => User::STATUS_OFFLINE,
            'avatar'   => 'https://s.gravatar.com/avatar/' . md5(strtolower(trim($email))),
        ]);
    }

    /**
     * @param string $email
     *
     * @return null|\App\Model\User|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public static function findUserByEmail(string $email)
    {
        return User::query()->where('email', '=', $email)->first() ?? NULL;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return User
     */
    public static function login(string $email, string $password) : User
    {
        $user = self::findUserByEmail($email);
        if (!$user || $user['delete_at'] !== NULL) {
            throw new ApiException(ErrorCode::USER_NOT_FOUND);
        }
        if (!password_verify($password, $user['password'])) {
            throw new ApiException(ErrorCode::USER_PASSWORD_ERROR);
        }
        self::userLoginLog($user['id']);
        return $user;
    }

    /**
     * @param int $uid
     *
     * @return bool
     */
    public static function userLoginLog(int $uid) : bool
    {
        return UserLoginLog::query()->insert([
            'uid'           => $uid,
            'user_login_ip' => getClientIp()
        ]);
    }

    public static function getUserByIds(array $ids)
    {
        return User::query()->whereNull('deleted_at')->whereIn('id', $ids)->get()->toArray();
    }

    /**
     * @param int    $userId
     * @param int    $receiverId
     * @param int    $groupId
     * @param int    $applicationType
     * @param string $applicationReason
     * @param int    $applicationStatus
     * @param int    $readState
     *
     * @return int
     */
    public static function createUserApplication(
        int $userId,
        int $receiverId,
        int $groupId,
        int $applicationType,
        string $applicationReason,
        int $applicationStatus = UserApplication::APPLICATION_STATUS_CREATE,
        int $readState = UserApplication::UN_READ
    ) {
        return UserApplication::query()->insertGetId([
            'uid'                => $userId,
            'receiver_id'        => $receiverId,
            'group_id'           => $groupId,
            'application_type'   => $applicationType,
            'application_status' => $applicationStatus,
            'application_reason' => $applicationReason,
            'read_state'         => $readState
        ]);
    }

    public static function setUserStatus(int $userId, int $status = User::STATUS_ONLINE)
    {

    }
}
