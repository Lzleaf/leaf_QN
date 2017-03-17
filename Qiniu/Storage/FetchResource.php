<?php
namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Http\Client;
use Qiniu\Http\Error;

final class FetchResource {
	private $auth;

	public function __construct(Auth $auth) {
		$this->auth = $auth;
	}

	public function fetch($fromUrl, $dstFileName) {
		$url = Config::IO_HOST .'/fetch/'. \Qiniu\base64_urlSafeEncode($fromUrl) .'/to/'. \Qiniu\base64_urlSafeEncode($dstFileName);
		$headers = $this->auth->authorization($url);

		$ret = Client::post($url, null, $headers);
		if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        } else {
			return array($ret->json(), null);
		}
	}
}