<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class contents_model extends CI_Model {
    private $auth_user = null;
    private $conn;
    private $conn2;
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

        $database = array('host'=>'localhost' , 'user'=>'dbuser' , 'pass'=>'sdkfasn@1@#dnfh' , 'source' => 'blockchain');

        $this->conn = new MysqlConnection();
        $this->conn2 = new MysqlConnection(null  , $database , true);

        $this->ret = new \stdClass;
        $this->key_value = array();
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

    public function getContentsSingleInfoCcid($params){
        if(strlen($params->ccid) < 1 && strlen($params->version)){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM CONTENTS AS t1 ";
        $sql .= "WHERE t1.ccid = ? AND ccid_ver = ?  LIMIT 1";

        $this->key_value = array($params->ccid , $params->version) ;
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

    public function getContentsLastSingleInfoByUserId($params){
        if(strlen($params->eth_account) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT t1.contents_id, t1.ccid, t1.ccid_ver ";
        $sql .= "FROM CONTENTS AS t1 ";
        $sql .= "WHERE t1.userid = ? ORDER BY contents_id DESC LIMIT 1";

        $this->key_value = array($params->eth_account);
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

    public function getContentsNextVal($params){
        if(strlen($params->init_key) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM CONTENTS AS t1 ";
        $sql .= "WHERE t1.init_key = ? LIMIT 1";

        $this->key_value = array($params->init_key);
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

    public function postContentsReg($params){
        if(strlen($params->init_key) < 1 && count($params->fileinfo) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "INSERT INTO CONTENTS SET ";
        //$sql .= "cate1 =  ?, ";
        //$sql .= "cate2 = ?, ";
        $sql .= "userid = ?, ";
        $sql .= "cate1 =  ? ,";
        $sql .= "title = ?, ";
        $sql .= "contents = ?, ";
        $sql .= "is_folder = ?, ";
        $sql .= "folder_name = ?, ";
        $sql .= "size = ?, ";
        $sql .= "wdate = UNIX_TIMESTAMP(), ";
        $sql .= "sort = ?, ";
        $sql .= "hash_tags = ? ,";
        $sql .= "main_img = ? ,";
        $sql .= "sub_img = ? ,";
        $sql .= "cid = ? ,";
        $sql .= "ccid = ? ,";
        $sql .= "ccid_ver = ? ,";
        $sql .= "cash = ? , ";
        $sql .= "watermarking = ? , ";
        $sql .= "drm = ? , ";
        $sql .= "is_adult = ? , ";
        $sql .= "init_key = ? ";

        $this->key_value = array(
            $params->userid,
            $params->genre,
            $params->title,
            $params->contents,
            $params->is_folder,
            $params->folder_name,
            $params->size,
            $params->sort,
            $params->hash_tags,
            $params->main_img,
            $params->sub_img,
            $params->cid,
            $params->ccid,
            $params->ccid_ver,
            $params->fee,
            $params->watermarking,
            $params->drm,
            $params->is_adult,
            $params->init_key,
        );

        $this->conn->begin_transaction();
        $result_arr = array();
        if($this->conn->query($sql, $this->key_value )){
            $result_arr[] = $this->conn->isError();
            $contents_id = $this->conn->insertId();

            if(isset($contents_id)) {
                $values = array();
                $insert_values = "";

                $file_sql = "INSERT INTO CONTENTS_FILE (contents_id , userid  , folder , filename , realsize , size , state , wdate ,sort) VALUES ";
                foreach($params->fileinfo as $key=>$val){
                    $exp_val = explode('|&|' , $val);
                    $exp_title = explode('\\' , $exp_val[0]);

                    if ($insert_values != "") $insert_values .= ", ";
                    $insert_values .= "(?, ?, ?, ?, ?, ? ,? ,? ,?)";

                    $folder = $params->folder_name ?  $params->folder_name : str_replace("\\".end($exp_title) , "" , $exp_val[0]);

                    array_push($values, $contents_id,  $params->userid, $folder, end($exp_title), $exp_val[1] , $exp_val[1] ,  'U', time() , ($key + 1));
                }

                $file_sql .= $insert_values;

                if($insert_values) {
                    $this->conn->query($file_sql, $values);
                    $result_arr[] = $this->conn->isError();
                }

                $basicMeta = array(
                    'target'=> array(),
                    'meta-type'=> 'basic-movie.v1',
                    'metadata'=> array(
                        'vender_id'=> $contents_id,
                        'country'=> $params->country,
                        'original_spoken_locale'=> $params->original_spoken_locale,
                        'title'=> $params->title,
                        'synopsis'=> $params->synopsis,
                        'production_company'=> $params->production_company,
                        'copyright_cline'=> $params->copyright_cline,
                        'theatrical_release_date'=> $params->theatrical_release_date,
                        'genre'=> $params->basicMeta_genre,
                        'ratings'=> $params->ratings,
                        'cast'=> array(),
                        'crew'=> array(),
                        'artwork'=> array()
                    )
                );
        
                if( count($params->fileinfo) ) {
                    foreach($params->fileinfo as $key=>$val){
                        $exp_val = explode('|&|' , $val);
                        array_push($basicMeta['target'], $exp_val[0]);
                    }
                }
        
                if( count($params->cast_name) ) {
                    foreach($params->cast_name as $key=>$val) {
                        $cast_item = array(
                            'name'=> $params->cast_name[$key],
                            'artist_id'=> '',
                            'cast_name'=> $params->cast_cast_name[$key],
                        );
        
                        array_push($basicMeta['metadata']['cast'], $cast_item);
                    }
                }
        
                if( count($params->crew_name) ) {
                    foreach($params->crew_name as $key=>$val) {
                        $crew_item = array(
                            'name'=> $params->crew_name[$key],
                            'artist_id'=> '',
                            'role'=> $params->crew_role[$key]
                        );
        
                        array_push($basicMeta['metadata']['crew'], $crew_item);
                    }
                }

                $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                    "contents_id"=> $contents_id
                ));

                $img_format_array = array('jpeg'=>'i01' , 'jpg'=>'i02' , 'png'=>'i03' , 'gif'=>'i04' , 'bmp'=>'i05');

                if( $contents_info->data->main_img ) {
                    $main_img_item = explode('|', $contents_info->data->main_img);

                    if($contents_info->data->main_img) {
                        $main_img_exp = explode('|', $contents_info->data->main_img);
                        $exp_main_img = explode('\\' , $main_img_exp[0]);
                        $ext = substr(strrchr(end($exp_main_img), '.'), 1);
    
                        $basicMeta['metadata']['artwork'][0]['title'] = $params->title;
                        $basicMeta['metadata']['artwork'][0]['file_name'] = $main_img_exp[0];
                        $basicMeta['metadata']['artwork'][0]['file_size'] = (int) $main_img_exp[1];
                        $basicMeta['metadata']['artwork'][0]['rep'] = 'true';
                        $basicMeta['metadata']['artwork'][0]['height'] = (int)(@$main_img_exp[3] ? @$main_img_exp[3] : 0 );
                        $basicMeta['metadata']['artwork'][0]['width'] = (int)(@$main_img_exp[2] ? @$main_img_exp[2] : 0 );
                        $basicMeta['metadata']['artwork'][0]['format'] = $img_format_array[strtolower($ext)];
                    }
                }

                if( $contents_info->data->sub_img ) {
                    $artworkIdx = count($basicMeta['metadata']['artwork']);

                    $sub_img_items = explode(',', $contents_info->data->sub_img);
                    
                    foreach($sub_img_items as $sub_img_item) {
                        $sub_img_exp = explode('|', $sub_img_item);
                        $exp_sub_img = explode('\\' , $sub_img_exp[0]);
                        $ext = substr(strrchr(end($exp_sub_img), '.'), 1);

                        $basicMeta['metadata']['artwork'][$artworkIdx]['title'] = $params->title;
                        $basicMeta['metadata']['artwork'][$artworkIdx]['file_name'] = $sub_img_exp[0];
                        $basicMeta['metadata']['artwork'][$artworkIdx]['file_size'] = (int) $sub_img_exp[1];
                        $basicMeta['metadata']['artwork'][$artworkIdx]['rep'] = 'false';
                        $basicMeta['metadata']['artwork'][$artworkIdx]['height'] = (int)(@$sub_img_exp[3] ? @$sub_img_exp[3] : 0 );
                        $basicMeta['metadata']['artwork'][$artworkIdx]['width'] = (int)(@$sub_img_exp[2] ? @$sub_img_exp[2] : 0 );
                        $basicMeta['metadata']['artwork'][$artworkIdx]['format'] = $img_format_array[strtolower($ext)];

                        $artworkIdx++;
                    }
                }

                $json_basicMeta = json_encode($basicMeta);

                $basicMetaSql = "UPDATE CONTENTS SET metainfo = ? WHERE contents_id = ?";

                $basicMetaValue = array(
                    $json_basicMeta,
                    $contents_id
                );

                $this->conn->query($basicMetaSql, $basicMetaValue);
                $result_arr[] = $this->conn->isError();
            }

            if($this->conn->is_commit($result_arr)){
                $this->conn->commit();
                $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("contents_id" => $contents_id ));
            }else{
                $this->conn->rollback();
                $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
            }


        }
        return $response;
    }

    public function postContentsUpdate($params){
        $contents_id = isset($params->contents_id) && $params->contents_id ? $params->contents_id : '';

        if( !$contents_id ){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE CONTENTS SET ";
        $sql .= "userid = ?, ";
        $sql .= "cate1 =  ? ,";
        $sql .= "title = ?, ";
        $sql .= "contents = ?, ";
        $sql .= "hash_tags = ?, ";
        $sql .= "metainfo = ? ";
        $sql .= "WHERE contents_id = ? ";

        $metainfo = '';

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"=> $contents_id
        ));

        if( $contents_info->data->metainfo ) {
            $contents_info->data->metainfo = json_decode($contents_info->data->metainfo);

            $contents_info->data->metainfo->metadata->country = $params->country;
            $contents_info->data->metainfo->metadata->original_spoken_locale = $params->original_spoken_locale;
            $contents_info->data->metainfo->metadata->title = $params->title;
            $contents_info->data->metainfo->metadata->synopsis = $params->synopsis;
            $contents_info->data->metainfo->metadata->production_company = $params->production_company;
            $contents_info->data->metainfo->metadata->copyright_cline = $params->copyright_cline;
            $contents_info->data->metainfo->metadata->theatrical_release_date = $params->theatrical_release_date;
            $contents_info->data->metainfo->metadata->genre = $params->genre;
            $contents_info->data->metainfo->metadata->ratings = $params->ratings;

            if( count($params->cast_name) ) {
                $contents_info->data->metainfo->metadata->cast = array();

                foreach($params->cast_name as $key=>$val) {
                    $cast_item = array(
                        'name'=> $params->cast_name[$key],
                        'artist_id'=> '',
                        'cast_name'=> $params->cast_cast_name[$key],
                    );
    
                    array_push($contents_info->data->metainfo->metadata->cast, $cast_item);
                }
            }
    
            if( count($params->crew_name) ) {
                $contents_info->data->metainfo->metadata->crew = array();

                foreach($params->crew_name as $key=>$val) {
                    $crew_item = array(
                        'name'=> $params->crew_name[$key],
                        'artist_id'=> '',
                        'role'=> $params->crew_role[$key]
                    );
    
                    array_push($contents_info->data->metainfo->metadata->crew, $crew_item);
                }
            }

            $metainfo = json_encode($contents_info->data->metainfo);
        }

        $this->key_value = array(
            $params->userid,
            $params->genre,
            $params->title,
            $params->contents,
            $params->hash_tags,
            $metainfo,
            $contents_id
        );

        $result_arr = array();

        $this->conn->begin_transaction();
        if($this->conn->query($sql, $this->key_value)){
            $result_arr[] = $this->conn->isError();

            if($this->conn->is_commit($result_arr)){
                $this->conn->commit();
                $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("contents_id" => $contents_id ));
            }else{
                $this->conn->rollback();
                $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
            }
        }else {
            $this->conn->rollback();
            $response = Utils::customizeResponse("997", "997", $this->conn->getError(), "");
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


    public function getContentsList($params){
        if(strlen($params->userid) < 1 && strlen($params->user_auth)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT * , (SELECT sum(t2.realsize) FROM CONTENTS_FILE AS t2 WHERE t1.contents_id = t2.contents_id) as total_realsize ";
        $table_sql = "FROM CONTENTS AS t1 ";

        $where_sql = "WHERE 1=1 ";
        if($params->user_auth != 9){
            $where_sql .= " AND t1.userid = ? ";
        }
        if(@$params->is_dataid == "Y"){
            $where_sql .= " AND t1.dataid != '' ";
        }

        $order_sql = "ORDER BY t1.wdate DESC ";

        $this->key_value = array($params->userid);

        if(@$params->page_yn == "Y"){
            $count_sql .= $table_sql.$where_sql;
            $this->conn->query($count_sql, $this->key_value );
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

    public function getUniqueKey($params){
        if(strlen($params->type) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "SELECT ".$params->type." FROM CONTENTS  ";
        $sql .= "WHERE ".$params->type." = ?  ";

        $this->key_value = array($params->type);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $row = $this->conn->fetch();
            if(!isset($row->$params->type)){
                $response = Utils::customizeResponse("200", "200", "SUCC.", "");
            }else{
                $response = Utils::customizeResponse("201", "201", "REPEAT", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }

    public function modContentsInfo($params){
        if(count($params) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


        foreach($params as $key=>$val) {
            if ($key != 'contents_id') {
                $update_field[] = $key . "=" . "'" . $val . "'";
            }
        }

        $sql = "UPDATE  CONTENTS  SET ".implode(',' , $update_field)." WHERE contents_id = ?  ";
        $this->key_value = array($params->contents_id);
        $this->conn->query($sql, $this->key_value);
        if(!$this->conn->isError()){
            $response = Utils::customizeResponse("200", "200", "SUCC", $this->ret);
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }
        return $response;
    }


    public function getAllContentsListD($params){

        if(strlen($params->userid) < 1 && strlen($params->user_auth)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t1.*  ";
        if($params->user_auth < 9 ) {
            $list_sql .= " , (SELECT t2.user_sell_contents_id FROM USER_SELL_CONTENTS AS t2 WHERE t1.contents_id = t2.contents_id AND t2.userid = '" . $params->userid . "') AS user_sell_contents_id";
            $list_sql .= " , (SELECT t2.productId FROM USER_SELL_CONTENTS AS t2 WHERE t1.contents_id = t2.contents_id AND t2.userid = '" . $params->userid . "') AS productId";
        }
        $table_sql = " FROM CONTENTS AS t1 ";
        $where_sql = " WHERE t1.state = ?  AND t1.ccid != '' AND t1.ccid_ver != '' AND dataid != '' ";

        $order_sql = "ORDER BY t1.wdate DESC ";

        $this->key_value = array($params->state);

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


    public function getSellContentsListD($params){

        if(strlen($params->userid) < 1 && strlen($params->user_auth)  < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $count_sql = "SELECT COUNT(*) AS cnt ";

        $list_sql = "SELECT t2.* , t1.user_sell_contents_id  , t1.cash as real_cash";
        $table_sql = " FROM USER_SELL_CONTENTS AS t1 ";
        $table_sql .= " LEFT JOIN CONTENTS AS t2  ON t1.contents_id = t2.contents_id ";
        $where_sql = " WHERE t2.state = ?  AND t2.ccid != '' AND t2.ccid_ver != '' ";
        if($params->user_auth < 9){
            $where_sql .= " AND t1.userid =  '".$params->userid."' ";
        }

        $order_sql = "ORDER BY t2.wdate DESC ";

        $this->key_value = array($params->state);

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


    public function getUserSellSingleConntents($params){
        if(strlen($params->contents_id) < 1 && strlen($params->userid) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }
        $sql = "SELECT t1.* ";
        $sql .= "FROM USER_SELL_CONTENTS AS t1 ";
        $sql .= "WHERE t1.contents_id = ? AND userid = ? LIMIT 1";

        $this->key_value = array($params->contents_id , $params->userid);
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


    public function postSellerContents($params){
        if(strlen($params->contents_id) < 1 && strlen($params->real_cash) < 1 && strlen($params->priductId) < 1 && strlen($params->seller_userid) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $query[] = 'contents_id = ? ';
        $query[] = 'userid = ? ';
        $query[] = 'wdate = ? ';
        $query[] = 'cash = ? ';
        $query[] = 'del_yn = ? ';
        $query[] = 'productId = ? ';

        $sql = "INSERT INTO  USER_SELL_CONTENTS SET ".  implode(',' , $query);
        $this->key_value = array($params->contents_id , $params->seller_userid , time() , $params->real_cash , 'N', $params->productId);
        $this->conn->query($sql, $this->key_value);
        $user_sell_contnets_id = $this->conn->insertId();

        if(!$this->conn->isError()){

            /*if(!$user_sell_contnets_id){
                $response = Utils::customizeResponse("998", "998", "FAIL", "");
            }else{

                if($user_sell_contnets_id){

                    $this->conn2->begin_transaction();

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
                    $contents_query[] = "productId = ? ";
                    $contents_query[] = "seller_userid = ? ";
                    $contents_query[] = "real_cash = ? ";
                    $contents_query[] = "user_sell_contents_id = ? ";
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
                        $params->productId,
                        $params->seller_userid,
                        $params->real_cash,
                        $user_sell_contnets_id,
                        $params->contents_data->metainfo,
                        $params->contents_data->dataid
                    );

                    if($this->conn2->query($contetns_sql, $contents_values)){
                        $result_arr[] = $this->conn2->isError();
                        $contnets_id = $this->conn2->insertId();

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
                                $this->conn2->query($contents_file_qry, $values);
                                $result_arr[] = $this->conn2->isError();

                                if($this->conn2->is_commit($result_arr)){
                                    $this->conn2->commit();
                                    $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("contents_id" => $contnets_id));
                                }else{
                                    $this->conn2->rollback();
                                    $response = Utils::customizeResponse("997", "997", $this->conn2->getError(), "");
                                }

                            }
                        }else{
                            $this->conn2->rollback();
                            $response = Utils::customizeResponse("997", "997", $this->conn2->getError(), "");
                        }
                    }else{
                        $this->conn2->rollback();
                        $response = Utils::customizeResponse("999", "999", $this->conn2->getError(), "");
                    }


                }else{
                    $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
                }


            }*/
            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("user_sell_contents_id" => $user_sell_contnets_id));
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }

        return $response;

    }


    public function modSellerContents($params){
        if(strlen($params->contents_id) < 1 && strlen($params->real_cash) < 1 && strlen($params->priductId) < 1 && strlen($params->seller_userid) < 1) {
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }

        $sql = "UPDATE USER_SELL_CONTENTS SET  cash = ? WHERE user_sell_contents_id = ? ";
        $this->key_value = array($params->real_cash , $params->user_sell_contents_id);
        $this->conn->query($sql, $this->key_value);

        if(!$this->conn->isError()) {

            /*$contents_sql = "UPDATE CONTENTS SET real_cash = ? WHERE user_sell_contents_id =  ?  AND productId = ? ";
            $contents_value = array($params->real_cash , $params->user_sell_contents_id , $params->productId);
            $this->conn2->query($contents_sql, $contents_value);
            if(!$this->conn2->isError()) {
                $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("user_sell_contents_id" => $params->user_sell_contents_id));
            }else{
                $response = Utils::customizeResponse("999", "999", $this->conn2->getError(), "");
            }*/
            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("user_sell_contents_id" => $params->user_sell_contents_id));

        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn->getError(), "");
        }

        return $response;
    }




    /*public function getCircleUserInfo(){

        $sql = "SELECT t1.* ";
        $sql .= "FROM MANAGER_INFO AS t1 ";
        $sql .= "WHERE t1.manager_info_id = ? ";

        $this->key_value = array(1);

        $this->conn2->query($sql, $this->key_value ,1);

        if(!$this->conn2->isError()){
            $row = $this->conn2->fetch();
            if(!isset($row->user_info_id)){
                $response = Utils::customizeResponse("998", "998", "데이터 조회 실패.", "");
            }else{
                $response = Utils::customizeResponse("200", "200", "SUCC", $row);
            }
        }else{
            $response = Utils::customizeResponse("999", "999", $this->conn2->getError(), "");
        }
        return $response;
    }*/



}
?>