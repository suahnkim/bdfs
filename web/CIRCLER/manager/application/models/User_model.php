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
            "signed"						=> null,
            "email"							=> null,
            "manager_info_id"           => null,
            "manager_id"                 => null,
            "manager_level"              => null,
            "manager_name"            => null,
            "user_info_id"                 => null,
            "level"                           =>null,

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

    public function getUserSearchEmail($params){
        if(strlen($params->email) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM USER_INFO AS t1 ";
        $sql .= "WHERE t1.email = ? LIMIT 1";

        $this->key_value = array($params->email);
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
			'signed'								=> true,
			'user_info_id'					=> $params->user_info_id,
			'eth_account'						=> $params->account,
			'eth_password'					=> $params->password,
			'email'									=> $params->email,
		);

		$this->session->set_userdata(array(
			AUTH_USER_KEY  => $me_data
		));
		$this->UserDataSession2Class(true);

		return Utils::customizeResponse(200, 200, "SUCC", null);
	}

    public function procCreateManagerSession($params){
        if(strlen($params->manager_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $this->setAccessToken($params->manager_id);

        $me_data = (object)array(
            'signed'								=> true,
            'email'                               => $params->email,
            'manager_info_id'				=> $params->manager_info_id,
            'manager_id'						=> $params->manager_id,
            'manager_level'					=> $params->manager_level,
            'manager_name'                 => $params->manager_name,
            'user_info_id'                     => $params->user_info_id,
            'level'                               => $params->level,
        );

        $this->session->set_userdata(array(
            AUTH_USER_KEY  => $me_data
        ));
        $this->managerDataSession2Class(true);

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
            $this->auth_user->manager_info_id				= $sess->manager_info_id;
            $this->auth_user->manager_level					= $sess->manager_level;
            $this->auth_user->manager_name				= $sess->manager_name;
            $this->auth_user->user_info_id                    = $sess->user_info_id;

			if($called_me){
				$this->auth_user->is_user_update			= time();
			}
		}
	}

    private function managerDataSession2Class($called_me = false){
        if($this->session->has_userdata(AUTH_USER_KEY)){
            $sess = $this->session->userdata(AUTH_USER_KEY);

            $this->auth_user->signed							= $sess->signed;
            $this->auth_user->manager_info_id				= $sess->manager_info_id;
            $this->auth_user->manager_level					= $sess->manager_level;
            $this->auth_user->manager_name				= $sess->manager_name;
            $this->auth_user->user_info_id                    = $sess->user_info_id;
            $this->auth_user->level                               = $sess->level;
            $this->auth_user->email                              = $sess->email;

            if($called_me){
                $this->auth_user->is_user_update			= time();
            }
        }
    }


	public function getUser(){
		if($this->isSignedIn()){
			if($this->auth_user->signed === null){
				$this->managerDataSession2Class();
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

	public function postManager($params){
        if(strlen($params->manager_id) < 1 && strlen($params->manager_pwd) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


        $query[]  = "manager_id = ? ";
        $query[]  = "manager_pwd = ? ";
        $query[]  = "state = ? ";
        $query[]  = "level = ? ";
        $query[]  = "create_datetime = now() ";

        $sql = "INSERT INTO MANAGER_INFO SET ".implode("," , $query);
        $this->key_value = array(
            $params->manager_id ,
            $params->manager_pwd,
            1,
            9,
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getManagerSingleInfo($params){
        if(strlen($params->manager_id) < 1 && strlen($params->manager_pwd) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


        $sql = "SELECT t1.* , t2.level as user_level , t2.email ";
        $sql .= "FROM MANAGER_INFO AS t1 ";
        $sql .=  "LEFT JOIN USER_INFO AS t2 ON t1.user_info_id = t2.user_info_id ";
        $sql .= "WHERE t1.manager_id = ? AND t1.manager_pwd = ? LIMIT 1";

        $this->key_value = array($params->manager_id , $params->manager_pwd);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->manager_info_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getUserStatisticInfo($params){
        if(strlen($params->search_key) < 1 && strlen($params->search_date) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        switch ($params->search_key){
            case 'day' : $left_date = "LEFT(t1.create_datetime ,10) "; break;
            case 'month' : $left_date = "LEFT(t1.create_datetime ,7) "; break;
        }

        $sql = "SELECT count(*) AS cnt FROM USER_INFO AS t1  WHERE ".$left_date." =  ? AND t1.state = ? ";
        $this->key_value = array($params->search_date , $params->state);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            if(!isset($row->cnt)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function  getUserSearch($params){
        if(strlen($params->state) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* ";
        $table_sql = "FROM USER_INFO AS t1 ";
        $where_sql = "WHERE 1 ";
        if($params->state){
            $where_sql .= " AND t1.state = ? ";
        }

        if($params->start_date && $params->end_date){
            $where_sql .= " AND (t1.create_datetime >= '".$params->start_date." 00:00:00' AND t1.create_datetime <= '".$params->end_date." 23:59:59')";
        }

        if($params->email){
            $where_sql .= " AND t1.email =  '".$params->email."' ";
        }

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

                    $this->conn->query($list_sql, $this->key_value);
                    if(!$this->conn->isError()){
                        $rows = $this->conn->fetchAll();
                        $this->ret->rows = (object)$rows;
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

    public function getCertifySearch($params){
        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* , t2.account  , t2.email  ";
        $table_sql = "FROM CERTIFY_INFO AS t1 ";
        $table_sql .= "LEFT JOIN USER_INFO AS t2  ON t1.user_info_id = t2.user_info_id ";

        $where_sql = "WHERE t1.del_yn = ? AND t2.state=1 ";

        $order_sql = "ORDER BY t1.certify_id DESC ";

        $this->key_value = array(
            'N',
        );

        if($params->start_date && $params->end_date){
            $where_sql .= " AND (t1.regdate >= '".$params->start_date." 00:00:00' AND t1.regdate <= '".$params->end_date." 23:59:59')";
        }
        if($params->email){
            $where_sql .= " AND t2,email = '".$params->email."'";
        }
        if($params->adult_yn){
            $where_sql .= " AND t1.adult_yn = '".$params->adult_yn."'";
        }

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

    public function modCertityInfo($params){
        if(count($params->certify_ids) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $certify_ids =  implode(',' , $params->certify_ids);
        $sql = "UPDATE CERTIFY_INFO SET ";
        $sql .= "adult_yn = ? ,";
        $sql .= "update_regdate = NOW() ";
        $sql .= "WHERE certify_id in (".$certify_ids.") ";

        $this->key_value = array(
            $params->adult_yn,
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){

            $user_info_ids = implode(',' , $params->user_info_ids);
            $sql = "UPDATE USER_INFO SET is_adult = ? WHERE user_info_id IN(".$user_info_ids.")";
            $this->key_value = array(
                $params->adult_yn,
            );
            $this->conn->query($sql, $this->key_value);

            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function modSingleCertityInfo($params){
        if(strlen($params->certify_id) < 1 && strlen($params->user_info_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE CERTIFY_INFO SET ";
        $sql .= "adult_yn = ? ,";
        $sql .= "update_regdate = NOW() ";
        $sql .= "WHERE certify_id = ? ";

        $this->key_value = array(
            $params->adult_yn,
            $params->certify_id,
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){

            $sql = "UPDATE USER_INFO SET is_adult = ? WHERE user_info_id = ? ";
            $this->key_value = array(
                $params->adult_yn,
                $params->user_info_id ,
            );
            $this->conn->query($sql, $this->key_value);

            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

}
?>