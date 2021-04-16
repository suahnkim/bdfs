<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class RestAPI{
	var $rest_api = "";
	var $access_token = "";
	var $language = "ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3";
	
	public function __construct($url = null, $access_token = null){
		if($url !== null){
			$this->rest_api = $url;
		}
		if($access_token !== null){
			$this->access_token = $access_token;
		}
	}
	
	public function setAccessToken($access_token){
		$this->access_token = $access_token;
	}
	
	public function setLanguage($language){
		$this->language = $language;
	}
	
	/*
	URL에 \ / 제한
	
	data 형식
	array(
		header => array()
		, url => array(key=>value)
		, querystring => array(key=>value)
		, data => array(key=>value)
	);

	url은 path에서 /{LOVE}/1로 되어있으면, url => array("LOVE"=>"사랑")이면  예 /사랑/1로 변환됨
	querystring는 어떤 메소드에 상관없이 URL에 ?key=value&key=value 식으로 가는것
	data 는 메소드에 맞춰서 데이터가 알아서 가는 것 보통 여기에 데이터 넣으면 끝
	header는 강제로 헤더 세팅하는거니까 왠만하면 안건드리고 추가 안하는 것을 권장
	access_token는 엑세스 토큰을 해당 통신에서 쏠때만 세팅 없으면 기본 글로벌 토큰으로 전송 Bearer는 자동으로 들어가니 토큰값만
	*/
	
	/**
	 * GET 전송
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function get($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "GET", $data);
	}
	
	/**
	 * POST 전송
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function post($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "POST", $data);
	}
	
	/**
	 * PUT 전송 querystring, data 같이 보내면 안됨. 줄중 하나만 쓸것
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function put($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "PUT", $data);
	}
	
	/**
	 * DELETE 전송
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function delete($rest_path, $data = null){
		return $this->restapi($rest_path, "DELETE", $data);
	}

	
	/**
	 * DELETE 전송
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function options($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "OPTIONS", $data);
	}
	
	/**
	 * DELETE Object Storage (스토리지 파일삭제)
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function deleteStorageObject($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "DELETESTORAGEOBJECT", $data);
	}

	/**
	 * Put Object Storage (스토리지 파일 업로드)
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	public function putStorageObject($rest_path, $data = null)
	{
		return $this->restapi($rest_path, "PUTSTORAGEOBJECT", $data);
	}
	
	/**
	 * Base64 URL Encode
	 * @param  [type] $input Base64 문자열
	 * @return [type]        [description]
	 */
	public function base64encode($input)
	{
		return strtr(base64_encode($input), '+/=', '-_.');
	}
	
	/**
	 * Base64 URL Decode
	 * @param  [type] $input Base64URLEncode된 문자열
	 * @return [type]        [description]
	 */
	public function base64decode($input)
	{
		return base64_decode(strtr($input, '-_.', '+/='));
	}
	
	/**
	 * REST API
	 * @param  [type] $rest_path api경로(URL)
	 * @param  [type] $method    GET, POST, DELETE, PUT, PUTSTORAGEOBJECT, DELETESTORAGEOBJECT
	 * @param  [type] $data      array( header => array(), url => (key=>value), querystring => array(key=>value), data => array(key=>value) ) // header, url, querystring, data 모두 생략가능 필수 아님
	 * @return [type]            [description]
	 */
	private function restapi($rest_path, $method, $data = null){ // null 파라메터 제거
		if(isset($data['url']) && count($data['url']) > 0){
			foreach($data['url'] as $key => $value){
				if($value === null) unset($data['url'][$key]);
			}
		}
		if(isset($data['querystring']) && count($data['querystring']) > 0){
			foreach($data['querystring'] as $key => $value){
				if($value === null) unset($data['querystring'][$key]);
			}
		}		
		if(isset($data['data']) && is_array($data['data']) && count($data['data']) > 0){
			foreach($data['data'] as $key => $value){
				if($value === null) unset($data['data'][$key]);
			}
		}
		$headers = array( // 헤더 설정
			"Content-Type"=>"application/x-www-form-urlencoded"
			, "Accept-Language"=>$this->language
		);
		if(isset($data['header']) && is_array($data['header'])){
			foreach($data['header'] as $key=>$value){
				$headers[$key] = $value;
			}
		}
		if(isset($data['access_token'])){
			$headers['Authorization'] = "Bearer ".$data['access_token'];
		}elseif($this->access_token != null && $this->access_token != ""){
			$headers['Authorization'] = "Bearer ".$this->access_token;
		}		
		$url = (strpos($rest_path, "http://") !== false || strpos($rest_path, "https://") !== false) ? $rest_path : $this->rest_api.$rest_path; // 기본 URL
		if(isset($data['url']) && count($data['url']) > 0){
			foreach($data['url'] as $key => $value) $url = str_replace("{".$key."}", $value, $url);
		}
		if(isset($data['querystring']) && count($data['querystring']) > 0){
			$url .= "?".http_build_query($data['querystring']);
		}
		$headers_parse = array();
		foreach($headers as $key => $value) array_push($headers_parse, $key.": ".$value);
		if(isset($data['data']) && is_array($data['data']) && count($data['data']) <= 0){
			array_push($headers_parse, "Content-Length: 0");
		}		
		$aCURLOPTs = array(
			CURLOPT_HEADER => TRUE //결과 값에 통신 header 출력 여부
			, CURLOPT_HTTPHEADER => $headers_parse
			, CURLOPT_FRESH_CONNECT => TRUE //연결 방식 (연결 시 cache 있을 시 사용 한다면 - 0 , 아니면(새로연결)- 1)
			, CURLOPT_RETURNTRANSFER => TRUE //return 된 결과물에 따른 처리방식(ex: 1-변수저장, 0-출력)
			, CURLOPT_SSL_VERIFYPEER => FALSE //https 사용 여부
			, CURLOPT_SSL_VERIFYHOST => FALSE //https 호스트 무시
			, CURLOPT_FOLLOWLOCATION => TRUE // 리다이렉션 추적
			, CURLOPT_ENCODING => "gzip, deflate, br"
			//, CURLOPT_SSLVERSION => 1
			, CURLOPT_TIMEOUT => 300
		);
		
		$ch = curl_init();
		curl_setopt_array($ch, ($aCURLOPTs));
		
		switch(strtoupper($method)){
			case "POST":
				curl_setopt($ch, CURLOPT_POST, TRUE);
				break;				
			case "DELETESTORAGEOBJECT":
			case "DELETE":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			case "OPTIONS":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
				break;
			case "PUT":
				if(isset($data['querystring']) && count($data['querystring']) > 0 && (!isset($data['data']) || count($data['data']) == 0)){
				 	curl_setopt($ch, CURLOPT_PUT, TRUE);
				}
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				break;
			case "PUTSTORAGEOBJECT":
				curl_setopt($ch, CURLOPT_PUT, TRUE);
				break;				
			case "GET":
				//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				break;
		}
		if(isset($data['data']) && ((is_array($data['data']) && count($data['data']) > 0) || is_string($data['data']))){
			switch(strtoupper($method)){
				case "DELETESTORAGEOBJECT":
				case "POST":
				case "DELETE":
				case "OPTIONS":
				case "PUT":
					if(isset($headers['Content-Type']) && ($headers['Content-Type'] == "application/json" || $headers['Content-Type'] == "text/json")){
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
					}else{
						if(is_array($data['data']) && count($data['data']) > 0){
							curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data['data']));
						}
					}
					break;					
				case "PUTSTORAGEOBJECT":
					$fp = fopen ($data['data'], "r");
					curl_setopt($ch, CURLOPT_INFILE, $fp);
					curl_setopt($ch, CURLOPT_INFILESIZE, filesize($data['data']));
					break;					
				case "GET":
					if(isset($data['querystring']) && count($data['querystring']) > 0){
						$url .= '&'.http_build_query($data['data'], '', '&');
					}else{
						$url .= '?'.http_build_query($data['data'], '', '&');
					}
					break;				
			}
		}		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		//curl_setopt($ch, CURLOPT_STDERR, fopen($_SERVER['DOCUMENT_ROOT']."/logs/_restapi.txt", "a"));
		try{
			$response = curl_exec($ch);
			$response_info = curl_getinfo($ch);
			if(class_exists('FirePHP')){
				$tmp = FirePHP::getInstance(true);
				$tmp->setEnabled(TRUE);
				$tmp->log('CURL RES');
				$tmp->log($response);
			}			
		}catch (exception $e){	
			if(class_exists('FirePHP')){
				$tmp = FirePHP::getInstance(true);
				$tmp->setEnabled(TRUE);
				$tmp->log('CURL');
				$tmp->log($response['info']['http_code'] ? $response['info']['http_code'] : null);
			}
			error_log(print_r($e,true));
		}
		$curlHeaderSize=$response_info['header_size'];
		$response_headers = trim(substr($response, 0, $curlHeaderSize));
		$response_body = trim(substr($response, $curlHeaderSize));

		$response_original_headers = explode("\n", $response_headers);
		
		$response_headers = array();
		foreach($response_original_headers as $header){
			if(strpos($header, ":") !== false){
				list($header_name, $header_value) = explode(":", $header, 2);
				$response_headers[$header_name] = trim($header_value);
			}
		}
		
		//$response_headers['http_code'] = $response_info['http_code'];
		
		$result['request_header'] = $headers;
		$result['info'] = $response_info;
		$result['header'] = $response_headers;
		$result['data'] = $response_body;
		
		if(!isset($data['data'])) $data['data'] = $data['querystring'];

		if(class_exists('FirePHP')){
			$tmp = FirePHP::getInstance(true);
			$tmp->setEnabled(TRUE);
			$tmp->log("URL(".($method ? $method : null).") : ".print_r(@$url, true)."\n");
			$tmp->log($headers ? $headers : null);
			$tmp->log($data['data'] ? $data['data'] : null);
			$tmp->log($response_body ? $response_body : null);
			//$tmp->log($response['info']['http_code'] ? $response['info']['http_code'] : null);
		}
		curl_close($ch);
		if(isset($fp)){
			fclose($fp); 
		}
		return $result;
	}

	public static function returnResponse($response){
		$response_obj = new stdClass();
		$response_obj->status = (isset($response['info']['http_code'])) ? (int)$response['info']['http_code'] : 500;

		$json = @json_decode($response['data']);

		if(floor($json->code) > "8000" && floor($json->code) < "9000"){
			$response_obj->status = "200";
		}elseif(floor($json->code) == "403"){
			$response_obj->status = $json->code;
		}else{
			$response_obj->status = floor($json->code);
		}
		$response_obj->message = isset($json->message) ? $json->message : null;

		if($response_obj->status == 0){
			$response_obj->status = $response_obj->code = 500;
			$response_obj->message = "Do not connect to the destination.";
		}elseif($response_obj->status == 200){
			if($json->code == 200){
				$response_obj->code    = @$json->code;
				$response_obj->message = @$json->message;
				$response_obj->data    = (isset($json) && $json !== null) ? $json->data : $response['data']['data'];
			}else{
				$response_obj->code    = @$json->code;
				$response_obj->message = @$json->message;
				$response_obj->data    = null;
			}
		}elseif($response_obj->status == 204){
			$response_obj->status = 200;
			$response_obj->code    = 200;
			$response_obj->data    = null;
		}elseif($response_obj->status < 400){
			$response_obj->code    = null;
			$response_obj->message = null;
		}elseif($response_obj->status < 500){
			$response_obj->code    = @$json->code;
			$response_obj->message = @$json->message;
		}else{
			$response_obj->code    = -500;
			$response_obj->message = @$json->message;
		}

		return $response_obj;
	}

	public static function customizeResponse($status = null, $code = null, $message = null, $data = null){
		$response_obj = new stdClass();
		$response_obj->status  = ($status == null)  ? 500:$status;
		$response_obj->code    = ($code == null)    ? 500:$code;
		if($message != null) $response_obj->message = $message;
		if($data != null)    $response_obj->data    = $data;

		return $response_obj;
	}
}
?>