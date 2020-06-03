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
use Hyperf\View\RenderInterface;

/**
 *
 */
class HomeController extends AbstractController
{
    /**
     * @param \Hyperf\View\RenderInterface $render
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     */
    public function index(RenderInterface $render)
    {
        return $render->render('/home/index');
    }
}
