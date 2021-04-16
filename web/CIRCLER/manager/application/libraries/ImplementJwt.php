<?php
require APPPATH.'/libraries/JWT.php';
class implementJwt{
	PRIVATE $key = "dkfnd#@!dkfbncd";

	public static function generateToken($data){          
		$jwt = JWT::encode($data, $this->key);
		return $jwt;
	}
	
	public function decodeToken($token){          
		$decoded = JWT::decode($token, $this->key, array('HS256'));
		$decodedData = (array) $decoded;
		return $decodedData;
	}
}
?> 