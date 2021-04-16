<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyEnum extends MyCLabs\Enum\Enum{
	public static function getItems(){
		return array();
	}

	public static function in($search_key){
		$items = static::getItems();
		foreach($items as $key=>$value){
			if($key == $search_key){
				return true;
			}
		}
		return false;
	}

	public static function is($x, $y){
		return ($x == $y) ? true:false;
	}

	public static function _print($search_key){
		$items = static::getItems();
		foreach($items as $key=>$value){
			if($key == $search_key){
				return $value;
			}
		}

		if(defined('static::CASE_ELSE')){
			return static::CASE_ELSE;
		}else{
			return $search_key;
		}
	}

	public static function _style($search_key){
		$items = static::getStyles();
		foreach($items as $key=>$value){
			if($key == $search_key){
				return $value;
			}
		}

		if(defined('static::CASE_ELSE')){
			return static::CASE_ELSE;
		}else{
			return $search_key;
		}
	}

	public static function printChecked($key_x, $key_y){
		if($key_x == $key_y){
			return "checked";
		}else{
			return "";
		}
	}

	public static function printSelected($key_x, $key_y){
		if($key_x == $key_y){
			return "selected";
		}else{
			return "";
		}
	}
}

class MyEnumBit extends MyCLabs\Enum\Enum{
	public static function getItems(){
		return array();
	}

	public static function _print($search_key){
		$items = static::getItems();
		foreach($items as $key=>$value){
			if($key == $search_key){
				return $value;
			}
		}

		if(defined('static::CASE_ELSE')){
			return static::CASE_ELSE;
		}else{
			return $search_key;
		}
	}

	public static function in($search_key){
		$items = static::getItems();
		foreach($items as $key=>$value){
			if(self::is($key, $search_key)){
				return true;
			}
		}
		return false;
	}

	public static function is($x, $y){
		return (((int)$x & (int)$y) > 0) ? true:false;
	}

	public static function iss($val){
		$arr = array();

		$items = static::getItems();
		foreach($items as $key=>$value){
			if(self::is($key, $val)){
				array_push($arr, $value);
			}
		}
		
		return $arr;
	}

	public static function printiss($val){
		$items = self::iss($val);

		$str = "";
		foreach($items as $key=>$value){
			$str .= $value.", ";
		}

		$str = preg_replace("/[,]$/i", "", trim($str));
		return $str;
	}

	public static function printChecked($key_x, $key_y){
		if(self::is($key_x, $key_y)){
			return "checked";
		}else{
			return "";
		}
	}

	public static function printSelected($key_x, $key_y){
		if(self::is($key_x, $key_y)){
			return "selected";
		}else{
			return "";
		}
	}
}

class ENUM_MC_MODE extends MyEnum{
	const INSERT = "insert";
	const MODIFY = "modify";

	public static function getItems(){
		return array(
			self::INSERT => "작성",
			self::MODIFY => "수정"
		);
	}
}

class ENUM_MC_DEVICE_TYPE extends MyCLabs\Enum\Enum{
	const PC      = "PC";
	const MOBILE  = "MOBILE";
	const ANDROID = "ANDROID";
	const IOS     = "IOS";
	const ETC     = "ETC";
}

class ENUM_MC_VIEW_TYPE extends MyCLabs\Enum\Enum{
	const PC     = "pc";
	const MOBILE = "mobile";
}

class ENUM_USER_STATE extends MyEnum{
    public static function getItems(){
        return array(
            0 => "대기",
            1 => "가입",
            8 => "회원탈퇴",
            9 => "괸리자탈퇴"
        );
    }
}

class ENUM_POINT_TYPE extends MyEnum{
    const P_1001 = '1001';
    const P_2001 = '2001';

    public static function getItems(){
        return array(
            self::P_1001 => "콘텐츠 구매",
            self::P_2001 => "적립"
        );
    }
}

?>