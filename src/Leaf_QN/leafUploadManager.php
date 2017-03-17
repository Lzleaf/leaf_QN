<?php
/**
 * Created by PhpStorm.
 * User: leaf
 * Date: 2017/3/17
 * Time: 下午2:22
 */

namespace Leaf_QN;

use League\Flysystem\Exception;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class leafUploadManager
{
    protected $config;

    public function __construct()
    {
        require_once '../../../../qiniu/php-sdk/autoload.php';
        $this->config = require_once 'config.php';
    }

    public function get_upload_token($bucket_index, $policy, $expires = 3600)
    {
        try {
            $auth = new Auth($this->config['accessKey'], $this->config['secretKey']);
            $bucket = $this->config['bucket'][$bucket_index][0];

            $token = $auth->uploadToken($bucket, null, $expires, $policy);

            return ['code' => 200, 'msg' => $token];
        } catch (Exception $e) {
            return ['code' => 400, 'msg' => 'Undefined offset: ' . $bucket_index];
        }
    }

}