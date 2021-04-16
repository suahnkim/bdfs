<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Utils{
	public function __construct(){ }
	public static function getRandomString($mode, $scale){
		switch($mode){
			case "0":
				$keys = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890";
				break;
			case "1":
				$keys = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
				break;
			case "2":
				$keys = "01234567890";
				break;
			case "3":
				$keys = "ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890";
				break;
			case "4":
				$keys = "abcdefghijklmnopqrstuvwxyz01234567890";
				break;
			default:
				$keys = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890";
				break;
		}
		
		$token = "";
		for($i = 0; $i < $scale; $i++){
			$token .= $keys[mt_rand(0, strlen($keys) - 1)];
		}
		return strtolower($token);
	}

	public static function createCSRPToken(){
		$keys = "A0N1B2A3C4N5D6U7E8M9F0LGOHTITJOKLMNOPQRSTUVWXYZ";
		$token = "";

		for($i = 0; $i < 20; $i++){
			$token .= $key[mt_rand(0, strlen($key) - 1)];
		}
		
		$this->session->set_userdata(SESSION_CSRP_TOKEN, $token);
		return $token;
	}

	public static function checkCSRPToken($token){
		if($this->session->userdata(SESSION_CSRP_TOKEN) == $token){
			return true;
		}

		return false;
	}

	public static function checkCSRPTokenOne($token){
		if($this->session->userdata(SESSION_CSRP_TOKEN) == $token){
			$this->session->unset_userdata(SESSION_CSRP_TOKEN);
			return true;
		}

		return false;
	}

	public static function tag2Text($value){
		if($value === null){
			$value = "";
		}

		$value = str_replace("&", "&amp;", $value);
		$value = str_replace("<", "&lt;", $value);
		$value = str_replace(">", "&gt;", $value);

		return $value;
	}

	public static function getallheaders($type = 'array'){
		if($type == 'array'){
			if(!function_exists('getallheaders')){
				$headers = '';
				foreach ($_SERVER as $name => $value){
					if(substr($name, 0, 5) == 'HTTP_'){
						$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
					}
				}
				return $headers;
			}else{
				return getallheaders();
			}
		}elseif($type == 'string'){
			$headers_string = "";
			$headers = self::getallheaders();
			foreach ($headers as $name => $value){
				$headers_string .= $name.":".$value."\n";
			}
			return $headers_string;
		}
	}

	public static function onMenu($url, $text = "on"){
		if(strpos(strtolower(@$_SERVER['PATH_INFO']), $url) === 0){
			return $text;
		}		
		return null;
	}

	public static function cleanXSS($data, $newline2br = FALSE){
		$return_str = $data; // HtmlEntities
		$return_str = htmlspecialchars($return_str);

		if($newline2br === TRUE){
			$return_str = str_replace("\n","<br>",$return_str);
		}
		$return_str = str_replace("&lt;br /&gt;", "<br>", $return_str);
		$return_str = str_replace("&lt;br&gt;", "<br>", $return_str);
		
		return $return_str;
	}

	public static function customizeResponse($status = null, $code = null, $message = null, $data = null){
		$response_obj = new stdClass();
		$response_obj->status  = ($status == null)  ? 500:$status;
		$response_obj->code    = ($code == null)    ? 500:$code;
		if($message != null) $response_obj->message = $message;
		if($data != null)    $response_obj->data    = $data;

		return $response_obj;
	}

	public static function goMessageUrl($message = null, $url = "/"){
		$html = "<script language='javascript'>";
		if(strlen($message) > 0) $html .= "alert('".$message."');";
		switch($url){
			case "new_window":
				$html .= "self.close();";
				break;
			default:
				$html .= "location.href = '".$url."'";
				break;
		}
		$html .= "</script>";
		echo($html);
		exit;
	}

	public static function makeFileLink($data){
		if(isset($data->file_domain) && isset($data->file_path) && strlen($data->file_domain) > 0){
			return "http://".$data->file_domain."/".$data->file_path;
		}else{
			return '';
		}
	}

	public static function makeImageLink($data){
		if(isset($data->image_domain) && isset($data->image_path) && strlen($data->image_domain) > 0){
			return "http://".$data->image_domain."/".$data->image_path;
		}else{
			return '';
		}
	}
	
	public static function file_size($size) {    
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB"); 
		if($size == 0){ 
			return('0 KB'); 
		}else{ 
			return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); 
		} 
	}
    
