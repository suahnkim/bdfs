<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user_model extends CI_Model {
	private $auth_user = null;
	private $conn;
	private $ret;
	private $key_value;

	function __construct(){
		parent::__construct();

		$this->auth_user = (object)array(
			"signed"								=> null,
			"user_info_id"					=> null,
			"eth_account"						=> null,
			"eth_password"					=> null,
			"email"									=> null,
			"is_user_update"				=> null,
            "is_adult"                       => null,
		);

		$this->conn = new MysqlConnection();
		$this->ret = new \stdClass;
		$this->key_value = array();
	}

	public function isSignedIn(){
		if(strlen($this->session->userdata(AUTH_USER_TOKEN)) > 0){
			return true;
		}
		return false;
	}

	public function getEthAccountSingle($params){
		if(strlen($params->account) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$sql = "SELECT t1.* ";
		$sql .= "FROM USER_INFO AS t1 ";
		$sql .= "WHERE t1.state = ? AND t1.account = ? LIMIT 1";

		$this->key_value = array('1', $params->account);
		$this->conn->query($sql, $this->key_value);
		if(!$this->conn->isError()){
			$row = $this->conn->fetch();
			if(!isset($row->user_info_id)){
				$response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
			}else{
				$response = Utils::customizeResponse("200", "200", "SUCC", $row);
			}
		}else{
			$response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
		}
		return $response;
	}

	public function chkSignEmail($params){
		if(strlen($params->email) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$sql = "SELECT COUNT(*) AS cnt FROM USER_INFO WHERE email = ? AND state IN (0, 1)";
		$this->key_value = array($params->email);

		$this->conn->query($sql, $this->key_value);
		if(!$this->conn->isError()){
			$row = $this->conn->fetch();
			if(isset($row->cnt) && $row->cnt < 1){
				$response = Utils::customizeResponse("200", "200", "SUCC", "");
			}else{
				$response = Utils::customizeResponse("200", "400", "이미 가입된 이메일주소 입니다. ", "");
			}
		}else{
			$response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
		}
		return $response;
	}

	public function putSignInforDirect($params){
		if(strlen($params->state) < 1 || strlen($params->email) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$sql = "INSERT INTO USER_INFO SET ";
		$sql .= "state = ?, ";
		$sql .= "account = ?, ";
		$sql .= "password_hash = ?, ";
		$sql .= "email = ?, ";
		$sql .= "create_ip = ?, ";
		$sql .= "update_datetime = NOW(), ";
		$sql .= "create_datetime = NOW() ";

		$this->key_value = array(
			$params->state, 
			'',
			'',
			$params->email,
			$params->create_ip,
		);

		$this->conn->query($sql, $this->key_value);
		if(!$this->conn->isError()){
			$this->ret->email = $params->email;
			$this->ret->user_info_id = $this->conn->insertId();
			$response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
		}else{
			$response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
		}
		return $response;
	}

	public function postAuthResult($params){
		if(strlen($params->user_info_id) < 1 || strlen($params->account) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}


		$sql = "UPDATE USER_INFO SET ";
		$sql .= "state = 1, ";
		$sql .= "account = ?, ";
		$sql .= "password_hash = ?, ";
		$sql .= "update_datetime = NOW() ";
		$sql .= "WHERE user_info_id = ? ";

		$this->key_value = array(
			$params->account,
			(strlen($params->password) > 0 ? Utils::create_hash($params->password) : ''),
			$params->user_info_id,
		);

		$this->conn->query($sql, $this->key_value);
		if(!$this->conn->isError()){
			$response = Utils::customizeResponse("200", "200", "SUCC", "");
		}else{
			$response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
		}
		return $response;
	}

	public function getUserInfoSingle($params){
		if(strlen($params->user_info_id) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$sql = "SELECT t1.* ";
		$sql .= "FROM USER_INFO AS t1 ";
		$sql .= "WHERE t1.user_info_id = ? LIMIT 1";
		
		$this->key_value = array($params->user_info_id);
		$this->conn->query($sql, $this->key_value);
		if(!$this->conn->isError()){
			$row = $this->conn->fetch();
			if(!isset($row->user_info_id)){
				$response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
			}else{
				$response = Utils::customizeResponse("200", "200", "SUCC", $row);
			}
		}else{
			$response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
		}
		return $response;
	}

	public function procCreateSignedSession($params){
		if(strlen($params->account) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$this->setAccessToken($params->account);

		$me_data = (object)array(
			'signed'							=> true,
			'user_info_id'					=> $params->user_info_id,
			'eth_account'					=> $params->account,
			'eth_password'				=> $params->password,
			'email'							=> $params->email,
            'is_adult'                       => $params->is_adult,
		);

		$this->session->set_userdata(array(
			AUTH_USER_KEY  => $me_data
		));
		$this->UserDataSession2Class(true);
		return Utils::customizeResponse(200, 200, "SUCC", null);
	}

	private function setAccessToken($account){
		$signin_data = array(
			AUTH_USER_TOKEN => $account,
		);
		$this->session->set_userdata($signin_data);
	}

	private function userDataSession2Class($called_me = false){
		if($this->session->has_userdata(AUTH_USER_KEY)){
			$sess = $this->session->userdata(AUTH_USER_KEY);
			
			$this->auth_user->signed							= $sess->signed;
			$this->auth_user->user_info_id					= $sess->user_info_id;
			$this->auth_user->eth_account					= $sess->eth_account;
			$this->auth_user->eth_password					= $sess->eth_password;
			$this->auth_user->email								= $sess->email;
			$this->auth_user->is_adult                          = $sess->is_adult;

			if($called_me){
				$this->auth_user->is_user_update			= time();
			}
            $this->load->helper('cookie');
			$cookie = array(
			    'name'  => 'is_adult',
                'value'  => $sess->is_adult,
                'expire' => 0,
                'domain' => '.circler.co.kr',
                'path'   => '/',
                'prefix' => '',
            );
			$this->input->set_cookie($cookie);
        }
	}

	public function getUser(){
		if($this->isSignedIn()){
			if($this->auth_user->signed === null){
				$this->userDataSession2Class();
			}
		}
		return $this->auth_user;
	}

	public function doSignOut(){
		$this->session->sess_destroy();
		$_SESSION = array();
		@session_destroy();
        $this->load->helper('cookie');
        delete_cookie("is_adult");
		return Utils::customizeResponse("200", "200", "success", null);
	}

	public function postUserCertify($params){
        if(strlen($params->user_info_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "INSERT  CERTIFY_INFO SET ";
        $sql .= "user_info_id = ?, ";
        $sql .= "identify_id = ? ,";
        $sql .= "attribute = ? ,";
        $sql .= "attributeId = ? ,";
        $sql .= "adult_yn = ?, ";
        $sql .= "regdate = now() ";

        $this->key_value = array(
            $params->user_info_id,
            $params->identify_id,
            $params->attribute,
            $params->attributeId,
            'N',
        );

        $this->conn->query($sql, $this->key_value);
        $this->ret->certify_id = $this->conn->insertId();
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getCertifySingle($params){
        if(strlen($params->user_info_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM CERTIFY_INFO WHERE user_info_id = ? AND del_yn = ? ";
        $this->key_value = array($params->user_info_id , 'N');
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->certify_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;

    }

    public function getCertifySearch($params){


        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT * ";
        $table_sql = "FROM CERTIFY_INFO AS t1 ";

        $where_sql = "WHERE t1.del_yn = ?";

        $order_sql = "ORDER BY t1.certify_id DESC ";

        $this->key_value = array('N');

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);
            if(!$this->conn->isError()){
                $row = $this->conn->fetch();
                if(isset($row->cnt)){
                    $this->ret->total_rows = $row->cnt;
                    $this->ret->num_start = $this->ret->total_rows - ($params->page_num - 1) * $params->page_size;

                    $list_sql .= $table_sql.$where_sql.$order_sql;

                    $list_sql .= "LIMIT ".(($params->page_num - 1) * $params->page_size).", ".$params->page_size;

                    $this->conn->query($list_sql, $this->key_value);
                    if(!$this->conn->isError()){
                        $rows = $this->conn->fetchAll('object');
                        $this->ret->rows = $rows;
                        $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
                    }else{
                        $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
                    }
                }else{
                    $response = Utils::customizeResponse("999", "999", "알수 없는 오류 발생.", "");
                }
            }else{
                $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
            }
        }else{
            $list_sql .= $table_sql.$where_sql.$order_sql;

            $this->conn->query($list_sql, $this->key_value);
            if(!$this->conn->isError()){
                $rows = $this->conn->fetchAll('object');
                $this->ret->rows = $rows;
                $this->ret->total_rows = count($rows);
                $this->ret->num_start = $this->ret->total_rows;

                $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
            }else{
                $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
            }
        }
        return $response;
    }


}
?>