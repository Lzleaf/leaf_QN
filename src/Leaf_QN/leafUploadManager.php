<?php
/**
 * Created by PhpStorm.
 * User: leaf
 * Date: 2017/3/17
 * Time: 下午2:22
 */

namespace Leaf_QN;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

class leafUploadManager
{
    protected $config;

    public function __construct()
    {
        $this->config = require_once "config.php";
    }

    protected function getAccess()
    {
        return $this->config['accessKey'];
    }

    protected function getSecret()
    {
        return $this->config['secretKey'];
    }

    protected function getBucket($bucket_index, $bucket_type = 'key')
    {
        if ($bucket_type == 'key') {
            return isset($this->config['bucket'][$bucket_index][0]) ? $this->config['bucket'][$bucket_index][0]
                : false;
        } elseif ($bucket_type == 'value') {
            return isset($this->config['bucket'][$bucket_index][1]) ? $this->config['bucket'][$bucket_index][1]
                : false;
        }
    }

    public function getUploadToken($bucket_index, $policy = null, $expires = 3600)
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucket = $this->getBucket($bucket_index);
        if (!$bucket) {
            return ['code' => 400, 'msg' => 'Undefined offset: ' . $bucket_index];
        }
        $token = $auth->uploadToken($bucket, null, $expires, $policy);
        return ['code' => 200, 'msg' => $token];
    }

    public function getDownloadToken($bucket_index, $resource_key)
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucket_value = $this->getBucket($bucket_index, 'value');
        if (!$bucket_value) {
            return ['code' => 400, 'msg' => 'Undefined offset: ' . $bucket_index];
        }
        $baseUrl = $bucket_value . "/" . $resource_key;
        $authUrl = $auth->privateDownloadUrl($baseUrl);
        return ['code' => 200, 'msg' => $authUrl];
    }

    public function getFileMsg($bucket_index, $resource_key)
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucketMgr = new BucketManager($auth);
        $bucket = $this->getBucket($bucket_index);
        list($ret, $err) = $bucketMgr->stat($bucket, $resource_key);
        if ($err !== null) {
            return ['code' => 400, 'msg' => $err];
        } else {
            return ['code' => 200, 'msg' => $ret];//fsize hash mimeType putTime
        }
    }

    /**
     * @param $bucket1_index 测试空间
     * @param $bucket2_index 移动空间
     * @param $key1 测试空间key
     * @param $key2 移动空间key
     * @return array
     */
    public function moveSingleFile($bucket1_index, $bucket2_index, $key1, $key2)
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucketMgr = new BucketManager($auth);
        $bucket1 = $this->getBucket($bucket1_index);
        $bucket2 = $this->getBucket($bucket2_index);
        $err = $bucketMgr->move($bucket1, $key1, $bucket2, $key2);
        if ($err !== null) {
            return ['code' => 400, 'msg' => $err];
        } else {
            return ['code' => 200, 'msg' => 'success'];
        }
    }

    public function delSingleFile($bucket_index, $resource_key)
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucketMgr = new BucketManager($auth);
        $bucket = $this->getBucket($bucket_index);
        $err = $bucketMgr->delete($bucket, $resource_key);
        if ($err !== null) {
            return ['code' => 400, 'msg' => $err];
        } else {
            return ['code' => 200, 'msg' => 'success'];
        }
    }

    /**
     * @param string $bucket_index     空间名
     * @param string $limit      单次列举个数限制
     * @param string $prefix     列举前缀
     * @param string $marker     列举标识
     * @param string $delimiter  指定目录分隔符
     * @return array
     */
    public function getListFiles($bucket_index, $limit, $prefix = '', $marker = '', $delimiter = '')
    {
        $auth = new Auth($this->getAccess(), $this->getSecret());
        $bucketMgr = new BucketManager($auth);
        $bucket = $this->getBucket($bucket_index);
        list($items, $marker, $err) = $bucketMgr->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
        if ($err !== null) {
            return ['code' => 400, 'msg' => $err];
        } else {
            return ['code' => 200, 'msg' => ['marker' => $marker, 'items' => $items]];
        }
    }
}