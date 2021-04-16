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
				$response = Utils::customizeResponse("200", "400", "기 가입된 이메일주소 입니다. ", "");
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
		if(strlen($params->account) < 1 && strlen($params->join_type) < 1){
			return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
		}

		$sql = "SELECT t1.* ";
		$sql .= "FROM USER_INFO AS t1 ";
		$sql .= "WHERE t1.account = ? AND t1.join_type = ? LIMIT 1";
		
		$this->key_value = array($params->account , $params->join_type);
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
        $this->load->library('Crypt');

		$this->setAccessToken($params->account);

		switch($params->role){
            case 'CP' : $user_auth = 1; break;
            case 'SP' : $user_auth = 2; break;
            case 'D' : $user_auth = 3; break;
        }

		$me_data = (object)array(
			'signed'								=> true,
			'eth_account'						=> $params->account,
			'eth_password'					=> Crypt::Encrypt($params->password),
            'eth_role'                           => $params->role,
            'user_auth'                        => $params->account == MASTER_ETH_ACCOUNT_ID ? 9 : $user_auth,
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
			$this->auth_user->eth_account					= $sess->eth_account;
			$this->auth_user->eth_password					= $sess->eth_password;
			$this->auth_user->user_auth                       = $sess->user_auth;

			if($called_me){
				$this->auth_user->is_user_update			= time();
			}
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
		return Utils::customizeResponse("200", "200", "success", null);
	}

    public function getUserVertify($params){
        if(strlen($params->account) < 1 && strlen($params->join_type) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM USER_INFO AS t1 ";
        $sql .= "WHERE t1.account = ? AND t1.join_type = ? LIMIT 1";

        $this->key_value = array($params->account , $params->join_type);
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

    public function putUserData($params){
        if(strlen($params->account) < 1 && strlen($params->join_type) < 1 && strlen($params->password_hash) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $keys[] = " state = 0";
        foreach($params as $key=>$val){
            $keys[] = $key ." =  ?  ";
            $values[] = $val;
        }
        $keys[] = "create_ip = '".$_SERVER['REMOTE_ADDR']."'";
        $keys[] = "create_datetime = now()";


        $sql = "INSERT INTO USER_INFO SET ".implode(',' , $keys );
        $this->conn->query($sql, $values);
		if(!$this->conn->isError()){
            $this->ret->user_info_id = $this->conn->insertId();
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
		return $response;
    }

    public function getUserList($params){
        if(strlen($params->user_auth) < 1 && $params->user_auth < 9){
            return Utils::customizeResponse(400, 400, "This is not a valid request.11", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* ";

        $table_sql = "FROM USER_INFO AS t1 ";

        $where_sql = "WHERE  t1.state = ? ";

        $order_sql = "ORDER BY t1.user_info_id DESC ";

        $this->key_value = array($params->state);

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

                    $this->conn->query($list_sql, $this->key_value );
                    if(!$this->conn->isError()){
                        $this->ret->rows = $this->conn->fetchAll('object');
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
                $this->ret->rows = $this->conn->fetchAll('object');
                $this->ret->total_rows = count($this->ret->rows);
                $this->ret->num_start = $this->ret->total_rows;

                $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
            }else{
                $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
            }
        }
        return $response;
    }

    public function modeUserAuthCommit($params){
        if(strlen($params->userAccounts) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE USER_INFO SET state= ? WHERE  account  IN( ? ) ";
        $this->key_value = array(
            $params->state,
            $params->userAccounts
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $this->ret->user_info_id = $this->conn->insertId();
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

}
?>