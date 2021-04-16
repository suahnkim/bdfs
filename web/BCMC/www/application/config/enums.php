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

    public static function _print_exam($search_key){
        $items = static::getItems_exam();

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


class ENUM_ACCOUNT_TYPE extends MyEnum{
    const   MASTER = "MASTER";

    const   CP  = "CP";
    const   SP  = "SP";
    const   D  = "D";

    public static function getItems(){
        return array(
            self::MASTER => "관리자",

            self::CP => "생산자",
            self::D => "유통업자",
            self::SP => "스토리지노드"
        );
    }

    public static function getItems_exam(){
        return array(
            self::MASTER => "",
            self::CP => "콘텐츠 저작권 소유자로서<br>블록체인 미디어 관리 센터에 <br>콘텐츠를 등록합니다",
            //self::CP => "콘텐츠 제공자",
            self::D => "저작권자가 등록한 콘텐츠를<br> 미디어 유통 사이트에 유통합니다.<br>",
            self::SP => "저작권자가 등록한 콘텐츠를<br> PC에 저장하여 다운로드 <br>이용자에게 공유합니다",
        );
    }
}

?>