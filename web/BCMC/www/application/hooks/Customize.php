<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customize{
	public function initialize(){
		define('SITE_DOMAIN', @$_SERVER['SERVER_NAME'].((@$_SERVER['SERVER_PORT'] == 80) ? '':(":".@$_SERVER['SERVER_PORT'])));
		define('GET_DOMAIN', str_replace("www.", "", SITE_DOMAIN));
		define('SITE_FULL_URL', SITE_DOMAIN.@$_SERVER['REQUEST_URI']);
		define('MC_DEVICE_TYPE', $this->getDevice());
		$this->setLayout(MC_DEVICE_TYPE);
	}

	private function setLayout($device){
		define('COM_ASSETS_PATH', '/assets/common');
		define('COM_VIEWS_PATH', 'common');
        define('MC_ASSETS_PATH', '/assets/');
        define('MC_VIEWS_PATH', '');
	}

	private function getDevice(){
		if(preg_match('/AndroidAPP/', @$_SERVER['HTTP_USER_AGENT'])){
			return ENUM_MC_DEVICE_TYPE::ANDROID;
		}elseif(preg_match('/iOSAPP/', @$_SERVER['HTTP_USER_AGENT'])){
			return ENUM_MC_DEVICE_TYPE::IOS;
		}elseif(preg_match('/(iPhone|Android|iPod|iPad|BlackBerry|IEMobile|HTC|Server_KO_SKT|SonyEricssonX1|SKT)/', @$_SERVER['HTTP_USER_AGENT'])){
			return ENUM_MC_DEVICE_TYPE::MOBILE;
		}else{
			return ENUM_MC_DEVICE_TYPE::PC;
		}
	}
}