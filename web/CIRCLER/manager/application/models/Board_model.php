<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class board_model extends CI_Model {
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

	public function postBoardManage($params){
        if(strlen($params->bbs_id) < 1 || strlen($params->bbs_name) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $query[] = "bbs_id = ? ";
        $query[] = "bbs_name = ? ";
        $query[] = "skin_name = ? ";
        $query[] = "file_use = ? ";
        $query[] = "file_cnt = ? ";
        $query[] = "ca_title = ? ";
        $query[] = "ca_name = ? ";
        $query[] = "list_cnt = ? ";
        $query[] = "editor_use = ? ";
        $query[] = "reply = ? ";
        $query[] = "comment = ? ";
        $query[] = "secret = ? ";
        $query[] = "regdate = now() ";

        $sql = "INSERT INTO BOARD_INFO SET ".implode(',' , $query);

        $this->key_value = array(
            $params->bbs_id,
            $params->bbs_name,
            $params->skin_name,
            $params->file_use,
            $params->file_cnt,
            $params->ca_title,
            $params->ca_name,
            $params->list_cnt,
            $params->editor_use,
            $params->reply,
            $params->comment,
            $params->secret,
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $this->ret->board_info_id = $this->conn->insertId();
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;

    }

    public function modBoardManage($params){
        if(strlen($params->board_info_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $query[] = "bbs_name = ? ";
        $query[] = "skin_name = ? ";
        $query[] = "file_use = ? ";
        $query[] = "file_cnt = ? ";
        $query[] = "ca_title = ? ";
        $query[] = "ca_name = ? ";
        $query[] = "list_cnt = ? ";
        $query[] = "editor_use = ? ";
        $query[] = "reply = ? ";
        $query[] = "comment = ? ";
        $query[] = "secret = ? ";


        $sql = "UPDATE  BOARD_INFO SET ".implode(',' , $query);
        $sql .= "WHERE board_info_id = ? ";

        $this->key_value = array(
            $params->bbs_name,
            $params->skin_name,
            $params->file_use,
            $params->file_cnt,
            $params->ca_title,
            $params->ca_name,
            $params->list_cnt,
            $params->editor_use,
            $params->reply,
            $params->comment,
            $params->secret,
            $params->board_info_id,
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $this->ret->board_info_id = $params->board_info_id;
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;

    }

    public function getBoardSingle($params){
        if(strlen($params->bbs_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM BOARD AS t1 ";
        $sql .= "WHERE t1.bbs_id = ? AND t1.board_id = ? LIMIT 1";

        $this->key_value = array($params->bbs_id , $params->board_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getBoardPrevNo($params){
        if(strlen($params->bbs_id) < 1 || strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.board_id ";
        $sql .= "FROM BOARD AS t1 ";
        $sql .= "WHERE t1.bbs_id = ? AND t1.board_id < ? ORDER BY t1.board_id DESC LIMIT 1";

        $this->key_value = array($params->bbs_id , $params->board_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getBoardNextNo($params){
        if(strlen($params->bbs_id) < 1 || strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.board_id ";
        $sql .= "FROM BOARD AS t1 ";
        $sql .= "WHERE t1.bbs_id = ? AND t1.board_id > ? ORDER BY t1.board_id ASC LIMIT 1";

        $this->key_value = array($params->bbs_id , $params->board_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getBoardSearch($params){
        if(strlen($params->bbs_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.*  , t2.email as user_email  , (SELECT count(*) as answer FROM BOARD AS a  WHERE a.wgroup = t1.wgroup AND a.bbs_id = '".$params->bbs_id."' AND a.depth > 0) AS answer ";

        $table_sql = "FROM BOARD AS t1 ";
        $table_sql .= "LEFT JOIN  USER_INFO AS t2 ON t1.user_info_id = t2.user_info_id ";

        $where_sql = "WHERE t1.bbs_id = ?  ";

        if($params->search_key && $params->search_value){
            if($params->search_key == "t1.subject" || $$params->t1.contents) $where_sql .= " AND ".$params->search_key." LIKE  '%".$params->search_value."%' ";
            else $where_sql .= " AND ".$params->search_key." =  '".$params->search_value."' ";
    }

        if($params->start_date && $params->end_date){
            $where_sql .= " AND (t1.regdate >=  '".$params->start_date." 00:00:00' AND t1.regdate <= '".$params->end_date." 23:59:59') ";
        }

        $order_sql = "ORDER BY t1.wgroup DESC , t1.sort ASC ";

        $this->key_value = array($params->bbs_id);

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

    public function getBoardInfoRedundancyCheck($params){
        if(strlen($params->search_key) < 1 || strlen($params->search_value) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM BOARD_INFO AS t1 ";
        $sql .= "WHERE t1.".$params->search_key." = ? LIMIT 1";

        $this->key_value = array($params->search_value);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_info_id)){
                $response = Utils::customizeResponse("998", "200", "ADD", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", '');
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getBoardInfoSearch($params){
        if(strlen($params->state) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* ";
        $table_sql = "FROM BOARD_INFO AS t1 ";
        $where_sql = "WHERE 1 ";

        if(@$params->search_key && @$params->search_value){
            $where_sql .= " AND t1.".$params->search_key." =  '".$params->search_value."' ";
        }

        $order_sql = "ORDER BY t1.board_info_id DESC ";

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

    public function getBoardInfoSingle($params){
        if(strlen($params->bbs_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM BOARD_INFO AS t1 ";
        $sql .= "WHERE t1.bbs_id = ? LIMIT 1";

        $this->key_value = array($params->bbs_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_info_id)){
                $response = Utils::customizeResponse("998", "200", "FAIL", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getBoardInfoSingleSearch($params){
        if(strlen($params->board_info_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM BOARD_INFO AS t1 ";
        $sql .= "WHERE t1.board_info_id = ? LIMIT 1";

        $this->key_value = array($params->board_info_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->board_info_id)){
                $response = Utils::customizeResponse("998", "200", "FAIL", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
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
            'manager_info_id'				=> $params->manager_info_id,
            'manager_id'						=> $params->manager_id,
            'manager_level'					=> $params->manager_level,
            'manager_name'                 => $params->manager_name,
        );

        $this->session->set_userdata(array(
            AUTH_USER_KEY  => $me_data
        ));
        $this->userDataSession2Class(true);

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

        $sql = "SELECT t1.* ";
        $sql .= "FROM MANAGER_INFO AS t1 ";
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

    public function getMaxNo($params){
        if(strlen($params->table) < 1 && strlen($params->column) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT max(".$params->column.") as maxno  FROM ".$params->table." WHERE 1=1 ".$params->where;
        $this->key_value = array();
        $this->conn->query($sql ,$this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            if(!isset($row->maxno)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function addBoard($params){
        if(strlen($params->bbs_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


        $query[] = "bbs_id = ? ";
        $query[] = "user_info_id = ? ";
        $query[] = "user_email = ? ";
        $query[] = "pwd = ? ";
        $query[] = "email = ? ";
        $query[] = "subject = ? ";
        $query[] = "contents = ? ";
        $query[] = "ip = ? ";
        $query[] = "fix = ?";
        $query[] = "secret = ? ";
        $query[] = "ca_name = ? ";
        $query[] = "phone = ? ";
        $query[] = "hp = ? ";
        $query[] = "regdate = now() ";

        $sql = "INSERT INTO BOARD SET ".implode(',' , $query);
        $this->key_value = array(
            $params->bbs_id ,
            $params->user_info_id,
            $params->user_email,
            $params->pwd,
            $params->email,
            $params->subject,
            $params->contents,
            $_SERVER['REMOTE_ADDR'],
            $params->fix,
            $params->secret,
            $params->ca_name,
            $params->phone,
            $params->hp,
        );
        $this->conn->query($sql, $this->key_value);
        $insertID = $this->conn->insertId();

        if(!$this->conn->isError()){

            if(!$insertID){
                $response = Utils::customizeResponse("200", "998", "FAIL", "");
            }else{
                $_params['table'] = 'BOARD';
                $_params['column'] = 'wgroup';
                $_params['where'] = '';
                $max_data = $this->getMaxNo((object)$_params);

                $sql = "UPDATE BOARD SET wgroup = ? WHERE board_id = ? ";
                $this->key_value = array(($max_data->data->maxno + 1) , $insertID);
                $this->conn->query($sql, $this->key_value);


                $response = Utils::customizeResponse("200", "200", "SUCC", (object)array('board_id'=>$insertID));
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function modBoard($params){
        if(strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


       foreach($params as $key=>$val){
            if($key == 'board_id') continue;
            $query[] = $key ."= '" .$val."'";
       }

        $sql = "UPDATE BOARD SET ".implode(',' , $query) . " WHERE board_id = ? ";
        $this->key_value = array(
            $params->board_id ,
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array('board_id'=>$params->board_id));
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function replyBoard($params){
        if(strlen($params->bbs_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $depth  = $params->depth + 1;
        $sort =  $params->sort + 1;

        $update_sql = "UPDATE BOARD SET sort = sort + 1 WHERE bbs_id = ? AND sort > ".$sort;
        $this->key_value = array($params->bbs_id);
        $this->conn->query($update_sql, $this->key_value);


        $query[] = "bbs_id = ? ";
        $query[] = "user_info_id = ? ";
        $query[] = "user_email = ? ";
        $query[] = "pwd = ? ";
        $query[] = "email = ? ";
        $query[] = "subject = ? ";
        $query[] = "contents = ? ";
        $query[] = "ip = ? ";
        $query[] = "fix = ?";
        $query[] = "secret = ? ";
        $query[] = "ca_name = ? ";
        $query[] = "phone = ? ";
        $query[] = "hp = ? ";
        $query[] = "depth = ? ";
        $query[] = "sort = ? ";
        $query[] = "wgroup = ? ";
        $query[] = "regdate = now() ";

        $sql = "INSERT INTO BOARD SET ".implode(',' , $query);
        $this->key_value = array(
            $params->bbs_id ,
            $params->user_info_id,
            $params->user_email,
            $params->pwd,
            $params->email,
            $params->subject,
            $params->contents,
            $_SERVER['REMOTE_ADDR'],
            $params->fix,
            $params->secret,
            $params->ca_name,
            $params->phone,
            $params->hp,
            $depth ,
            $sort ,
            $params->wgroup,
        );
        $this->conn->query($sql, $this->key_value);
        $insertID = $this->conn->insertId();

        if(!$this->conn->isError()){

            if(!$insertID){
                $response = Utils::customizeResponse("200", "998", "FAIL", "");
            }else{

                $response = Utils::customizeResponse("200", "200", "SUCC", (object)array('board_id'=>$insertID));
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function delSetBoard($params){
        if(strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "DELETE FROM BOARD WHERE board_id = ? ";
        $this->key_value = array($params->board_id);

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", '');
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function postBoardFile($params){
        if(count($params->data) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $values = array();
        $insert_values = "";

        $sql  = "INSERT INTO BOARD_FILE (board_id , file_path , file_name , file_type , file_origin_name ,file_size ,file_domain , del_yn) VALUES ";

        for($i=0 ; $i<count($params->data->file_name); $i++){

            if ($insert_values != "") $insert_values .= ", ";
            $insert_values .= "(? , ? , ? , ? , ? , ? , ? , ? )";
            array_push($values , $params->board_id , $params->data->file_path[$i] , $params->data->file_name[$i] , $params->data->file_type[$i] , $params->data->file_origin_name[$i] , $params->data->file_size[$i],$params->ftp_domain,'N');
        }

        if($insert_values) {
            $sql .= $insert_values;
            $this->conn->query($sql, $values);
            $result_arr[] = $this->conn->isError();
        }

        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
        }
        return $response;
    }


    public function modBoardFile($params){
        if(count($params->board_file_no) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        foreach($params as $key=>$val){
            if($key == 'board_file_no') continue;
            $query[] = $key ."= '". $val."'";
        }

        $sql  = "UPDATE BOARD_FILE SET ".implode(',' , $query) . " WHERE board_file_no = ? ";
        $this->key_value = array($params->board_file_no);
        $this->conn->query($sql, $this->key_value , 1);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array('board_file_no'=>$params->board_file_no));
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getBoardFile($params){
        if(strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM BOARD_FILE WHERE board_id = ? AND del_yn = ? ";
        $this->key_value = array($params->board_id , 'N');

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $rows = $this->conn->fetchAll('object');
            if(count($rows) < 1){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $this->ret->rows = $rows;
                $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getBoardFileSingle($params){
        if(strlen($params->board_file_no) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM BOARD_FILE WHERE board_file_no = ? ";
        $this->key_value = array($params->board_file_no);

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            if(!$row->board_file_no){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public  function setDeltBoard($params){
        if(strlen($params->board_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "DELETE FROM BOARD WHERE board_id = ? ";
        $this->key_value = array($params->board_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){

            $file_sql = "UPDATE BOARD_FILE SET del_yn ='Y' WHERE board_id = ? ";
            $this->key_value = array($params->board_id);
            $this->conn->query($file_sql, $this->key_value);

            $rows = $this->getBoardFile((object)array('board_id'=>$params->board_id));
            if($rows->code == 200) $this->ret->rows = $rows->data->rows;
            else $this->ret->rows = array();

            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);

        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function setDelBoardFile($params){
        if(strlen($params->board_file_no) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        foreach($params as $key=>$val){
            if($key != 'board_file_no') {
                $query[] = $key . " = '" . $val."'";
            }
        }

        $sql = "UPDATE BOARD_FILE SET ".implode(',' , $query) . " WHERE board_file_no = ? ";
        $this->key_value = array($params->board_file_no);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;

    }

    public function getBoardReplySearch($params){
        if(strlen($params->bbs_id) < 1 && strlen($params->wgroup) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM BOARD WHERE bbs_id = ? AND wgroup = ? AND depth = ? ";
        $this->key_value = array($params->bbs_id , $params->wgroup , $params->depth);

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            if(empty($row->board_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                //$this->ret->rows = $rows;
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }



}
?>