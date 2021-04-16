<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class contents_model extends CI_Model {
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

	public function getContentsProductInfo($params){

        if(strlen($params->productId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM CONTENTS AS t1 ";
        $sql .= "WHERE t1.productId = ? LIMIT 1";

        $this->key_value = array($params->productId);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->contents_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsSingleInfo($params){
        if(strlen($params->contents_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM CONTENTS AS t1 ";
        $sql .= "WHERE t1.contents_id = ? LIMIT 1";

        $this->key_value = array($params->contents_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->contents_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsFileSingleInfo($params){
        if(strlen($params->contents_id) < 1 || strlen($params->contents_file_no) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.* ";
        $sql .= "FROM CONTENTS_FILE AS t1 ";
        $sql .= "WHERE t1.contents_id = ? AND t1.contents_file_id = ?  AND t1.state != 'D' LIMIT 1";

        $this->key_value = array($params->contents_id , $params->contents_file_no);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->contents_file_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getContentFileInfo($params){

        if(strlen($params->contents_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT * ";
        $table_sql = "FROM CONTENTS_FILE AS t1 ";

        $where_sql = "WHERE t1.contents_id = ?";

        $order_sql = "ORDER BY t1.sort ASC ";

        $this->key_value = array($params->contents_id);

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

    public function getContentsList($params){
        if(strlen($params->page_num) < 1 && strlen($params->page_size)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* ";
        $table_sql = "FROM CONTENTS AS t1 ";

        $where_sql = "WHERE t1.state=1 ";
        if(@$params->srch_key && @$params->srch_value){
            $where_sql .= "AND t1.". @$params->srch_key . " LIKE '%".@$params->srch_value."%' ";
        }
        if(@$params->title){
            $where_sql .= " AND t1.title LIKIE '%".$params->title."%' ";
        }

        if(@$params->contents_id){
            $where_sql .= " AND t1.contents_id = '%".$params->contents_id."%' ";
        }

        if(@$params->start_date && @$params->end_date){
            $where_sql .= " AND (wdate >= ".strtotime(date($params->start_date . ' 00:00:00'))." AND wdate <= ".strtotime(date($params->end_date . ' 23:59:59')).") ";
        }

        $order = @$params->order ? @$params->order : 'wdate';
        $order_sql = " ORDER BY t1.".$order." DESC ";

        $this->key_value = array();

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);
            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function getContentsTop($params){
        if(strlen($params->limit) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM CONTENTS WHERE state=1 ORDER BY contents_id DESC LIMIT ".$params->limit;
        $this->key_value = array($params->limit);
        $this->conn->query($sql , $this->key_value);
        if(!$this->conn->isError()){
            $rows = $this->conn->fetchAll('object');
            $this->ret->rows = $rows;
            $this->ret->total_rows = count($rows);
            $this->ret->num_start = $this->ret->total_rows;
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsPopularList($params){
        if(strlen($params->page_num) < 1 && strlen($params->page_size)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* ";
        $table_sql = "FROM CONTENTS_DOWNLOAD_RANK AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";

        $where_sql = "WHERE 1=1 ";
        if(@$params->srch_key && @$params->srch_value){
            $where_sql .= "AND t2.". @$params->srch_key . " LIKE '%".@$params->srch_value."%' ";
        }
        $order_sql = " ORDER BY t1.download_cnt DESC ";

        $this->key_value = array();

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);
            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function getContentsPopularTop($params){
        if(strlen($params->limit) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM CONTENTS_DOWNLOAD_RANK AS t1 LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id WHERE t2.state=1 ORDER BY t1.contents_download_id DESC LIMIT ". $params->limit;
        $this->key_value = array();
        $this->conn->query($sql , $this->key_value);
        if(!$this->conn->isError()){
            $rows = $this->conn->fetchAll('object');
            $this->ret->rows = $rows;
            $this->ret->total_rows = count($rows);
            $this->ret->num_start = $this->ret->total_rows;
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsRecommandList($params){

        if(strlen($params->page_num) < 1 && strlen($params->page_size)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* ";
        $table_sql = "FROM CONTENTS_RECOMMAND_RANK AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";

        $where_sql = "WHERE 1=1 ";
        if(@$params->srch_key && @$params->srch_value){
            $where_sql .= "AND t2.". @$params->srch_key . " LIKE '%".@$params->srch_value."%' ";
        }
        $order_sql = " ORDER BY t1.recommand_cnt DESC ";

        $this->key_value = array();

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);
            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function getContentsRecommandTop($params){
        if(strlen($params->limit) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM CONTENTS_RECOMMAND_RANK AS t1 LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id WHERE t2.state=1 ORDER BY t1.contents_recommand_id DESC LIMIT ".$params->limit;

        $this->key_value = array($params->limit);
        $this->conn->query($sql , $this->key_value);
        if(!$this->conn->isError()){
            $rows = $this->conn->fetchAll('object');
            $this->ret->rows = $rows;
            $this->ret->total_rows = count($rows);
            $this->ret->num_start = $this->ret->total_rows;
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

	public function postContentsReg($params){

	    if(strlen($params->productId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


            $this->conn->begin_transaction();

            $contents_query[] = "cate1 = ? ";
            $contents_query[] = "userid = ? ";
            $contents_query[] = "title = ? ";
            $contents_query[] = "contents = ? ";
            $contents_query[] = "is_folder = ? ";
            $contents_query[] = "folder_name = ? ";
            $contents_query[] = "size = ? ";
            $contents_query[] = "cash = ? ";
            $contents_query[] = "is_adult = ? ";
            $contents_query[] = "is_rights = ? ";
            $contents_query[] = "state = ? ";
            $contents_query[] = "wdate = ? ";
            $contents_query[] = "init_key = ? ";
            $contents_query[] = "main_img = ? ";
            $contents_query[] = "sub_img = ? ";
            $contents_query[] = "hash_tags = ? ";
            $contents_query[] = "cid = ? ";
            $contents_query[] = "ccid = ? ";
            $contents_query[] = "ccid_ver = ? ";
            $contents_query[] = "drm = ? ";
            $contents_query[] = "watermarking = ? ";
            $contents_query[] = "is_adult = ? ";
            $contents_query[] = "productId = ? ";
            $contents_query[] = "real_cash = ? ";
            $contents_query[] = "metainfo = ? ";
            $contents_query[] = "dataid = ? ";

            $contetns_sql = "INSERT INTO CONTENTS SET ".implode(',' , $contents_query);

            $contents_values = array(
                $params->contents_data->cate1,
                $params->contents_data->userid,
                $params->contents_data->title,
                $params->contents_data->contents,
                $params->contents_data->is_folder,
                $params->contents_data->folder_name,
                $params->contents_data->size,
                $params->contents_data->cash,
                $params->contents_data->is_adult,
                $params->contents_data->is_rights,
                1,
                time(),
                $params->contents_data->init_key,
                $params->contents_data->main_img,
                $params->contents_data->sub_img,
                $params->contents_data->hash_tags,
                $params->contents_data->cid,
                $params->contents_data->ccid,
                $params->contents_data->ccid_ver,
                $params->contents_data->drm,
                $params->contents_data->watermarking,
                $params->is_adult ,
                $params->productId,
                $params->real_cash,
                $params->contents_data->metainfo,
                $params->contents_data->dataid
            );


            if($this->conn->query($contetns_sql, $contents_values)){
                $result_arr[] = $this->conn->isError();
                $contnets_id = $this->conn->insertId();

                if($contnets_id){
                    if(count($params->contents_data->rows) > 0){

                        $values = array();
                        $insert_values = "";
                        $contents_file_qry = "INSERT INTO CONTENTS_FILE (contents_id , userid , folder , filename , realsize , size , state , wdate , sort) VALUES ";
                        foreach($params->contents_data->rows as $key=>$val){

                            if ($insert_values != "") $insert_values .= ", ";

                            $insert_values .= "(? ,? ,? ,? ,? ,? ,? ,? ,?)";
                            array_push($values , $contnets_id , $val->userid , $val->folder , $val->filename , $val->realsize , $val->size , $val->state , $val->wdate , $val->sort);


                        }

                        $contents_file_qry .= $insert_values;
                        $this->conn->query($contents_file_qry, $values);
                        $result_arr[] = $this->conn->isError();

                        if($this->conn->is_commit($result_arr)){
                            $this->conn->commit();
                            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("contents_id" => $contnets_id));
                        }else{
                            $this->conn->rollback();
                            $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
                        }

                    }
                }else{
                    $this->conn->rollback();
                    $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
                }
            }else{
                $this->conn->rollback();
                $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
            }

		return $response;
    }


    public function putContentsFile($params){
        if(strlen($params->contents_id) < 1 || strlen($params->contents_file_no) < 1 || strlen($params->state) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE CONTENTS_FILE SET ";
        $sql .= "state= ? ";
        $sql .= "WHERE contents_id = ?  AND contents_file_id = ? ";

        $this->key_value = array(
          $params->state,
          $params->contents_id ,
          $params->contents_file_no
        );

        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){

            $sql = "SELECT count(*) as cnt , (SELECT count(*) FROM ) FROM CONTENTS_FILE WHERE t1.state= ?  ";

            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

	public function getPurchaseInfo($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $today = time();
        $expire_day = $today + (60 * 60 * 24) * 3;

        $sql = "SELECT * FROM LOG_PURCHASE WHERE contents_id = ? AND accountId = ? AND edate > ?  ";

        $this->key_value = array(
            $params->contents_id ,
            $params->accountId,
            $today
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->log_purchase_id)){
                $response = Utils::customizeResponse("200", "998", "BUY", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "RE", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function contentsPurchase($params){

        if(strlen($params->contents_id) < 1 || strlen($params->blockchain_purchaseId) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $query[] = "accountId = ?";
        $query[] = "contents_id = ?";
        $query[] = "state = ?";
        $query[] = "point = ?";
        $query[] = "wdate = ?";
        $query[] = "edate = ?";
        $query[] = "blockchain_purchaseId = ?";
        $sql = "INSERT INTO LOG_PURCHASE SET ".implode(',' , $query);

        $this->key_value = array(
            $params->accountId,
            $params->contents_id ,
            $params->state,
            $params->point,
            $params->wdate,
            $params->edate,
            $params->blockchain_purchaseId
        );

        $this->conn->query($sql, $this->key_value);
        $insertID = $this->conn->insertId();
        if(!$this->conn->isError()){

            if($insertID){
                $query2[] = "accountId = ?";
                $query2[] = "log_purchase_id = ?";
                $query2[] = "code = ?";
                $query2[] = "point = ?";
                $query2[] = "info = ?";
                $query2[] = "wdate = ?";

                $sql2 = "INSERT INTO LOG_POINT SET ".implode(',' ,  $query2);
                $key_value = array(
                    $params->accountId,
                    $insertID,
                    1001,
                    $params->point,
                    $params->title,
                    $params->wdate,
                );
                $this->conn->query($sql2, $key_value);

                $contents_sql = "UPDATE CONTENTS SET purchase = purchase + 1 WHERE contents_id = ? ";
                $this->conn->query($contents_sql, array($params->contents_id));

                $contents_down_sql = "INSERT INTO CONTENTS_DOWNLOAD_RANK(contents_id , download_cnt ) VALUES ( ? , ? ) ON DUPLICATE KEY UPDATE download_cnt = download_cnt + 1 ";
                $this->key_value = array(
                    $params->contents_id ,
                    1
                );
                $this->conn->query($contents_down_sql, $this->key_value);
                //$query3  = "INSERT INTO person VALUES (NULL, 15, 'James', 'Barkely', 1) ON DUPLICATE KEY UPDATE insert_cnt = insert_cnt + 1;"
            }
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;


    }


    public function getContentsReceiveList($params){

        if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* , t1.edate ";
        $table_sql = "FROM LOG_PURCHASE AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";

        $where_sql = "WHERE t1.accountId = ? AND t1.edate >  UNIX_TIMESTAMP() AND t1.state='Y'";

        $order_sql = " ORDER BY t1.log_purchase_id DESC ";
        if(@$params->receive_srch_value){
            $where_sql .= " AND t2.title LIKE '%".$params->receive_srch_value."%'";
        }

        $this->key_value = array($params->accountId);

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);

            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function getContentsTotal($params){
        if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT SUM(point) AS total_point FROM LOG_POINT WHERE accountId = ? AND point_type = ? ";
        $this->key_value = array($params->accountId , $params->point_type);
        $this->conn->query($sql, $this->key_value);

        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            $response = Utils::customizeResponse("200", "200", "SUCC", $row);
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getContentsUseList($params){

        if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.* ";
        $table_sql = "FROM LOG_POINT AS t1 ";
        $where_sql = "WHERE t1.accountId = ? ";
        if(is_numeric($params->point_type)){
            $where_sql .= " AND t1.point_type = '".$params->point_type."' ";
        }
        $order_sql = " ORDER BY t1.log_point_id DESC ";
      /*  if(@$params->receive_srch_value){
            $where_sql .= " AND t2.info LIKE '%".$params->srch_value."%' ";
        }*/
        if($params->start_date && $params->end_date){
            $start_date = strtotime($params->start_date . " 00:00:00");
            $end_date = strtotime($params->end_date . " 23:59:59");

            $where_sql .= " AND (t1.wdate >=  '".$start_date."' AND t1.wdate <= '".$end_date."') ";
        }


        $this->key_value = array($params->accountId);

        if(@$params->page_yn == "Y"){

            $count_sql .= $table_sql.$where_sql;

            $this->conn->query($count_sql, $this->key_value);

            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function setContentsReceiveDel($params){

        if(count($params->contents_ids) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $contents_ids = implode(',' , $params->contents_ids);
        $sql = "UPDATE LOG_PURCHASE SET state = 'D' WHERE contents_id IN(".$contents_ids.")";
        $this->conn->query($sql);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsZzimList($params){
        if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* ";
        $table_sql = "FROM CONTENTS_ZZIM AS t1 ";
        $table_sql .= " LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";
        $where_sql = "WHERE t1.accountId = ? AND t1.del_yn = ? ";

        $order_sql = " ORDER BY t1.contents_zzim_id DESC ";

        $this->key_value = array($params->accountId , 'N');

        if(@$params->page_yn == "Y"){

            $count_sql .= $table_sql.$where_sql;

            $this->conn->query($count_sql, $this->key_value);

            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
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

    public function setContentsZzimDel($params){

        if(count($params->contents_ids) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $contents_ids = implode(',' , $params->contents_ids);
        $sql = "UPDATE CONTENTS_ZZIM SET del_yn = 'Y' WHERE contents_id IN(".$contents_ids.")";
        $this->conn->query($sql);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsZzimInfo($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $today = time();
        $sql = "SELECT * FROM CONTENTS_ZZIM WHERE contents_id = ? AND accountId = ? AND expire_date > ? AND del_yn = ?  ";

        $this->key_value = array(
            $params->contents_id ,
            $params->accountId,
            $today,
            $params->del_yn,
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->contents_zzim_id)){
                $response = Utils::customizeResponse("200", "200", "", "");
            }else{
                $response = Utils::customizeResponse("200", "201", "이미 찜한 콘텐츠입니다.", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function setContentsZzim($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        
        $expire_date = time() + ((60 * 60 * 24) * 7);  //7일간
        $query[] = "contents_id = ? ";
        $query[] = "accountId = ? ";
        $query[] = "expire_date = ? ";

        $sql = "INSERT INTO CONTENTS_ZZIM SET ".implode(',' , $query);
        $this->key_value = array(
            $params->contents_id ,
            $params->accountId,
            $expire_date
        );

        $this->conn->query($sql, $this->key_value);
        $insertID = $this->conn->insertId();

        if(!$this->conn->isError()){

            if(!$insertID){
                $response = Utils::customizeResponse("200", "998", "FAIL", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "콘텐츠를 찜하였습니다.", $insertID);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getContentsRecommand($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


    }


    public function getContentsRecommandLog($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT * FROM LOG_CONTENTS_RECOMMAND WHERE accountId = ?  AND contents_id = ? ";
        $this->key_value = array(
            $params->accountId,
            $params->contents_id ,
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->log_contents_recommand_id)){
                $response = Utils::customizeResponse("200", "200", "", "");
            }else{
                $response = Utils::customizeResponse("200", "201", "이미 추천한 콘텐츠입니다.", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function  setContentsRecommand($params){
        if(strlen($params->contents_id) < 1 || strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "INSERT INTO CONTENTS_RECOMMAND_RANK(contents_id , recommand_cnt ) VALUES ( ? , ? ) ON DUPLICATE KEY UPDATE recommand_cnt = recommand_cnt + 1 ";
        $this->key_value = array(
            $params->contents_id ,
            1
        );
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $sql2 = "INSERT INTO LOG_CONTENTS_RECOMMAND SET accountId = ? , contents_id = ? ";
            $this->conn->query($sql2, array($params->accountId , $params->contents_id));

            $sql3 = "UPDATE CONTENTS SET  eva = eva + 1 WHERE contents_id = ? ";
            $this->conn->query($sql3, array($params->contents_id));

            $contents_info = $this->getContentsSingleInfo((object)array(
                'contents_id'       => $params->contents_id
            ));

            $response = Utils::customizeResponse("200", "200", '콘텐츠를 추천했습니다.', array('recommand_cnt'=>number_format($contents_info->data->eva)));
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function getSellProductStatisticInfo($params){
        if(strlen($params->search_date) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT sum(point) as sum_point FROM LOG_PURCHASE WHERE state= ?  AND FROM_UNIXTIME(wdate , '%Y-%m-%d') =  ? ";
        $this->key_value = array($params->state , $params->search_date);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch('object');
            $response = Utils::customizeResponse("200", "200", "SUCCESS.", $row);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public  function  getSellContentsPointTop($params){
        if(strlen($params->search_date) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $list_sql = "SELECT count(*) as cnt ,  sum(t1.point) as sum_point  , t1.contents_id , t2.size , t2.real_cash ,t2.title ";
        $table_sql = "FROM LOG_PURCHASE AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";
        $where_sql = "WHERE  t1.state = ?  AND FROM_UNIXTIME(t1.wdate , '%Y-%m-%d') = ? ";

        $order_sql = " GROUP BY t1.contents_id DESC ORDER BY sum_point DESC LIMIT ". $params->limit;

        $this->key_value = array('Y'  , $params->search_date);

        $list_sql .= $table_sql.$where_sql.$order_sql;

        $this->conn->query($list_sql, $this->key_value);

        if(!$this->conn->isError()){
            $rows = $this->conn->fetchAll('object');
            $this->ret->rows = $rows;
            //$this->ret->total_rows = count($rows);
            //$this->ret->num_start = $this->ret->total_rows;

            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }


        return $response;


    }

    public function setContentsModifyPrice($params){

        if(count($params->contents_id) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE CONTENTS SET real_cash = ? WHERE contents_id = ? ";
        $this->key_value = array($params->real_price , $params->contents_id);
        $this->conn->query($sql , $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function delContents($params){

        if(count($params->contents_ids) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $contents_ids = implode(',' , $params->contents_ids);
        $sql = "UPDATE CONTENTS SET state = '2' WHERE contents_id IN(".$contents_ids.")";
        $this->conn->query($sql);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", "");
        }else{
            $response = Utils::customizeResponse("200", "999", $this->conn->getError(), "");
        }
        return $response;
    }



    public function getContentsPurchaseList($params){

        /*if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }*/
        $sum_sql = "SELECT sum(point) as total_point ";
        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* , t3.email , t1.wdate , t1.point  ";
        $table_sql = "FROM LOG_PURCHASE AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";
        $table_sql .= "LEFT JOIN USER_INFO AS t3 ON t1.accountId = t3.account ";
        $where_sql = "WHERE t1.state = 'Y' ";

        if($params->contents_id){
            $where_sql .= " AND t1.contents_id = '".$params->contents_id."' ";
        }
        if($params->email){
            $where_sql .= " AND t3.emial = '".$params->email."' ";
        }

        if($params->title){
            $where_sql .= " AND t2.title LIKE '".$params->title."' ";
        }

        if(@$params->start_date && @$params->end_date){
            $start_date = strtotime($params->start_date . " 00:00:00");
            $end_date = strtotime($params->end_date . " 23:59:59");

            $where_sql .= " AND (t1.wdate >=  '".$start_date."' AND t1.wdate <= '".$end_date."') ";
        }

        $order_sql = " ORDER BY t1.log_purchase_id DESC ";


        $this->key_value = array();

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value);
            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
                if(isset($row->cnt)){
                    $this->ret->total_rows = $row->cnt;

                    $sum_sql .= $table_sql.$where_sql;
                    $this->conn->query($sum_sql, $this->key_value);
                    $sum_row = $this->conn->fetch('object');

                    $this->ret->total_point  = $sum_row->total_point;
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


    public function getSellContents($params){
       /* if(strlen($params->accountId) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }*/

        $sum_sql = "SELECT SUM(t1.point) AS total_point ";
        $count_sql = "SELECT count(DISTINCT t1.contents_id) AS cnt ";
        $list_sql = "SELECT count(*) as cnt ,  sum(t1.point) as sum_point  , t1.contents_id , t2.size , t2.real_cash ,t2.title ";
        $table_sql = "FROM LOG_PURCHASE AS t1 ";
        $table_sql .= "LEFT JOIN CONTENTS AS t2 ON t1.contents_id = t2.contents_id ";
        $where_sql = "WHERE  t1.state = ? ";

        if(@$params->start_date && @$params->end_date){
            $start_date = strtotime($params->start_date . " 00:00:00");
            $end_date = strtotime($params->end_date . " 23:59:59");

            $where_sql .= " AND (t1.wdate >=  '".$start_date."' AND t1.wdate <= '".$end_date."') ";
        }

        if($params->contents_id){
            $where_sql .= " AND t1.contents_id = '".$params->contents_id."' ";
        }
        if($params->title){
            $where_sql .= " AND t2.title LIKE '".$params->title."' ";
        }

        $order_sql = " GROUP BY t1.contents_id DESC ORDER BY t1.wdate DESC ";
        $this->key_value = array( 'Y');

        if(@$params->page_yn == "Y"){

            $count_sql .= $table_sql.$where_sql;

            $this->conn->query($count_sql, $this->key_value);

            if(!$this->conn->isError()){
                $row = $this->conn->fetch('object');
                if(isset($row->cnt)){
                    $this->ret->total_rows = $row->cnt;
                    $this->ret->num_start = $this->ret->total_rows - ($params->page_num - 1) * $params->page_size;

                    $sum_sql .= $table_sql.$where_sql;
                    $this->conn->query($sum_sql, $this->key_value);
                    $sum_row = $this->conn->fetch('object');

                    $this->ret->total_point  = $sum_row->total_point;

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