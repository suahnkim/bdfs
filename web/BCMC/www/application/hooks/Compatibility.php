<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Compatibility{
	public function initialize(){
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])){ // CloudFlare
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){ // UcloudBiz
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')){
			define('HTTP_REQUEST_SCHEME', 'https');
		}else{
			define('HTTP_REQUEST_SCHEME', 'http');
		}
	}
}
?>