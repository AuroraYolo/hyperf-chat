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
use App\Exception\ApiException;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\JwtAuthMiddleware;

/**
 * Class UtilController
 * @package App\Controller\Http
 * @Controller(prefix="util")
 */
class UtilController extends AbstractController
{
    /**
     * @RequestMapping(path="uploadImg",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     * @param \League\Flysystem\Filesystem
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function uploadImg(\League\Flysystem\Filesystem $filesystem)
    {
        try {
            $file = $this->request->file('file');
            if (!$file) {
                throw new ApiException(400, 'FILE_DOES_NOT_EXIST');
            }
            $size = $file->getSize();
            if ($size / 1024 / 1024 > 10) {
                throw new ApiException(400, '文件大小超过10M');
            }
            $extName = $file->getExtension();

            $dir     = BASE_PATH . '/public/storage/upload/';
            $dirName = date('Ymd') . '/';
            $dir     = $dir . $dirName;
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $fileName = time() . rand(1, 999999);
            $path     = $dir . $fileName . '.' . $extName;

            $file->moveTo($path);
            return $this->response->success([
                'src' => env('STORAGE_IMG_URL') . $dirName . $fileName . '.' . $extName
            ]);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * @RequestMapping(path="uploadFile",methods="POST")
     * @Middleware(JwtAuthMiddleware::class)
     * @param \League\Flysystem\Filesystem
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function uploadFile()
    {
        try {
            $file = $this->request->file('file');
            if (!$file) {
                throw new ApiException(400, 'FILE_DOES_NOT_EXIST');
            }
            $size = $file->getSize();
            if ($size / 1024 / 1024 > 10) {
                throw new ApiException(400, '文件大小超过10M');
            }
            $extName = $file->getExtension();

            $dir     = BASE_PATH . '/public/file/upload/';
            $dirName = date('Ymd') . '/';
            $dir     = $dir . $dirName;
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $fileName = time() . rand(1, 999999);
            $path     = $dir . $fileName . '.' . $extName;

            $file->moveTo($path);
            return $this->response->success([
                'src' => env('STORAGE_FILE_URL') . $dirName . $fileName . '.' . $extName
            ]);
        } catch (\Throwable $throwable) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
    }
}
