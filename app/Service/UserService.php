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
use App\Model\FriendRelation;
use App\Model\User;
use App\Model\UserApplication;
use App\Model\UserLoginLog;
use App\Task\UserTask;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Psr\Http\Message\ServerRequestInterface;
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
     * @param string $applicationType
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
        string $applicationType,
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
        self::changeUserInfoById($userId, [
            'status' => $status
        ]);
        $friendIds = FriendService::getFriendGroupByUserId($userId);
        $friendIds = array_column($friendIds, 'friend_id');

        $onlineFds = [];
        foreach ($friendIds as $friendId) {
            $fd = TableManager::get(MemoryTable::USER_TO_FD)->get($friendId, 'fd');
            $fd && array_push($onlineFds, $fd);
        }

        $result = [
            'user_id' => $userId,
            'status'  => FriendRelation::STATUS_TEXT[$status]
        ];

        $task = ApplicationContext::getContainer()->get(UserTask::class);
        $task->setUserStatus($onlineFds, $result);

        return $result;
    }

    public static function changeUserInfoById(int $userId, array $data)
    {
        return User::query()->whereNull('deleted_at')->where(['id' => $userId])->update($data);
    }

    /**
     * @param int $uid
     *
     * @return int
     */
    public static function getUnreadApplicationCount(int $uid)
    {
        return UserApplication::query()
                              ->whereNull('deleted_at')
                              ->where('read_state', 'eq', userApplication::UN_READ)
                              ->where('receiver_id', '=', $uid)
                              ->count('id');
    }

    /**
     * @param int    $userApplicationId
     * @param string $userApplicationType
     *
     * @return \App\Model\UserApplication|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public static function beforeApply(int $userApplicationId, string $userApplicationType)
    {
        $userApplicationInfo = self::findUserApplicationById($userApplicationId);
        self::checkApplicationProcessed($userApplicationInfo);
        dump($userApplicationInfo->application_type,$userApplicationType);
        if ($userApplicationInfo->application_type !== $userApplicationType) {
            throw new ApiException(ErrorCode::USER_APPLICATION_TYPE_WRONG);
        }
        return $userApplicationInfo;
    }

    /**
     * @param int $id
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|UserApplication
     */
    public static function findUserApplicationById(int $id)
    {
        $userApplication = UserApplication::query()->whereNull('deleted_at')->find($id);
        if (!$userApplication) {
            throw new ApiException(ErrorCode::USER_APPLICATION_NOT_FOUND);
        }
        return $userApplication;
    }

    public static function checkApplicationProcessed(UserApplication $userApplication)
    {
        if ($userApplication->application_status !== UserApplication::APPLICATION_STATUS_CREATE) {
            throw new ApiException(ErrorCode::USER_APPLICATION_PROCESSED);
        }

        if ($userApplication->receiver_id !== $request = Context::get(ServerRequestInterface::class)->getAttribute('user')->id) {
            throw new ApiException(ErrorCode::NO_PERMISSION_PROCESS);
        }
    }

    /**
     * @param int $uid
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|User
     */
    public static function findUserInfoById(int $uid)
    {
        $userInfo = User::query()->whereNull('deleted_at')->where(['id' => $uid])->first();
        if (!$userInfo) {
            throw new ApiException(ErrorCode::USER_NOT_FOUND);
        }

        return $userInfo;
    }

    /**
     * @param int $uid
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public static function getApplication(int $uid, int $page, int $size)
    {
        $model                = UserApplication::query()
                                               ->whereNull('deleted_at')
                                               ->where(['uid' => $uid])
                                               ->orWhere(['receiver_id' => $uid]);
        $applications['list'] = $model->orderBy('created_at', 'desc')->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        $count                = $model->count('id');
        $result               = [];
        $userIds              = [];
        $groupIds             = [];
        $applicationIds       = [];
        foreach ($applications['list'] as $application) {
            ($uid != $application['uid']) && array_push($applicationIds, $application['id']);
            $applicationRole = ($uid === $application['uid'])
                ? (($application['application_status'] != UserApplication::APPLICATION_STATUS_CREATE)
                    ? $applicationRole = UserApplication::APPLICATION_SYSTEM
                    : UserApplication::APPLICATION_CREATE_USER)
                : UserApplication::APPLICATION_RECEIVER_USER;
            array_push($userIds, $application['uid']);
            array_push($userIds, $application['receiver_id']);

            ($application['application_type'] == UserApplication::APPLICATION_TYPE_GROUP) && array_push($groupIds, $application['group_id']);

            ($application['application_type'] == UserApplication::APPLICATION_TYPE_GROUP) && array_push($groupIds, $application['groupId']);

            $result[] = [
                'user_application_id'     => $application['id'],
                'user_id'                 => $application['uid'],
                'receiver_id'             => $application['receiver_id'],
                'group_id'                => $application['group_id'],
                'application_role'        => $applicationRole,
                'application_type'        => $application['application_type'],
                'created_at'              => $application['created_at'],
                'updated_at'              => $application['updated_at'],
                'application_status'      => $application['application_status'],
                'application_status_text' => UserApplication::APPLICATION_STATUS_TEXT[$application['application_status']],
                'application_reason'      => $application['application_reason']
            ];
        }

        $userInfos  = array_column(self::getUserByIds($userIds), NULL, 'id');
        $groupInfos = array_column(FriendService::getGroupByIds($groupIds), NULL, 'group_id');

        foreach ($result as &$item) {
            if ($item['application_type'] == UserApplication::APPLICATION_TYPE_GROUP) {
                $item['group_name']   = $groupInfos[$item['group_id']]['group_name'] ?? '';
                $item['group_avatar'] = $groupInfos[$item['group_id']]['avatar'] ?? '';
            }
            $item['user_name']       = $userInfos[$item['user_id']]['username'] ?? '';
            $item['user_avatar']     = $userInfos[$item['user_id']]['avatar'] ?? '';
            $item['receiver_name']   = $userInfos[$item['receiver_id']]['username'] ?? '';
            $item['receiver_avatar'] = $userInfos[$item['receiver_id']]['avatar'] ?? '';
        }

        $applications['list'] = $result;
        if (!empty($applicationIds)) {
            $change = self::changeApplicationReadStateByIdsAndReceiverId($applicationIds, $uid, UserApplication::ALREADY_READ);
            if (!$change) {
                throw new ApiException(ErrorCode::USER_APPLICATION_SET_READ_FAIL);
            }
        }
        $applications['count'] = $count;
        return $applications;
    }

    /**
     * @param array $ids
     * @param int   $receiver_id
     * @param int   $readState
     *
     * @return int
     */
    public static function changeApplicationReadStateByIdsAndReceiverId(array $ids, int $receiver_id, int $readState)
    {
        return UserApplication::query()->whereNull('deleted_at')
                              ->where('receiver_id', '=', $receiver_id)
                              ->whereIn('id', $ids)
                              ->update([
                                  'read_state' => $readState
                              ]);
    }

}
