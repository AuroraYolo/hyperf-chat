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
use App\Service\GroupService;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\JwtAuthMiddleware;

/**
 * Class GroupController
 * @package App\Controller\Http
 * @Controller(prefix="group")
 */
class GroupController extends AbstractController
{

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @RequestMapping(path="create",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function createdGroup()
    {

        $user         = $this->request->getAttribute('user');
        $groupName    = $this->request->input('group_name');
        $avatar       = $this->request->input('avatar');
        $size         = $this->request->input('size');
        $introduction = $this->request->input('introduction');
        $validation   = $this->request->input('validation');
        return $this->response->success(GroupService::createGroup($user->id, $groupName, $avatar, (int)$size, $introduction, (int)$validation));
    }

    /**
     * @RequestMapping(path="getGroupRelation",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getGroupRelation()
    {

        $groupId = $this->request->input('id');
        return $this->response->success(GroupService::getGroupRelationById((int)$groupId));
    }

    /**
     * @RequestMapping(path="getRecommendedGroup",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getRecommendedGroup()
    {
        try {
            return $this->response->success(GroupService::getRecommendedGroup(20));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="search",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function searchGroup()
    {

        $keyword = $this->request->input('keyword');
        $page    = $this->request->input('page');
        $size    = $this->request->input('size');
        return $this->response->success(GroupService::searchGroup($keyword, $page, $size));
    }

    /**
     * @RequestMapping(path="apply",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function apply()
    {

        $userId            = $this->request->getAttribute('user')->id;
        $groupId           = $this->request->input('group_id');
        $applicationReason = $this->request->input('application_reason');
        $result            = GroupService::apply((int)$userId, (int)$groupId, $applicationReason);
        $msg               = empty($result) ? '等待管理员验证 !' : '你已成功加入此群 !';
        return $this->response->success($result, 0, $msg);
    }

    /**
     * @RequestMapping(path="info",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function groupInfo()
    {

        $groupId = $this->request->input('group_id');
        return $this->response->success(GroupService::findGroupById((int)$groupId));
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
            $result            = GroupService::agreeApply((int)$userApplicationId);
            Db::commit();
            return $this->response->success($result);
        } catch (\Throwable $throwable) {
            Db::rollBack();
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="refuseApply",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function refuseApply()
    {

        $userApplicationId = $this->request->input('user_application_id');
        GroupService::refuseApply((int)$userApplicationId);
        return $this->response->success($userApplicationId);
    }

    /**
     * @RequestMapping(path="getChatHistory",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getChatHistory()
    {

        $toGroupId = $this->request->input('to_group_id');
        $page      = $this->request->input('page');
        $size      = $this->request->input('size');
        return $this->response->success(GroupService::getChatHistory((int)$toGroupId, (int)$page, (int)$size));
    }
}
