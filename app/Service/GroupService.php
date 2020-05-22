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
use App\Model\Group;
use App\Model\GroupChatHistory;
use App\Model\GroupRelation;
use App\Model\UserApplication;
use App\Task\GroupTask;
use App\Task\UserTask;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\ApplicationContext;

class GroupService
{
    /**
     * @param int    $userId
     * @param string $groupName
     * @param string $avatar
     * @param int    $size
     * @param string $introduction
     * @param int    $validation
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|Group
     */
    public static function createGroup(int $userId, string $groupName, string $avatar, int $size, string $introduction, int $validation)
    {
        $groupId = Group::query()->insertGetId([
            'uid'          => $userId,
            'group_name'   => $groupName,
            'avatar'       => $avatar,
            'size'         => $size,
            'introduction' => $introduction,
            'validation'   => $validation
        ]);
        if (!$groupId) {
            throw new ApiException(ErrorCode::GROUP_CREATE_FAIL);
        }
        $groupRelationId = GroupRelation::query()->insertGetId([
            'uid'      => $userId,
            'group_id' => $groupId
        ]);
        if (!$groupRelationId) {
            throw new ApiException(ErrorCode::GROUP_RELATION_CREATE_FAIL);
        }
        return self::findGroupById($groupId);
    }

    /**
     * @param int $groupId
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|Group
     */
    public static function findGroupById(int $groupId)
    {
        $groupInfo = Group::query()->where(['id' => $groupId])->first();
        if (!$groupInfo) {
            throw new ApiException(ErrorCode::GROUP_NOT_FOUND);
        }
        return $groupInfo;
    }

    /**
     * @param int $groupId
     *
     * @return array
     */
    public static function getGroupRelationById(int $groupId)
    {
        self::findGroupById($groupId);

        $groupRelations = GroupRelation::query()->whereNull('deleted_at')->where(['group_id' => $groupId])->get()->toArray();

        $userIds   = array_column($groupRelations, 'uid');
        $userInfos = UserService::getUserByIds($userIds);
        $data      = [];

        foreach ($userInfos as $info) {
            $data['list'][] = [
                'username' => $info['username'],
                'id'       => $info['id'],
                'avatar'   => $info['avatar'],
                'sign'     => $info['sign']
            ];
        }

        return $data;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public static function getRecommendedGroup(int $limit)
    {
        return Group::query()->whereNull('deleted_at')->orderBy('created_at', 'desc')->limit($limit)->get()->toArray();
    }

    public static function searchGroup(string $keyword, int $page, int $size)
    {
        return Group::query()->whereNull('deleted_at')
                    ->where(['id' => $keyword])
                    ->orWhere('group_name', 'like', "%$keyword%")
                    ->limit($size)
                    ->offset(($page - 1) * $size)->get()->toArray();
    }

    /**
     * @param int    $userId
     * @param int    $groupId
     * @param string $applicationReason
     *
     * @return \App\Model\Group|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|string
     */
    public static function apply(int $userId, int $groupId, string $applicationReason)
    {
        self::checkIsGroupRelation($userId, $groupId);

        $groupInfo = self::findGroupById($groupId);

        self::checkGroupSize($groupId, $groupInfo->size);

        $applicationStatus = (($groupInfo->validation) == Group::VALIDATION_NOT) ? UserApplication::APPLICATION_STATUS_ACCEPT : UserApplication::APPLICATION_STATUS_CREATE;

        $result = UserService::createUserApplication($userId, $groupInfo->uid, $groupId, UserApplication::APPLICATION_TYPE_GROUP, $applicationReason, $applicationStatus, UserApplication::UN_READ);

        if (!$result) {
            throw new ApiException(ErrorCode::USER_CREATE_APPLICATION_FAIL);
        }
        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$groupInfo->uid, 'fd') ?? '';
        if ($fd) {
            ApplicationContext::getContainer()->get(UserTask::class)->unReadApplicationCount($fd, '新');
        }
        if ($groupInfo->validation == Group::VALIDATION_NOT) {
            GroupRelation::query()->insertGetId([
                'uid'      => $userId,
                'group_id' => $groupId
            ]);
            return $groupInfo;
        }
        return '';
    }

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public static function checkIsGroupRelation(int $userId, int $groupId)
    {
        $check = GroupRelation::query()->whereNull('deleted_at')
                              ->where(['uid' => $userId])
                              ->where(['group_id' => $groupId])->first();
        if ($check) {
            throw new ApiException(ErrorCode::GROUP_RELATION_ALREADY);
        }
        return $check;
    }

