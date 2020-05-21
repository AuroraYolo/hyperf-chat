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

namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Service\FriendService;
use App\Service\UserService;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\JwtAuthMiddleware;

/**
 * Class FriendController
 * @package App\Controller\Http
 * @Controller(prefix="friend")
 */
class FriendController extends AbstractController
{
    /**
     * @RequestMapping(path="getRecommendedFriend",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getRecommendedFriend()
    {
        try {
            return $this->response->success(FriendService::getRecommendedFriend(20));
        } catch (\Throwable $exception) {
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     *
     * @RequestMapping(path="createFriendGroup",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function createFriendGroup()
    {
        try {
            /**
             * @var \App\Model\User $user
             */
            $user            = $this->request->getAttribute('user');
            $friendGroupName = $this->request->input('friend_group_name');
            $result          = FriendService::createFriendGroup($user->id, $friendGroupName);
            return $this->response->success([
                'id'        => $result->id,
                'groupname' => $result->friend_group_name
            ]);
        } catch (\Throwable $exception) {
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @RequestMapping(path="search",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function searchFriend()
    {
        try {
            $keyword = $this->request->input('keyword');
            $page    = $this->request->input('page');
            $size    = $this->request->input('size');
            return $this->response->success(FriendService::searchFriend($keyword, (int)$page, (int)$size));
        } catch (\Throwable $exception) {
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @RequestMapping(path="apply",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function apply()
    {
        try {
            $user              = $this->request->getAttribute('user');
            $receiverId        = $this->request->input('receiver_id');
            $friendGroupId     = $this->request->input('friend_group_id');
            $applicationReason = $this->request->input('application_reason');
            return $this->response->success(FriendService::apply($user->id, (int)$receiverId, (int)$friendGroupId, (string)$applicationReason));
        } catch (\Throwable $exception) {
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @RequestMapping(path="agreeApply",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function agreeApply()
    {
        Db::beginTransaction();
        try {
            $userApplicationId = $this->request->input('user_application_id');
            $friendGroupId     = $this->request->input('friend_group_id');
            $result            = FriendService::agreeApply((int)$userApplicationId, (int)$friendGroupId);
            Db::commit();
            return $this->response->success($result);
        } catch (\Throwable $exception) {
            Db::rollBack();
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @RequestMapping(path="getChatHistory",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getChatHistory()
    {
        try {
            $user       = $this->request->getAttribute('user');
            $fromUserId = $this->request->input('from_user_id');
            $page       = $this->request->input('page');
            $size       = $this->request->input('size');
            return $this->response->success(FriendService::getChatHistory((int)$fromUserId, $user->id, (int)$page, (int)$size));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="info",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function friendInfo()
    {
        try {
            $userId   = $this->request->input('user_id');
            $userInfo = UserService::findUserInfoById((int)$userId);
            return $this->response->success($userInfo);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="refuseApply",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function refuseApply()
    {
        try {
            $userApplicationId = $this->request->input('user_application_id');
            FriendService::refuseApply((int)$userApplicationId);
            return $this->response->success($userApplicationId);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }
}
