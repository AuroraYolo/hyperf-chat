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
use App\Exception\InputException;
use App\Service\FriendService;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\JwtAuthMiddleware;
use function App\Helper\checkAuth;

/**
 * Class UserController
 * @package App\Controller
 * @Controller(prefix="user")
 */
class UserController extends AbstractController
{

    /**
     * @Inject()
     * @var \Phper666\JWTAuth\JWT
     */
    private $auth;

    /**
     * @Inject()
     * @var \Hyperf\Validation\Contract\ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @RequestMapping(path="home",methods="GET")
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function home()
    {
        if (!$user = checkAuth()) {
            return $this->response->redirect('index/login');
        }
        $menus = config('menu');
        return $this->view->render('user/home', [
            'menus'      => $menus,
            'user'       => $user,
            'wsUrl'      => env('WS_URL'),
            'webRtcUrl'  => env('WEB_RTC_URL'),
            'stunServer' => 'stunServer'
        ]);
    }

    /**
     * 注册
     * @RequestMapping(path="register",methods="POST")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function register()
    {
        try {
            $email    = $this->request->input('email');
            $password = $this->request->input('password');
            $params   = $this->request->all();
            //------参数校验-----------//
            $validator = $this->validationFactory->make($params, [
                'email'    => 'required|email|max:50',
                'password' => 'required|string|max:50',
            ]);
            if ($validator->fails()) {
                $errorMessage = array_values($validator->errors()->all());

                throw new InputException(implode(',', $errorMessage));
            }
            return $this->response->success(UserService::register($email, $password));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * 登录
     * @RequestMapping(path="login",methods="POST")
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login()
    {
        try {
            $email    = $this->request->input('email');
            $password = $this->request->input('password');
            $user     = UserService::login($email, $password);
            $auth     = [
                'uid'      => $user->id,
                'username' => $user->email
            ];
            $token    = $this->auth->setScene('default')->getToken($auth);
            return $this->response
                ->withCookie(new Cookie('IM_TOKEN', $token, time() + $this->auth->getTTL(), '/', '', false, false,)
                )->json([
                    'data' => $user,
                    'code' => 0,
                    'msg'  => '登录成功',
                ]);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @RequestMapping(path="init",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function userInit()
    {
        try {
            /**
             * @var \App\Model\User $user
             */
            $user         = $this->request->getAttribute('user');
            $friend       = FriendService::getFriend($user->id);
            $group        = FriendService::getGroup($user->id);
            return $this->response->success([
                'mine'   => UserService::getMine($user),
                'friend' => $friend,
                'group'  => $group
            ]);
        } catch (\Throwable $exception) {
            return $this->response->error($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @RequestMapping(path="getApplication",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getApplication()
    {
        try {
            /**
             * @var \App\Model\User $user
             */
            $user   = $this->request->getAttribute('user');
            $page   = $this->request->input('page');
            $size   = $this->request->input('size');
            $result = UserService::getApplication($user->id, (int)$page, (int)$size);
            return $this->response->success($result);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="signOut",methods="GET")
     */
    public function signOut()
    {
        return $this->response->withCookie(new Cookie('IM_TOKEN', ''))->redirect(env('APP_URL').'/index/login');
    }

    /**
     * @RequestMapping(path="getUnreadApplicationCount",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getUnreadApplicationCount()
    {
        try {
            $user = $this->request->getAttribute('user');
            return $this->response->success(UserService::getUnreadApplicationCount($user->id));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="info",methods="GET")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function userInfo()
    {
        try {
            return $this->response->success($this->request->getAttribute('user'));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="changeUserNameAndAvatar",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function changeUserNameAndAvatar()
    {
        try {
            $user     = $this->request->getAttribute('user');
            $username = $this->request->input('username');
            $avatar   = $this->request->input('avatar');
            return $this->response->success(UserService::changeUserNameAndAvatar($user->id, $username, $avatar));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="setSign",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function setSign()
    {
        try {
            $user = $this->request->getAttribute('user');
            $sign = $this->request->input('sign');
            return $this->response->success(UserService::setSign($user->id, $sign));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="setStatus",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function setStatus()
    {
        try {
            $user   = $this->request->getAttribute('user');
            $status = $this->request->input('status');
            return $this->response->success(UserService::setUserStatus($user->id, (int)$status));
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

}