/** 글자자르기 */
	public static function cut_str($str = '', $len = '', $suffix = '…'){
		$arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
		$str_len = count($arr_str);
		if($str_len >= $len){
			$slice_str = array_slice($arr_str, 0, $len);
			$str = join('', $slice_str);
			return $str . ($str_len > $len ? $suffix : '');
		}else{
			$str = join('', $arr_str);
			return $str;
		}
	}        

	public static function makeSelectBox($Dic, $selected = "", $topText = "", $topValue = ""){
		$html = "";
		if(is_array($Dic)){
			$html = "";
			if($topText != ""){
				$html .= "<option value=\"".$topValue."\">".$topText."</option>\n";
			}
			foreach($Dic as $key => $value){
				if(strpos($key, "<optgroup") !== FALSE){
					$html .= "<optgroup label=\"".$value."\"></optgroup>\n";
				}else{
					if(strlen($selected) <= 0){
						$html .= "<option value=\"".$key."\">".$value."</option>\n";
					}else{
						$html .= "<option value=\"".$key."\"".(($key == $selected) ? " selected":"").">".$value."</option>\n";
					}					
				}
			}
		}
		return $html;
	}

	public static function create_hash($password, $force_compat = false){
		if(function_exists('mcrypt_create_iv')){
			$salt = base64_encode(mcrypt_create_iv(PBKDF2_COMPAT_SALT_BYTES, MCRYPT_DEV_URANDOM));
		}elseif(file_exists('/dev/urandom') && $fp = @fopen('/dev/urandom', 'r')){
			$salt = base64_encode(fread($fp, PBKDF2_COMPAT_SALT_BYTES));
		}else{
			$salt = '';
			for($i = 0; $i < PBKDF2_COMPAT_SALT_BYTES; $i += 2){
				$salt .= pack('S', mt_rand(0, 65535));
			}
			$salt = base64_encode(substr($salt, 0, PBKDF2_COMPAT_SALT_BYTES));
		}
		$salt = "";
		
		$algo = strtolower(PBKDF2_COMPAT_HASH_ALGORITHM);
		$iterations = PBKDF2_COMPAT_ITERATIONS;
		
		if($force_compat || !function_exists('hash_algos') || !in_array($algo, hash_algos())){
			$algo = false;
			$iterations = round($iterations / 5);
		}
		
		$pbkdf2 = self::pbkdf2_default($algo, $password, $salt, $iterations, PBKDF2_COMPAT_HASH_BYTES);
		$prefix = $algo ? $algo : 'sha1';
		return $prefix . ':' . $iterations . ':' . $salt . ':' . base64_encode($pbkdf2);
	}
	
	public static function validate_password($password, $hash){
		$params = explode(':', $hash);
		if(count($params) < 4) return false;
		
		$pbkdf2 = base64_decode($params[3]);
		$pbkdf2_check = pbkdf2_default($params[0], $password, $params[2], (int)$params[1], strlen($pbkdf2));
		return slow_equals($pbkdf2, $pbkdf2_check);
	}
	
	public static function needs_upgrade($hash){
		$params = explode(':', $hash);
		if(count($params) < 4) return true;
		$algo = $params[0];
		$iterations = (int)$params[1];
		
		if(!function_exists('hash_algos') || !in_array($algo, hash_algos())){
			return false;
		}elseif($algo === strtolower(PBKDF2_COMPAT_HASH_ALGORITHM) && $iterations >= PBKDF2_COMPAT_ITERATIONS){
			return false;
		}else{
			return true;
		}
	}
	
	public static function slow_equals($a, $b){
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++){
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0; 
	}
	
	public static function pbkdf2_default($algo, $password, $salt, $count, $key_length){
		if($count <= 0 || $key_length <= 0){
			trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
		}
		
		if(!$algo) return pbkdf2_fallback($password, $salt, $count, $key_length);
    
		$algo = strtolower($algo);
		if(!function_exists('hash_algos') || !in_array($algo, hash_algos())){
			if($algo === 'sha1'){
				return pbkdf2_fallback($password, $salt, $count, $key_length);
			}else{
				trigger_error('PBKDF2 ERROR: Hash algorithm not supported.', E_USER_ERROR);
			}
		}
		if(function_exists('hash_pbkdf2')){
			return hash_pbkdf2($algo, $password, $salt, $count, $key_length, true);
		}
		$hash_length = strlen(hash($algo, '', true));
		$block_count = ceil($key_length / $hash_length);
		$output = '';
		for($i=1;$i<=$block_count; $i++){
			$last = $salt.pack('N', $i);
			$last = $xorsum = hash_hmac($algo, $last, $password, true);
			for($j=1;$j<$count; $j++){
				$xorsum ^= ($last = hash_hmac($algo, $last, $password, true));
			}
			$output .= $xorsum;
		}
		return substr($output, 0, $key_length);
	}
	
	public static function pbkdf2_fallback($password, $salt, $count, $key_length){
		$hash_length = 20;
		$block_count = ceil($key_length / $hash_length);
		if(strlen($password) > 64){
			$password = str_pad(sha1($password, true), 64, chr(0));
		}else{
			$password = str_pad($password, 64, chr(0));
		}
		
		$opad = str_repeat(chr(0x5C), 64) ^ $password;
		$ipad = str_repeat(chr(0x36), 64) ^ $password;
		
		$output = '';
		for($i=1;$i<=$block_count;$i++){
			$last = $salt.pack('N', $i);
			$xorsum = $last = pack('H*', sha1($opad . pack('H*', sha1($ipad . $last))));
			for($j = 1; $j < $count; $j++){
				$last = pack('H*', sha1($opad . pack('H*', sha1($ipad . $last))));
				$xorsum ^= $last;
			}
			$output .= $xorsum;
		}
		return substr($output, 0, $key_length);
	}
}
?>