    /**
     * @param int $groupId
     * @param int $size
     *
     * @return int
     */
    public static function checkGroupSize(int $groupId, int $size)
    {
        $count = GroupRelation::query()->whereNull('deleted_at')->where(['group_id' => $groupId])->count();
        if ($count >= $size) {
            throw new ApiException(ErrorCode::GROUP_FULL);
        }
        return $count;
    }

    /**
     * @param int $userApplicationId
     *
     * @return int
     */
    public static function agreeApply(int $userApplicationId)
    {
        $userApplicationInfo = UserService::beforeApply($userApplicationId, UserApplication::APPLICATION_TYPE_GROUP);

        self::checkIsGroupRelation($userApplicationInfo->uid, $userApplicationInfo->group_id);
        FriendService::changeApplicationStatusById($userApplicationId, UserApplication::APPLICATION_STATUS_ACCEPT);

        $groupInfo = self::findGroupById($userApplicationInfo->group_id);
        self::checkGroupSize($groupInfo->id, $groupInfo->size);

        $pushGroupInfo = [
            'type'      => UserApplication::APPLICATION_TYPE_GROUP,
            'avatar'    => $groupInfo->avatar,
            'groupName' => $groupInfo->group_name,
            'groupId'   => $groupInfo->id,
        ];

        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$userApplicationInfo->uid, 'fd') ?? '';

        if ($fd) {
            ApplicationContext::getContainer()->get(GroupTask::class)->agreeApply($fd, $pushGroupInfo);
            ApplicationContext::getContainer()->get(UserTask::class)->unReadApplicationCount($fd, '新');
        }

        return GroupRelation::query()->insertGetId([
            'uid'      => $userApplicationInfo->uid,
            'group_id' => $userApplicationInfo->group_id
        ]);
    }

    /**
     * @param int $userApplicationId
     *
     * @return \App\Model\UserApplication|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public static function refuseApply(int $userApplicationId)
    {
        $userApplicationInfo = UserService::beforeApply($userApplicationId, UserApplication::APPLICATION_TYPE_GROUP);
        FriendService::changeApplicationStatusById($userApplicationId, UserApplication::APPLICATION_STATUS_REFUSE);

        $fd = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$userApplicationInfo->uid, 'fd') ?? '';
        if ($fd) {
            ApplicationContext::getContainer()->get(UserTask::class)->unReadApplicationCount($fd, '新');
        }
        return $userApplicationInfo;
    }

    /**
     * @param int $toGroupId
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public static function getChatHistory(int $toGroupId, int $page, int $size)
    {
        $model                 = GroupChatHistory::query()
                                                 ->whereNull('deleted_at')
                                                 ->where(['to_group_id' => $toGroupId]);
        $historyInfos['list']  =
            $model
                ->orderBy('created_at', 'asc')
                ->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        $historyInfos['count'] = $model->count();
        $userIds               = [];
        foreach ($historyInfos['list'] as $historyInfo) {
            array_push($userIds, $historyInfo['from_uid']);
        }
        $userInfos = array_column(UserService::getUserByIds($userIds), NULL, 'id');
        $result    = [
            'count' => $historyInfos['count'],
        ];
        foreach ($historyInfos['list'] as $historyInfo) {
            $id               = $historyInfo['from_uid'];
            $result['list'][] = [
                'id'        => $id,
                'username'  => $userInfos[$id]['username'],
                'avatar'    => $userInfos[$id]['avatar'],
                'content'   => $historyInfo['content'],
                'timestamp' => strtotime($historyInfo['created_at']) * 1000
            ];
        }
        return $result;
    }

    /**
     * @param string $messageId
     * @param int    $fromUserId
     * @param int    $toGroupId
     * @param string $content
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|GroupChatHistory
     */
    public static function createGroupChatHistory(
        string $messageId,
        int $fromUserId,
        int $toGroupId,
        string $content
    ) {
        $data = [
            'message_id'  => $messageId,
            'from_uid'    => $fromUserId,
            'to_group_id' => $toGroupId,
            'content'     => $content,
        ];
        $id   = GroupChatHistory::query()->insertGetId($data);
        return GroupChatHistory::query()->whereNull('deleted_at')->where('id', '=', $id)->first();
    }

    /**
     * @param int $groupId
     *
     * @return array
     */
    public static function getGroupRelationUserIdsById(int $groupId)
    {
        return GroupRelation::query()->whereNull('deleted_at')->where(['group_id' => $groupId])->select('uid')->get()->toArray();
    }

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public static function checkNotGroupRelation(int $userId, int $groupId)
    {
        return GroupRelation::query()->whereNull('deleted_at')
                            ->where(['uid' => $userId])
                            ->where(['group_id' => $groupId])->first();
    }
}



