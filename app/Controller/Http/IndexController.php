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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class IndexController
 * @package App\Controller
 * @Controller(prefix="index")
 */
class IndexController extends AbstractController
{

    /**
     * @RequestMapping(path="login",methods="GET")
     */
    public function login()
    {
        return $this->view->render('user/login');
    }

    /**
     * @RequestMapping(path="register",methods="GET")
     */
    public function register()
    {
        return $this->view->render('user/register');
    }

    /**
     * @RequestMapping(path="createFriendGroup",methods="GET")
     */
    public function createFriendGroup()
    {
        return $this->view->render('friend/createGroup');
    }

    /**
     * @RequestMapping(path="createGroup",methods="GET")
     */
    public function createGroup()
    {
        return $this->view->render('group/create');
    }

    /**
     * @RequestMapping(path="findUser",methods="GET")
     */
    public function findUser()
    {
        return $this->view->render('friend/find');
    }

    /**
     * @RequestMapping(path="userInfo",methods="GET")
     */
    public function userInfo()
    {
        return $this->view->render('user/info');
    }

    /**
     * @RequestMapping(path="friendInfo",methods="GET")
     */
    public function friendInfo()
    {
        return $this->view->render('friend/info');
    }

    /**
     * @RequestMapping(path="groupInfo",methods="GET")
     */
    public function groupInfo()
    {
        return $this->view->render('group/info');
    }

    /**
     * @RequestMapping(path="application",methods="GET")
     */
    public function msgBox()
    {
        return $this->view->render('user/application');
    }

    /**
     * @RequestMapping(path="history",methods="GET")
     */
    public function chatLog()
    {
        return $this->view->render('user/history');
    }

    /**
     * @RequestMapping(path="about",methods="GET")
     */
    public function about()
    {
        return $this->view->render('chat/about');
    }

    /**
     * @RequestMapping(path="friendRoom",methods="GET")
     */
    public function friendRoom()
    {
        return $this->view->render('friend/room');
    }
    /**
     * @RequestMapping(path="findGroup",methods="GET")
     */
    public function findGroup()
    {
        return $this->view->render('group/find');
    }
}
