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
use App\Model\FriendChatHistory;
use App\Model\FriendGroup;
use App\Model\FriendRelation;
use App\Model\Group;
use App\Model\GroupRelation;
use App\Model\User;
use App\Model\UserApplication;
use App\Task\FriendTask;
use App\Task\UserTask;
use Hyperf\DbConnection\Db;
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
        $userInfos = array_column($users, NULL, 'id');

        $friend = [];
        foreach ($friendGroups as $friend_group) {
            $friend[$friend_group['id']] = [
                'id'        => $friend_group['id'],
                'groupname' => $friend_group['friend_group_name'],
                'list'      => []
            ];
        }

        foreach ($friendRelations as $friend_relation) {
            $userInfo                                              = $userInfos[$friend_relation['friend_id']];
            $friend[$friend_relation['friend_group_id']]['list'][] = [
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

    /**
     * @param int $userApplicationId
     * @param int $friendGroupId
     *
     * @return array
     */
    public static function agreeApply(int $userApplicationId, int $friendGroupId)
    {
        $userApplicationInfo = UserService::beforeApply($userApplicationId, UserApplication::APPLICATION_TYPE_FRIEND);

        self::findFriendGroupById($userApplicationInfo->group_id);
        self::findFriendGroupById($friendGroupId);

        self::changeApplicationStatusById($userApplicationId, UserApplication::APPLICATION_STATUS_ACCEPT);
        $fromCheck = self::checkIsFriendRelation($userApplicationInfo->receiver_id, $userApplicationInfo->uid);
        $toCheck   = self::checkIsFriendRelation($userApplicationInfo->uid, $userApplicationInfo->receiver_id);

        if (!$fromCheck) {
            self::createFriendRelation($userApplicationInfo->receiver_id, $userApplicationInfo->uid, $friendGroupId);
            self::createFriendRelation($userApplicationInfo->uid, $userApplicationInfo->receiver_id, $userApplicationInfo->group_id);
        }

        if ($fromCheck && $toCheck) {
            throw new ApiException(ErrorCode::FRIEND_RELATION_ALREADY);
        }

        $friendInfo = UserService::findUserInfoById($userApplicationInfo->uid);
        $selfInfo   = UserService::findUserInfoById($userApplicationInfo->receiver_id);

        $pushUserInfo = [
            'type'     => UserApplication::APPLICATION_TYPE_FRIEND,
            'avatar'   => $selfInfo->avatar,
            'username' => $selfInfo->username,
            'groupid'  => $userApplicationInfo->group_id,
            'id'       => $selfInfo->id,
            'sign'     => $selfInfo->sign,
            'status'   => $selfInfo->status
        ];

        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$friendInfo->id, 'fd') ?? '';
        if ($fd) {
            ApplicationContext::getContainer()->get(FriendTask::class)->agreeApply($fd, $pushUserInfo);
            ApplicationContext::getContainer()->get(UserTask::class)->unReadApplicationCount($fd, '新');
        }

        return [
            'type'     => UserApplication::APPLICATION_TYPE_FRIEND,
            'avatar'   => $friendInfo->avatar,
            'username' => $friendInfo->username,
            'id'       => $friendInfo->id,
            'sign'     => $friendInfo->sign,
            'groupid'  => $friendGroupId,
            'status'   => FriendRelation::STATUS_TEXT[$friendInfo->status]
        ];
    }

    /**
     * @param int $id
     * @param int $applicationStatus
     *
     * @return int
     */
    public static function changeApplicationStatusById(int $id, int $applicationStatus)
    {
        return UserApplication::query()->whereNull('deleted_at')->where(['id' => $id])->update([
            'application_status' => $applicationStatus
        ]);
    }

    /**
     * @param int $userId
     * @param int $friendId
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public static function checkIsFriendRelation(int $userId, int $friendId)
    {
        return FriendRelation::query()
                             ->whereNull('deleted_at')
                             ->where(['uid' => $userId])
                             ->where(['friend_id' => $friendId])
                             ->first();
    }

    public static function createFriendRelation(int $userId, int $friendId, int $groupId)
    {
        return FriendRelation::query()->insertGetId([
            'uid'             => $userId,
            'friend_id'       => $friendId,
            'friend_group_id' => $groupId,
        ]);
    }

    /**
     * @param string $messageId
     * @param int    $fromUserId
     * @param int    $toUserId
     * @param string $content
     * @param int    $receptionState
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|FriendChatHistory
     */
    public static function createFriendChatHistory(
        string $messageId,
        int $fromUserId,
        int $toUserId,
        string $content,
        int $receptionState = FriendChatHistory::NOT_RECEIVED
    ) {
        $data = [
            'message_id'      => $messageId,
            'from_uid'        => $fromUserId,
            'to_uid'          => $toUserId,
            'content'         => $content,
            'reception_state' => $receptionState
        ];
        $id   = FriendChatHistory::query()->insertGetId($data);
        return FriendChatHistory::query()->whereNull('deleted_at')->where(['id' => $id])->first();
    }

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getUnreadMessageByToUserId(int $uid)
    {
        $historyInfos = FriendChatHistory::query()->whereNull('deleted_at')->where(['to_uid' => $uid])->where('reception_state', '=', FriendChatHistory::NOT_RECEIVED)->get()->toArray();

        $userIds = [$uid];

        foreach ($historyInfos as $historyInfo) {
            array_push($userIds, $historyInfo['from_uid']);
        }

        $userInfos = array_column(UserService::getUserByIds($userIds), NULL, 'id');

        $result = [];

        foreach ($historyInfos as $historyInfo) {
            $fromUserId = $historyInfo['from_uid'];
            $result[]   = [
                'username'   => $userInfos[$fromUserId]['username'],
                'avatar'     => $userInfos[$fromUserId]['avatar'],
                'from_uid'   => $fromUserId,
                'content'    => $historyInfo['content'],
                'message_id' => $historyInfo['message_id'],
                'timestamp'  => strtotime($historyInfo['created_at']) * 1000
            ];
        }
        return $result;
    }

    /**
     * @param string $messageId
     * @param int    $receptionState
     *
     * @return int
     */
    public static function setFriendChatHistoryReceptionStateByMessageId(string $messageId, int $receptionState = FriendChatHistory::RECEIVED)
    {
        return FriendChatHistory::query()->whereNull('deleted_at')->where('message_id', '=', $messageId)->update([
            'reception_state' => $receptionState
        ]);
    }

    public static function getChatHistory(int $fromUserId, int $userId, int $page, int $size)
    {
        $model                 = FriendChatHistory::query()->whereNull('deleted_at')
                                                  ->where('from_uid', '=', $fromUserId)
                                                  ->where('to_uid', $userId)
                                                  ->orWhere('from_uid', '=', $userId)
                                                  ->where('to_uid', $fromUserId);
        $historyInfos['count'] = $model->count('id');
        $historyInfos['list']  = $model->orderBy('created_at', 'ASC')
                                       ->limit($size)
                                       ->offset(($page - 1) * $size)
                                       ->get()
                                       ->toArray();

        $userInfos = array_column(UserService::getUserByIds([$fromUserId, $userId]), NULL, 'id');

        $result = [
            'count' => $historyInfos['count'],
            'list'  => []
        ];

        foreach ($historyInfos['list'] as $history_info) {
            $id               = $history_info['from_uid'];
            $result['list'][] = [
                'id'        => $id,
                'username'  => $userInfos[$id]['username'],
                'avatar'    => $userInfos[$id]['avatar'],
                'content'   => $history_info['content'],
                'timestamp' => strtotime($history_info['created_at']) * 1000
            ];
        }
        return $result;
    }

    /**
     * @param int $userApplicationId
     *
     * @return \App\Model\UserApplication|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public static function refuseApply(int $userApplicationId)
    {
        $userApplicationInfo = UserService::beforeApply($userApplicationId, UserApplication::APPLICATION_TYPE_FRIEND);
        self::changeApplicationStatusById($userApplicationId, UserApplication::APPLICATION_STATUS_REFUSE);

        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$userApplicationInfo->id, 'fd') ?? '';
        if ($fd) {
            ApplicationContext::getContainer()->get(UserTask::class)->unReadApplicationCount($fd, '新');
        }
        return $userApplicationInfo;
    }

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getFriendIdsByUserId(int $uid)
    {
        return FriendRelation::query()->where(['uid' => $uid])->whereNull('deleted_at')->get()->toArray();
    }
}

