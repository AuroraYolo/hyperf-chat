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
use App\Constants\MemoryTable;
use App\Exception\ApiException;
use App\Model\FriendGroup;
use App\Model\FriendRelation;
use App\Model\Group;
use App\Model\GroupRelation;
use App\Model\User;
use App\Model\UserApplication;
use App\Task\UserTask;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\ApplicationContext;

class FriendService
{
    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getFriend(int $uid)
    {
        $friendGroups   = self::getFriendGroupByUserId($uid);
        $friendGroupIds = array_column($friendGroups, 'id');

        $friendRelations   = self::getFriendRelationByFriendGroupIds($friendGroupIds);
        $friendRelationIds = array_column($friendRelations, 'friend_id');

        $users     = UserService::getUserByIds($friendRelationIds);
        $userInfos = array_column($users, 'id');

        $friend = [];
        foreach ($friendGroups as $friend_group) {
            $friend[$friend_group['id']] = [
                'id'        => $friend_group['id'],
                'groupname' => $friend_group['friend_group_name'],
                'list'      => []
            ];
        }

        foreach ($friendRelations as $friend_relation) {
            $userInfo                                            = $userInfos[$friend_relation['friend_id']];
            $friend[$friend_relation['friend_group_id']]['list'] = [
                'username' => $userInfo['username'],
                'id'       => $userInfo['id'],
                'avatar'   => $userInfo['avatar'],
                'sign'     => $userInfo['sign'],
                'status'   => FriendRelation::STATUS_TEXT[$userInfo['status']],
            ];
        }
        return array_values($friend);
    }

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getFriendGroupByUserId(int $uid) : array
    {
        return FriendGroup::query()->where(['uid' => $uid])->whereNull('deleted_at')->get()->toArray();
    }

    /**
     * @param array $friendGroupIds
     *
     * @return array
     */
    public static function getFriendRelationByFriendGroupIds(array $friendGroupIds) : array
    {
        return FriendRelation::query()->whereNull('deleted_at')->whereIn('friend_group_id', $friendGroupIds)->get()->toArray();
    }

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getGroup(int $uid)
    {
        $groupRelations = self::getGroupRelationByUserId($uid);
        $groupIds       = array_column($groupRelations, 'group_id');

        $groupInfos = self::getGroupByIds($groupIds);
        $result     = [];

        foreach ($groupInfos as $groupInfo) {
            $result[] = [
                'groupname' => $groupInfo['group_name'],
                'id'        => $groupInfo['id'],
                'avatar'    => $groupInfo['avatar']
            ];
        }
        return $result;
    }

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getGroupRelationByUserId(int $uid) : array
    {
        return GroupRelation::query()->whereNull('deleted_at')->where(['uid' => $uid])->get()->toArray();
    }

    /**
     * @param array $groupIds
     *
     * @return array
     */
    public static function getGroupByIds(array $groupIds) : array
    {
        return Group::query()->whereNull('deleted_at')->whereIn('id', $groupIds)->get()->toArray();
    }

    /**
     * @param int $limit
     *
     * @return \Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    public static function getRecommendedFriend(int $limit)
    {
        return User::query()->whereNull('deleted_at')->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * @param int    $uid
     * @param string $friendGroupName
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|FriendGroup
     */
    public static function createFriendGroup(int $uid, string $friendGroupName)
    {
        $friendGroupId = FriendGroup::query()->insertGetId([
            'uid'               => $uid,
            'friend_group_name' => $friendGroupName
        ]);
        if (!$friendGroupId) {
            throw new ApiException(ErrorCode::FRIEND_GROUP_CREATE_FAIL);
        }
        return self::findFriendGroupById($friendGroupId);
    }

    /**
     * @param int $friendGroupId
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|FriendGroup
     */
    public static function findFriendGroupById(int $friendGroupId)
    {
        $result = FriendGroup::query()->where(['id' => $friendGroupId])->first();
        if (!$result) {
            throw new ApiException(ErrorCode::FRIEND_GROUP_NOT_FOUND);
        }
        return $result;
    }

    /**
     * @param string $keyword
     * @param int    $page
     * @param int    $size
     *
     * @return array
     */
    public static function searchFriend(string $keyword, int $page, int $size)
    {
        $model = User::query()->whereNull('deleted_at')
                     ->where('id', '=', $keyword)
                     ->orWhere('username', 'like', "%{$keyword}%")
                     ->orWhere('email', 'like', "%{$keyword}%");
        $list  = $model->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        $count = $model->count('id');
        return compact('list', 'count');
    }

    public static function apply(int $userId, int $receiverId, int $friendGroupId, string $applicationReason)
    {
        if ($userId == $receiverId) {
            throw new ApiException(ErrorCode::FRIEND_NOT_ADD_SELF);
        }
        /**
         * @var FriendRelation $check
         */
        $check = FriendRelation::query()
                               ->whereNull('deleted_at')
                               ->where(['uid' => $userId])
                               ->where(['friend_id' => $receiverId])
                               ->first();
        if ($check) {
            throw new ApiException(ErrorCode::FRIEND_RELATION_ALREADY);
        }

        User::query()->whereNull('deleted_at')->find($userId);
        ($receiverId);
        $friendGroupInfo = self::findFriendGroupById($friendGroupId);

        if (!$friendGroupInfo) {
            throw new ApiException(ErrorCode::FRIEND_GROUP_NOT_FOUND);
        }

        $result = UserService::createUserApplication($userId, $receiverId, $friendGroupId, UserApplication::APPLICATION_TYPE_FRIEND, $applicationReason, UserApplication::APPLICATION_STATUS_CREATE, UserApplication::UN_READ);
        if (!$result) {
            throw new ApiException(ErrorCode::USER_CREATE_APPLICATION_FAIL);
        }

        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$receiverId, 'fd') ?? '';
        if ($fd) {
            $task = ApplicationContext::getContainer()->get(UserTask::class);
            $task->unReadApplicationCount($fd, '新');
        }
        return $result;
    }
}
