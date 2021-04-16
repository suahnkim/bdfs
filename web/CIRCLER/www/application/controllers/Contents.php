<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( !function_exists( 'hex2bin' ) ) {
    function hex2bin( $str ) {
        $sbin = "";
        $len = strlen( $str );
        for ( $i = 0; $i < $len; $i += 2 ) {
            $sbin .= pack( "H*", substr( $str, $i, 2 ) );
        }

        return $sbin;
    }
}

class contents extends CI_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('contents_model');

    }

    public function _remap($method, $params = array()){
        if($this->input->is_ajax_request()) $method = '_'.$method;
        if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "_check_eth_account" || $method == "_login_view" || $method == "setBlockChainContentsInfo" || $method == 'externalImageFtpUpload' || $method == 'setBlockChainContentsInfoApi'){
            if($this->user_model->isSignedIn() && $method == "signin"){
                header("Location: /");
                return;
            }
        }else{
            if(!$this->user_model->isSignedIn()){
                if($this->input->is_ajax_request()){
                    http_response_code(200);
                    echo json_encode(array("code"=>401, "message"=>"로그인 후 이용하실 수 있습니다."));
                    return;
                }else{
                    header("Location: /user/signin");
                    return;
                }
            }
        }
        if(method_exists($this, $method)){
            if(empty($params)){
                $this->{$method}();
            }else{
                call_user_func_array(array($this, $method), $params);
            }
        }else{
            if($this->input->is_ajax_request()){
                http_response_code(200);
                echo json_encode(array("code"=>404, "message"=>"404"));
            }else{
                show_404();
            }
            return;
        }
    }

    public function signin(){
        $this->load->view(MC_VIEWS_PATH."/inc/header");
        $this->load->view(MC_VIEWS_PATH."/login/default");
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
    }

    public function logout(){
        $this->user_model->doSignOut();
        header("Location: /");
    }


    private function _ajax_view(){
        $user = $this->user_model->getUser();
        $request_params = (object)array(
            'contents_id'            => $this->input->post('contents_id' , true),
        );

        if($request_params->contents_id > 0) {
            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id" => $request_params->contents_id
            ));

            if ($contents_info->code == 200 && $contents_info->data->contents_id > 0) {
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id" => $contents_info->data->contents_id
                ));

                foreach ($contents_file_info->data->rows as $key => $val) {
                    $contents_file_info->data->rows[$key]->realsize_str = getFileSizeStr($val->realsize);
                }

                $contents_info->data->rows = $contents_file_info->data->rows;

                $purchase_info = $this->contents_model->getPurchaseInfo((object)array(
                    "contents_id"           =>  $contents_info->data->contents_id,
                    "accountId"             =>   $user->eth_account,
                ));

                $zzim_info = $this->contents_model->getContentsZzimInfo((object)array(
                    "contents_id"           =>  $contents_info->data->contents_id,
                    "accountId"             =>   $user->eth_account,
                    "del_yn"                  =>    'N',
                ));

                $contents_info->data->purchase = $purchase_info->message == 'RE' ? 1 : 0;
                $contents_info->data->zzim = $zzim_info->code == '201' ? 1 : 0;
                $contents_info->data->user_down = isset($purchase_info->data->down) && $purchase_info->data->down ? $purchase_info->data->down : 0;
            }

            $res = array();
            ob_start();
            $this->load->view("/contents/ajax/contents_view", array(
                "user"=>$user,
                "data" => $contents_info->data
            ));
            $res['result']  = true;
            $res['contents_html'] = ob_get_contents();
            ob_end_clean();

            echo json_encode($res);
            exit;
        }
    }

    public function form(){
        $this->load->view(MC_VIEWS_PATH."/contents/form");
    }

    public function down(){
        //271

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => 271
        ));

        if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
            $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                "contents_id"           => $contents_info->data->contents_id
            ));
            $contents_info->data->rows =  $contents_file_info->data->rows;
        }
        $this->load->view(MC_VIEWS_PATH."/contents/down" ,array(
            "data"               => $contents_info->data
        ));
    }

    private function _check_eth_account(){
        $request_params = (object)array(
            "account_list"			=> $this->input->get("account_list", true),
        );

        $request_params->account_arr = explode(",", $request_params->account_list);
        $response = Utils::customizeResponse("200", "200", "SUCC", (object)array());
        $response->data->list = array();

        foreach($request_params->account_arr as $key => $val){
            $result = $this->user_model->getEthAccountSingle((object)array(
                "account"			=> $val,
            ));

            if($result->code == "200"){
                array_push($response->data->list, (object)array("account" => $val, "email" => $result->data->email));
            }else{
                array_push($response->data->list, (object)array("account" => $val, "email" => ''));
            }
        };

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function complete(){
        $request_params = (object)array(
            "s"		=> $this->input->get('s', true),
            "contents_no"			=> $this->input->get('contents_no', true),
            "file_no"			    => $this->input->get('file_no', true), '',
        );

        if(strlen($request_params->contents_no) < 1 || strlen($request_params->file_no) < 1){
            return Utils::customizeResponse(400, 400, "This is not a valid request.", null);
        }


        $contents_response = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"               => $request_params->contents_no,
        ));

        $contents_file_response = $this->contents_model->getContentsFileSingleInfo((object)array(
            "contents_id"               => $request_params->contents_no,
            "contents_file_no"         => $request_params->file_no
        ));

        if($contents_file_response->code == 200 && $contents_file_response->data->contents_file_id){

            $put_file_response  = $this->contents_model->putContentsFile((object)array(
                "contents_id"               =>  $request_params->contents_no ,
                "contents_file_no"         => $request_params->file_no,
                "state"                         => $request_params->s == "C" ? "P" : "D"
            ));

            if($put_file_response->code == 200){
                $response = Utils::customizeResponse("200", "200", "SUCC", null);

                $this->load->library('RestApi');
                $this->restapi = new RestApi("https://203.229.154.79:9800");

                $package_response = $this->restapi->post("/drm/statusInfo.do", array(
                    "data"=> array(
                        "user_id"            => $contents_file_response->data->userid,
                        "next_val"           => $contents_response->data->init_key,
                    ),
                ));

                //print_r($package_response);

            }else{
                $response = Utils::customizeResponse("200", "400", "FAIL", null);
            }
        }else{
            $response = Utils::customizeResponse("200", "400", "FAIL", null);
        }

        return $response;

    }

    public function lists($method = ''){

        $user = $this->user_model->getUser();

        $request_params = (object)array(
            "page_num"      => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"     => $this->input->get('pageSize', true) == "" ? "18" : $this->input->get('pageSize', true),
            "srch_key"      => $this->input->get('srch_key', true),
            "srch_value"    => $this->input->get('srch_value', true),
        );

        switch ($method){
            case 'popular' :
                $response = $this->contents_model->getContentsPopularList((object)array(
                    "page_yn"       => 'Y',
                    "page_num"      => $request_params->page_num,
                    "page_size"     => $request_params->page_size,
                    "srch_key"      => $request_params->srch_key,
                    "srch_value"    => $request_params->srch_value,
                ));
                break;
            case 'recent' :
                $response = $this->contents_model->getContentsList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
            case 'recommand' :
                $response = $this->contents_model->getContentsRecommandList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
            default :
                $response = $this->contents_model->getContentsList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
        }



        switch($method){
            case 'popular' : $sub_title= '인기 콘텐츠'; break;
            case 'recent' : $sub_title= '새로운 콘텐츠'; break;
            case 'recommand' : $sub_title= '추천 콘텐츠'; break;
            default : $sub_title = '콘텐츠리스트'; break;
        }

        $this->load->view("/inc/header" , (object)array(
            "user"                 => $user
        ));
        $this->load->view("/inc/top");
        $this->load->view("/contents/list" , (object)array(
            "data"                  => $response->data,
            "user"                  => $user,
            "sub_title"              => $sub_title,
        ));
        $this->load->view("/inc/footer_js");
        $this->load->view("/inc/footer");

    }

    public function setBlockChainContentsInfo(){

        //$aaa = print_r($_REQUEST , true);
        //file_put_contents('log.txt' , "-------------  json contents info print ---------------------".date('YmdHis')."----------------------\n\n".$aaa ."\n\r" , FILE_APPEND);

        $request_params = (object)array(
          'action_type'       =>   $this->input->post('action_type' , true),
          'contents_info'    =>   $this->input->post('contents_info' , true),
          'productId'          =>   $this->input->post('productid' , true),
          'real_cash'         =>   $this->input->post('real_cash' , true),
        );

        if($request_params->productId){
            $contents_json = json_decode($request_params->contents_info);

            $contents_info = $this->contents_model->getContentsProductInfo((object)array(
                'productId'             =>          $request_params->productId
            ));

            if($contents_info->code == 200){

            }else{
                $response = $this->contents_model->postContentsReg((object)array(
                    'contents_data'         => $contents_json,
                    'productId'               => $request_params->productId,
                    'real_cash'              => $request_params->real_cash,
                ));
            }

        }else{
            $response = Utils::customizeResponse("200", "500", "required productid!.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);

    }

    public function setBlockChainContentsInfoApi(){

        $aaa = print_r($_REQUEST , true);
        file_put_contents('log.txt' , "-------------  json contents info print ---------------------".date('YmdHis')."----------------------\n\n".$aaa ."\n\r" , FILE_APPEND);

        $request_params = (object)array(
            'action_type'       =>   $this->input->post('action_type' , true),
            'contents_info'    =>   $this->input->post('contents_info' , true),
            'productId'          =>   $this->input->post('productid' , true),
            'real_cash'         =>   $this->input->post('real_cash' , true),
        );

        if($request_params->productId){

            $json = json_decode($request_params->contents_info);
            $contents_json = json_decode(base64_decode($json->result[0]->meta_container[0]->metadata->contents_info));
            $contents_json->ccid = $json->result[0]->ccid;
            $contents_json->ccid_ver = $json->result[0]->version;
            // djkim ftp file upload 주석 처리
            /*
            if(count($json->result[0]->meta_container[0]->metadata->artwork) >0){
                $img_cnt = 0;
                $subimg = array();

                preg_match("/(([a-z0-9\-]+\.)*)([a-z0-9\-]+)\.([a-z]{3,4}|[a-z]{2,3}\.[a-z]{2})(\:[0-9]+)?$/", $_SERVER['HTTP_HOST'], $matches);
                $sub_domain = null;
                if($matches[1]) {
                    $image_sub_domain = substr($matches[1], 0, -1)  == 'dev' ? 'dev.coimg.circler.co.kr' : 'coimg.circler.co.kr';
                }else{
                    $image_sub_domain = 'coimg';
                }

                
                foreach($json->result[0]->meta_container[0]->metadata->artwork as $key=>$val){
                    if( preg_match("/\.(gif|jpg|jpeg|png|bmp)$/i", strtolower($val->file_name))){
                        if($img_cnt == 0){
                            $main_img_url = $this->externalImageFtpUpload((object)array(
                                'ccid'          => $json->result[0]->ccid,
                                'version'      => $json->result[0]->version,
                                'path'          => $val->file_name,
                            ));
                            if($main_img_url){
                                $main_img = "http://".$image_sub_domain .'/' .$main_img_url;
                            }

                            $img_cnt++;

                        }else{
                            $sub_img_url = $this->externalImageFtpUpload((object)array(
                                'ccid'          => $json->result[0]->ccid,
                                'version'      => $json->result[0]->version,
                                'path'          => $val->file_name,
                            ));
                            if($sub_img_url) $subimg[] = "http://".$image_sub_domain .'/' .$sub_img_url;
                            $img_cnt++;
                        }
                    }
                }

                if($main_img) $contents_json->main_img = $main_img;
                if(count($subimg) > 0){
                    $contents_json->sub_img = implode(',' ,$subimg);
                }else{
                    $contents_json->sub_img = "";
                }
            }
            */
            $contents_info = $this->contents_model->getContentsProductInfo((object)array(
                'productId'             =>          $request_params->productId
            ));

            if($contents_info->code == 200){

            }else{
                $response = $this->contents_model->postContentsReg((object)array(
                    'contents_data'         => $contents_json,
                    'productId'               => $request_params->productId,
                    'real_cash'              => $request_params->real_cash,
                ));
            }

        }else{
            $response = Utils::customizeResponse("200", "500", "required productid!.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);

    }

    private function _purchaseInfo(){
        $user = $this->user_model->getUser();
        /*
            "user_info_id"			    => $user->user_info_id,
            "accountId"                => $user->eth_account,
         */
        $request_params = (object)array(
            "contents_id"		        => $this->input->post('contents_id', true),
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id
        ));

        if($contents_info->data->contents_id && $contents_info->code == 200){

            $today = time();
            $expire_day = $today + ((60 * 60 * 24) * 3);

            $response = $this->contents_model->getPurchaseInfo((object)array(
                "contents_id"                      =>  $request_params->contents_id,
                "accountId"                        =>   $user->eth_account,
            ));

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _contentsPurchase(){

        $user = $this->user_model->getUser();

        $today = time();
        $expire_day = $today + ((60 * 60 * 24) * 3);

        $request_params = (object)array(
            "contents_id"		                => $this->input->post('contents_id', true),
            "blockchain_purchaseId"     =>  $this->input->post('blockchain_purchaseId' , true),
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id
        ));


        $response = $this->contents_model->contentsPurchase((object)array(
            "accountId"                      => $user->eth_account,
            "contents_id"                    => $request_params->contents_id,
            "point"                             => $contents_info->data->real_cash,
            "state"                             => 'Y',
            "wdate"                           => $today,
            "edate"                            => $expire_day,
            "blockchain_purchaseId"   => $request_params->blockchain_purchaseId,
            "title"                               => $contents_info->data->title,
            "add_cnt_type"                  => 'purchase',
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }


    private function _form_submit(){

        $this->load->library('RestApi');
        $request_params = (object)array(
            "cont_name"		=> $this->input->post('cont_name', true),
            "contents"			=> $this->input->post('contents', true),
            "fileinfo"			    => $this->input->post('fileinfo', true),
            "folderpath"        => $this->input->post('folderpath' , true) ? $this->input->post('folderpath' , true) : '',
        );

        $tot_count = count($request_params->fileinfo);
        $tot_size = 0;
        foreach($request_params->fileinfo as $key=>$val){
            $exp_val = explode('|&|' , $val);
            $tot_size += $exp_val[1];
        }

        $user_id = "test";

        $this->load->library('RestApi');
        $this->restapi = new RestApi("https://203.229.154.79:9800");

        $init_response = $this->restapi->post("/drm/uploadinit.do", array(
            "data"			=> array(
                "user_id"            => $user_id,
                "tot_count"         => (int)$tot_count,
                "tot_size"           => (int)$tot_size,
                "cont_name"       =>  $request_params->cont_name,
                "ccid"                => "testccid",
                "ccid_ver"          => "ver1",
                "drm_yn"            => "y",
                "id_sign"           =>  $this->String2Hex($user_id . $tot_count . $tot_size . $request_params->cont_name . 'y'),
            ),
        ));

      if($init_response['info']['http_code'] == 200){
          $return_json =  json_decode($init_response['data']);
         if($return_json->code == 0 && !empty($return_json->next_val)){

              $contents = substr(trim($request_params->contents),0,65536);
              $contents = preg_replace("#[\\\]+$#",'',$contents);

              $contents_response = $this->contents_model->postContentsReg((object)array(
                  "userid"				=> $user_id,
                  "title"                   => $request_params->cont_name,
                  "contents"            => $contents,
                  "is_folder"            =>  $request_params->folderpath ? 'Y' : 'N',
                  "folder_name"       => $request_params->folderpath,
                  "size"                  => $tot_size,
                  "wdate"                => time(),
                  "edate"                => time()  + (60 * 60 * 24 * 999),
                  "sort"                   => 1,
                  "init_key"              => $return_json->next_val,
                  "fileinfo"               => $request_params->fileinfo
              ));

              if($contents_response->code == 200 && isset($contents_response->data->contents_id)){

                  $file_info = $this->contents_model->getContentFileInfo((object)array(
                      "contents_id"             =>  $contents_response->data->contents_id ,
                      "page_yn"                 => 'N'
                  ));
                  $response = $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("next_val" => $return_json->next_val , "fileinfo" => $file_info->data , "user_id"=>$user_id));
              }
          }else{
             $response = Utils::customizeResponse("200", "400", "API호출에 실패하였습니다1.", null);
         }
      }else{
          $response = Utils::customizeResponse("200", "400", "API호출에 실패하였습니다2.", null);
      }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _ajaxContentsLists(){
        $user = $this->user_model->getUser();

        $request_params = (object)array(
            "page_num"			                           => $this->input->post('pageNum', true) == "" ? "1" : $this->input->post('pageNum', true),
            "page_size"			                           => $this->input->post('pageSize', true) == "" ? "18" : $this->input->post('pageSize', true),
            "srch_key"			                               => $this->input->post('srch_key', true),
            "srch_value"			                           => $this->input->post('srch_value', true),
            "segment"                                         => $this->input->post('segment' , true),
        );

        switch ($request_params->segment){
            case 'popular' :
                $response = $this->contents_model->getContentsPopularList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
            case 'recent' :
                $response = $this->contents_model->getContentsList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
            case 'recommand' :
                $response = $this->contents_model->getContentsRecommandList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
            default :
                $response = $this->contents_model->getContentsList((object)array(
                    "page_yn"                   => 'Y',
                    "page_num"			     => $request_params->page_num,
                    "page_size"			     => $request_params->page_size,
                    "srch_key"                  => $request_params->srch_key,
                    "srch_value"               => $request_params->srch_value,
                ));
                break;
        }


        if($response->code == 200){
            if(count($response->data->rows) > 0){
                foreach($response->data->rows as $key=>$val){
                    $response->data->rows[$key]->datetime = get_datetime($val->wdate);
                    $response->data->rows[$key]->number_real_cash = number_format($val->real_cash);
                }
            }
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function  _contentsZzim(){
        $user = $this->user_model->getUser();
        $request_params = (object)array(
            "contents_id"			                        => $this->input->post('contents_id', true),
            "accountId"		                            => $user->eth_account
        );

        $zzim_info = $this->contents_model->getContentsZzimInfo((object)array(
            "contents_id"			                        => $request_params->contents_id,
            "accountId"		                            => $user->eth_account,
            "del_yn"                                        => 'N'
        ));

        if($zzim_info->code == 200){
            $response = $this->contents_model->setContentsZzim((object)array(
                'contents_id'       => $request_params->contents_id,
                'accountId'         => $user->eth_account,
            ));
        }else{
            $response = Utils::customizeResponse("200", "201", $zzim_info->message, '');
        }

        echo json_encode($response);
        http_response_code($response->status);
    }


    private function _contentsRecommand(){
        $user = $this->user_model->getUser();
        $request_params = (object)array(
            "contents_id"	=> $this->input->post('contents_id', true),
            "accountId"    => $user->eth_account
        );

        $recommand_log = $this->contents_model->getContentsRecommandLog((object)array(
            "contents_id"      =>      $request_params->contents_id,
            "accountId"        =>      $request_params->accountId,
        ));

        if($recommand_log->code == 200){

            $response = $this->contents_model->setContentsRecommand((object)array(
                "contents_id"      =>      $request_params->contents_id,
                "accountId"        =>      $request_params->accountId,
            ));

        }else{
            $response = Utils::customizeResponse("200", "201", $recommand_log->message, '');
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    public function externalImageFtpUpload($params){

        $file = 'http://15.164.5.18:80/ccsearch/v1/ccontent/'.$params->ccid.'/'.$params->version.'/'.str_replace(' ' , '%20',$params->path);
        $images = file_get_contents($file);
        $filename  = basename($file);
        $create_filename  = time() ."_". md5($filename) .'.'. pathinfo($filename, PATHINFO_EXTENSION);
        $save = file_put_contents($_SERVER['DOCUMENT_ROOT'] .'/data/'. $filename , $images);
        $full_name = $_SERVER['DOCUMENT_ROOT'] .'/data/'. $filename;

        $absolute_path = "/data/upload/circler";
        $ftp_mak_dir  = date('Y').'/'.date('m').'/'. date('d');
        $response = $this->ftpUpload($absolute_path , $ftp_mak_dir , $create_filename , $full_name);
        if($response['result']){
            unlink($full_name);
            $filename = $ftp_mak_dir . '/' .$create_filename;
        }else{
            $filename = '';
        }

        return $filename;
    }

    private function ftpUpload($absolute_path ,  $ftp_make_dir , $filename , $origin_file){

        $result = array();

        $conn_id = ftp_connect(FTP_IP, FTP_PORT);
        $conn_login = ftp_login($conn_id, FTP_ID, FTP_PASS);

        $result['result'] = false;
        $result['msg'] = '';
        if(!$conn_id || !$conn_login){
            $result['result'] = false;
            $result['msg'] = 'FTP 연결에 실패하였습니다.';
        }else{
            ftp_pasv($conn_id, true);
        }
        //$absolute_path = "/data/upload/circler";
        //$ftp_make_dir = date('Y').'/'.date('m').'/'. date('d');
        //$filename = time() .'_' . $_FILES['filename']['name'][0];

        $exp_path = explode('/' , $ftp_make_dir);

        $make_dir = '';
        foreach($exp_path as $key=>$dir){
            $make_dir .= "/" . $dir;
            if(@!ftp_chdir($conn_id ,$make_dir)){
                @ftp_mkdir($conn_id, $make_dir);

            }
        }
        $upload_path = $absolute_path .'/'.$ftp_make_dir ;
        if(!ftp_put($conn_id , $filename , $origin_file , FTP_BINARY)){
            $result['result'] = false;
            $result['msg'] = 'FTP UPLOAD FAIL.';
        }else{
            $result['result'] = true;
        };
        return $result;
    }

    private function _purchaseDownCountAdd(){
        $user = $this->user_model->getUser();

        $request_params = (object)array(
            "log_purchase_id"=> $this->input->post('log_purchase_id', true)
        );

        $response = $this->contents_model->setPurchaseDownCountAdd((object)array(
            "log_purchase_id"=> $request_params->log_purchase_id,
            "accountId"=> $user->eth_account,
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }


}
/*
		$this->load->library('JWT');
		$token = JWT::encode((object)array("aaa","bbb"), AUTH_TOKEN_KEY);
		$decoded = JWT::decode($token, AUTH_TOKEN_KEY, array('HS256'));
*/
?>