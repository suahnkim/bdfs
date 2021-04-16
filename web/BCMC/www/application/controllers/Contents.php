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
        if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "_check_eth_account" || $method == "_login_view" || $method == "complete" || $method == 'getContentsJson' || $method == 'setContentsCcid' || $method == "tmp_upload"){
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

    public function defaultPage(){
        $user =  $this->session->userdata('AUSER');

        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/common/default/default");
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
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

    public function lists($qre1 , $qry2 = ''){

        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}($qry2);
        }else{
            show_404();
        }
    }


    public function view($qre1 , $qry2){

        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}($qry2);
        }else{
            show_404();
        }
    }

    public function form($qre1){

        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}();
        }else{
            show_404();
        }
    }

    public function modify_form($qre1 , $qry2) {

        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}($qry2);
        }else{
            show_404();
        }
    }

    public function lists_P($num){
        $user =  $this->session->userdata('AUSER');

        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
        );

        $response = $this->contents_model->getContentsList((object)array(
            "userid"                      => $user->eth_account,
            "user_auth"                 => $user->user_auth,
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
        ));

        /*$result = array();

        $result['resultCode'] = 0;
        $result['resultMessage'] = 'SUCCESS';
        $result['dataList'] = array();
        $result['dataList']['total_rows'] = $response->data->total_rows;
        $result['dataList']['num_start'] = $response->data->num_start;
        $result['dataList']['page'] = 1;
        $i=0;
        foreach($response->data->rows as $key=>$val){
            $result['dataList']['rows'][$i]['dataId'] = "6183915816349691217741748533665061327770517720588461739869247132760768147286";
            $result['dataList']['rows'][$i]['accountId'] = $val->userid;
            $result['dataList']['rows'][$i]['genre'] = $val->cate1;
            $result['dataList']['rows'][$i]['title']  = $val->title;
            $result['dataList']['rows'][$i]['size'] = $val->size;
            $result['dataList']['rows'][$i]['cash'] = $val->cash;
            $result['dataList']['rows'][$i]['state'] = 1;
            $result['dataList']['rows'][$i]['cid'] = $val->cid;
            $result['dataList']['rows'][$i]['ccid'] = $val->ccid;
            $result['dataList']['rows'][$i]['ccid_ver'] = $val->ccid_ver;
            $result['dataList']['rows'][$i]['drm'] = $val->drm;
            $result['dataList']['rows'][$i]['watermark'] = $val->watermarking;
            $result['dataList']['rows'][$i]['main_img'] = $val->main_img;
            if($i >1) break;
            $i++;
        }

        print_r(json_encode($result));
        exit;

        print_r(json_encode($response));
        exit;*/

        /*echo "<br><br><br><br><br><br><br><br><br><br>";
        print_r($response);*/
        if($response->code == 200){
            foreach($response->data->rows as $key=>$val){
                $response->data->rows[$key]->total_realsize = $this->getFileSizeStr($val->total_realsize);
            }
        }

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/contents/P/list{$num}",array(
            "request_params"        => $request_params,
            "data"                        => $response->data,
            "paging"                    => $paging,
            "user_auth"                 => $user->user_auth
        ));
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
    }

    public function plist(){
        $user =  $this->session->userdata('AUSER');
        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
        );

        $response = $this->contents_model->getContentsList((object)array(
            "userid"                      => $user->eth_account,
            "user_auth"                 => $user->user_auth,
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "is_dataid"                  => 'Y',
        ));

        if($response->code == 200){
            foreach($response->data->rows as $key=>$val){
                $response->data->rows[$key]->total_realsize = $this->getFileSizeStr($val->size);
            }
        }

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/contents/P/plist",array(
            "user"                       => $user,
            "data"                       => $response->data,
            "paging"                    => $paging
        ));
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
    }

    public function lists_D(){
        $user =  $this->session->userdata('AUSER');
        $this->load->library('Pagination');
        $this->load->helper('alert');
        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "list_type"			                               => $this->input->get('list_type', true),
        );

        if($user->user_auth == 3 || $user->user_auth == 9){

            if(empty($request_params->list_type)) {
                $response = $this->contents_model->getAllContentsListD((object)array(
                    "userid" => $user->eth_account,
                    "user_auth" => $user->user_auth,
                    "page_yn" => 'Y',
                    "page_num" => $request_params->page_num,
                    "page_size" => $request_params->page_size,
                    "state" => '3',
                ));
            }else{
                $response = $this->contents_model->getSellContentsListD((object)array(
                    "userid" => $user->eth_account,
                    "user_auth" => $user->user_auth,
                    "page_yn" => 'Y',
                    "page_num" => $request_params->page_num,
                    "page_size" => $request_params->page_size,
                    "state" => '3',
                ));
            }
            /*echo "<br><br><br><br><br><br><br><br><br><br>";
            print_r($response);*/
            if($response->code == 200){
                foreach($response->data->rows as $key=>$val){
                    $response->data->rows[$key]->total_realsize = $this->getFileSizeStr($val->size);
                }
            }

            $total_rows = $response->data->total_rows;
            $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


            $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
                "user"          => $user
            ));
            $this->load->view(MC_VIEWS_PATH."/contents/D/list",array(
                "request_params"        => $request_params,
                "data"                        => $response->data,
                "paging"                    => $paging
            ));
            $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
            $this->load->view(MC_VIEWS_PATH."/inc/footer");
        }else{
            alert();
        }


    }

    public function view_P($contents_id){
        $user =  $this->session->userdata('AUSER');
        $this->load->helper(array('alert' , 'common'));

        if($contents_id < 1){
            alert();
        }else {

            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $contents_id
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));

                foreach($contents_file_info->data->rows as $key=>$val){
                    $contents_file_info->data->rows[$key]->realsize_str = $this->getFileSizeStr($val->realsize);
                }
                $contents_info->data->rows =  $contents_file_info->data->rows;
            }


            $this->load->view(MC_VIEWS_PATH . "/inc/header", array(
                "user" => $user
            ));
            $this->load->view(MC_VIEWS_PATH . "/contents/P/view", array(
                "data"          => $contents_info->data
            ));
            $this->load->view(MC_VIEWS_PATH . "/inc/footer_js");
            $this->load->view(MC_VIEWS_PATH . "/inc/footer");
        }
    }

    public function form_P(){
        $user =  $this->session->userdata('AUSER');
        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/contents/P/form");
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
    }

    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public function modify_form_P($contents_id){
        $user =  $this->session->userdata('AUSER');
        $this->load->helper(array('alert' , 'common'));

        if($contents_id < 1){
            alert();
        }else {

            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $contents_id
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));

                foreach($contents_file_info->data->rows as $key=>$val){
                    $contents_file_info->data->rows[$key]->realsize_str = $this->getFileSizeStr($val->realsize);
                }
                $contents_info->data->rows =  $contents_file_info->data->rows;
            }

            if( isset($contents_info->data->ipfs_json_data) ) {
                $contents_info->data->ipfs_json_data = json_decode($contents_info->data->ipfs_json_data);
            }

            $contents_info->data->main_img_src = '';

            if( $contents_info->data->main_img ) {
                $contents_info->data->main_img_src = explode('|', $contents_info->data->main_img)[4];
            }

            $sub_imgs = array();
            if( $contents_info->data->sub_img ) {
                $sub_img_arr = explode(',', $contents_info->data->sub_img);

                if( count($sub_img_arr) ) {
                    foreach($sub_img_arr as $sub_img) {
                        $tmp_arr = explode('|', $sub_img);

                        array_push($sub_imgs, $tmp_arr);
                    }
                }
            }
            $contents_info->data->sub_img_arr = $sub_imgs;

            $contents_info->data->size_text = $this->formatSizeUnits( (int)$contents_info->data->size );

            if( $contents_info->data->metainfo ) {
                $contents_info->data->metainfo = json_decode($contents_info->data->metainfo);
            }

            $this->load->view(MC_VIEWS_PATH . "/inc/header", array(
                "user" => $user
            ));
            $this->load->view(MC_VIEWS_PATH . "/contents/P/modify_form", array(
                "data"          => $contents_info->data
            ));
            $this->load->view(MC_VIEWS_PATH . "/inc/footer_js");
            $this->load->view(MC_VIEWS_PATH . "/inc/footer");
        }
    }

    public function down(){
        //271

        $request_params = (object)array(
            "contents_id"           =>      $this->input->get('contents_id', true)
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id
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
        $aaa = print_r($_REQUEST , true);
        file_put_contents('log.txt' , "----------------------------------".date('YmdHis')."----------------------\n\n".$aaa ."\n\n" , FILE_APPEND);

        $request_params = (object)array(
            "s"		                => $this->input->get('s', true),
            "contents_no"			=> $this->input->get('contents_no', true),
            "file_no"			        => $this->input->get('file_no', true), '',
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
                $this->restapi = new RestApi(PACKAGE_API_URL);

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

    private function _form_submit(){
        $user =  $this->session->userdata('AUSER');
        $this->load->library('RestApi');
        $this->load->library('Crypt');
        $request_params = (object)array(
            "cont_name"		=> trim($this->input->post('cont_name', true)),
            "genre"              => $this->input->post('genre' , true),
            "contents"			=> trim($this->input->post('contents', true)),
            "fileinfo"			    => $this->input->post('fileinfo', true),
            "hash_tags"       => trim($this->input->post('hash_tags' , true)),
            "folderpath"        => $this->input->post('folderpath' , true) ? $this->input->post('folderpath' , true) : '',
            "drm"                 => $this->input->post('drm' , true) ? $this->input->post('drm' , true) : 'n',
            "watermarking"   => $this->input->post('watermarking' , true) ? $this->input->post('watermarking' , true) : 'n',
            "id_sign"            => $this->input->post('id_sign' , true) ? $this->input->post('id_sign' , true) : '',
            "main_img"         => $this->input->post('main_img' , true) ? $this->input->post('main_img' , true) : '',
            "sub_img"          => $this->input->post('main_img' , true) ? $this->input->post('sub_img' , true) : '',
            "fee"                 => $this->input->post('fee' , true) ? trim($this->input->post('fee' , true)) : 0,
            "watermarking"   => $this->input->post('watermarking' , true) ? $this->input->post('watermarking' , true) : 'n',
            "drm"                 => $this->input->post('drm' , true) ? $this->input->post('drm' , true) : 'n',
            "is_adult"           => $this->input->post('is_adult', true) ? $this->input->post('is_adult', true) : 'N',

            "country"=> $this->input->post('country', true),
            "original_spoken_locale"=> $this->input->post('original_spoken_locale', true),
            "title"=> trim($this->input->post('cont_name', true)),
            "synopsis"=> trim($this->input->post('synopsis', true)),
            "production_company"=> trim($this->input->post('production_company', true)),
            "copyright_cline"=> trim($this->input->post('copyright_cline', true)),
            "theatrical_release_date"=> trim($this->input->post('theatrical_release_date', true)),
            "basicMeta_genre"=> $this->input->post('basicMeta_genre', true),
            "ratings"=> $this->input->post('ratings', true),
            "cast_name"=> $this->input->post('cast_name', true),
            "cast_artist_id"=> '',
            "cast_cast_name"=> $this->input->post('cast_cast_name', true),
            "crew_name"=> $this->input->post('crew_name', true),
            "crew_artist_id"=> '',
            "crew_role"=> $this->input->post('crew_role', true)
        );

        if( count($request_params->cast_name) ) {
            foreach($request_params->cast_name as $k=>$v) {
                $request_params->cast_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->cast_cast_name) ) {
            foreach($request_params->cast_cast_name as $k=>$v) {
                $request_params->cast_cast_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->crew_name) ) {
            foreach($request_params->crew_name as $k=>$v) {
                $request_params->crew_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->crew_role) ) {
            foreach($request_params->crew_role as $k=>$v) {
                $request_params->crew_role[$k] = trim($v);
            } // foreach End
        }

        $tot_count = count($request_params->fileinfo);
        $tot_size = 0;
        foreach($request_params->fileinfo as $key=>$val){
            $exp_val = explode('|&|' , $val);
            $tot_size += $exp_val[1];
        }

        $user_id = $user->eth_account;
        $user_pass = Crypt::Decrypt($user->eth_password);
        $this->load->library('RestApi');
        $this->restapi = new RestApi(PACKAGE_API_URL);

        $id_sign_exp = explode('|&|' , $request_params->id_sign);
        $id_sign = '{"sign":"'.$id_sign_exp[0].'","pubKey":"'.$id_sign_exp[1].'"}';

        $cid = "";//$this->generateRandomString(10 , 'cid');
        $ccid = "";//$this->generateRandomString(8 , 'ccid');
        $ccid_ver = "";//1;

        $init_response = $this->restapi->post("/drm/uploadinit.do", array(
            "data"			=> array(
                "user_id"            => $user_id,
                "tot_count"         => (int)$tot_count,
                "pwd"               =>  $user_pass,
                "tot_size"           => (int)$tot_size,
                "cont_name"       =>  $request_params->cont_name,
                "cid"                  => "",
                "ccid"                => "",
                "ccid_ver"          => "",
                "drm_yn"            => $request_params->drm,
                "id_sign"           =>  $id_sign,
            ),
        ));

        //$init_response['info']['http_code'] = 200;
        if($init_response['info']['http_code'] == 200){

            $return_json =  json_decode($init_response['data']);
            /*$return_json  = new stdClass();

            $return_json->code = 0;
            $return_json->next_val = 'test_next_val';*/

            if($return_json->code == 0 && !empty($return_json->next_val)){

                $contents = substr(trim($request_params->contents),0,65536);
                $contents = preg_replace("#[\\\]+$#",'',$contents);

                $contents_response = $this->contents_model->postContentsReg((object)array(
                    "userid"				  => $user_id,
                    "genre"                => $request_params->genre,
                    "title"                   => $request_params->cont_name,
                    "contents"            => $contents,
                    "is_folder"            =>  $request_params->folderpath ? 'Y' : 'N',
                    "folder_name"       => $request_params->folderpath,
                    "size"                  => $tot_size,
                    "wdate"                => time(),
                    "edate"                => time()  + (60 * 60 * 24 * 999),
                    "sort"                   => 1,
                    "init_key"              => $return_json->next_val,
                    "fileinfo"               => $request_params->fileinfo,
                    "hash_tags"         => $request_params->hash_tags,
                    "main_img"          => $request_params->main_img,
                    "sub_img"           => $request_params->sub_img,
                    "cid"                   => $cid,
                    "ccid"                 => $ccid,
                    "ccid_ver"           => $ccid_ver,
                    "fee"                   => $request_params->fee,
                    "watermarking"     => $request_params->watermarking,
                    "drm"                   => $request_params->drm,
                    "is_adult"             => $request_params->is_adult,

                    "country"=> $request_params->country,
                    "original_spoken_locale"=> $request_params->original_spoken_locale,
                    "title"=> $request_params->title,
                    "synopsis"=> $request_params->synopsis,
                    "production_company"=> $request_params->production_company,
                    "copyright_cline"=> $request_params->copyright_cline,
                    "theatrical_release_date"=> $request_params->theatrical_release_date,
                    "basicMeta_genre"=> $request_params->basicMeta_genre,
                    "ratings"=> $request_params->ratings,
                    "cast_name"=> $request_params->cast_name,
                    "cast_artist_id"=> '',
                    "cast_cast_name"=> $request_params->cast_cast_name,
                    "crew_name"=> $request_params->crew_name,
                    "crew_artist_id"=> '',
                    "crew_role"=> $request_params->crew_role
                ));

                if($contents_response->code == 200 && isset($contents_response->data->contents_id)){

                    $file_info = $this->contents_model->getContentFileInfo((object)array(
                        "contents_id"             =>  $contents_response->data->contents_id ,
                        "page_yn"                 => 'N'
                    ));
                    $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("next_val" => $return_json->next_val , "fileinfo" => $file_info->data , "user_id"=>$user_id));
                }
            }else{
                $response = Utils::customizeResponse("200", "400", $return_json->result, null);
            }
        }else{
            $response = Utils::customizeResponse("200", "400", "API호출에 실패하였습니다2.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _modify_form_submit(){
        $user =  $this->session->userdata('AUSER');
        $this->load->library('RestApi');
        $this->load->library('Crypt');
        $request_params = (object)array(
            "cont_name"=> trim($this->input->post('cont_name', true)),
            "genre"=> $this->input->post('genre' , true),
            "contents"=> trim($this->input->post('contents', true)),
            "hash_tags"=> trim($this->input->post('hash_tags' , true)),
            "id_sign"=> $this->input->post('id_sign' , true) ? $this->input->post('id_sign' , true) : '',
            "contents_id"=> $this->input->post('contents_id', true) ? $this->input->post('contents_id', true) : '',

            "country"=> $this->input->post('country', true),
            "original_spoken_locale"=> $this->input->post('original_spoken_locale', true),
            "title"=> trim($this->input->post('cont_name', true)),
            "synopsis"=> trim($this->input->post('synopsis', true)),
            "production_company"=> trim($this->input->post('production_company', true)),
            "copyright_cline"=> trim($this->input->post('copyright_cline', true)),
            "theatrical_release_date"=> trim($this->input->post('theatrical_release_date', true)),
            "basicMeta_genre"=> $this->input->post('basicMeta_genre', true),
            "ratings"=> $this->input->post('ratings', true),
            "cast_name"=> $this->input->post('cast_name', true),
            "cast_artist_id"=> '',
            "cast_cast_name"=> $this->input->post('cast_cast_name', true),
            "crew_name"=> $this->input->post('crew_name', true),
            "crew_artist_id"=> '',
            "crew_role"=> $this->input->post('crew_role', true)
        );

        if( count($request_params->cast_name) ) {
            foreach($request_params->cast_name as $k=>$v) {
                $request_params->cast_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->cast_cast_name) ) {
            foreach($request_params->cast_cast_name as $k=>$v) {
                $request_params->cast_cast_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->crew_name) ) {
            foreach($request_params->crew_name as $k=>$v) {
                $request_params->crew_name[$k] = trim($v);
            } // foreach End
        }

        if( count($request_params->crew_role) ) {
            foreach($request_params->crew_role as $k=>$v) {
                $request_params->crew_role[$k] = trim($v);
            } // foreach End
        }

        $user_id = $user->eth_account;
        $user_pass = Crypt::Decrypt($user->eth_password);
        $this->load->library('RestApi');
        $this->restapi = new RestApi(PACKAGE_API_URL);

        $id_sign_exp = explode('|&|' , $request_params->id_sign);
        $id_sign = '{"sign":"'.$id_sign_exp[0].'","pubKey":"'.$id_sign_exp[1].'"}';

        $contents = substr(trim($request_params->contents),0,65536);
        $contents = preg_replace("#[\\\]+$#",'',$contents);

        $contents_response = $this->contents_model->postContentsUpdate((object)array(
            "userid"=> $user_id,
            "genre"=> $request_params->genre,
            "title"=> $request_params->cont_name,
            "contents"=> $contents,
            "hash_tags"=> $request_params->hash_tags,
            "contents_id"=> $request_params->contents_id,

            "country"=> $request_params->country,
            "original_spoken_locale"=> $request_params->original_spoken_locale,
            "title"=> $request_params->title,
            "synopsis"=> $request_params->synopsis,
            "production_company"=> $request_params->production_company,
            "copyright_cline"=> $request_params->copyright_cline,
            "theatrical_release_date"=> $request_params->theatrical_release_date,
            "basicMeta_genre"=> $request_params->basicMeta_genre,
            "ratings"=> $request_params->ratings,
            "cast_name"=> $request_params->cast_name,
            "cast_artist_id"=> '',
            "cast_cast_name"=> $request_params->cast_cast_name,
            "crew_name"=> $request_params->crew_name,
            "crew_artist_id"=> '',
            "crew_role"=> $request_params->crew_role
        ));

        if( $contents_response->code == 200 && isset($contents_response->data->contents_id) ) {
            $contents_info = $this->getContentsInfoJson($contents_response->data->contents_id);
            $contents_info_data_object = json_decode(base64_decode($contents_info->data['data']));

            $response = (object)(array(
                'dataid'=> $contents_info_data_object->dataid,
                'info'=> $contents_info->data['data']
            ));

            $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("response" => $response));
        } else {
            $response = Utils::customizeResponse("200", "400", "컨텐츠수정에 실패하였습니다2.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }


    public function String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }


    public function Hex2String($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    private function _test(){
        print_r($_POST);
    }

    public function getFileSizeStr($size){
        if($size == 0 ) return 0;
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return @round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
    }

    public function generateRandomString($length = 10 , $type ='') {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $response = $this->contents_model->getUniqueKey((object)array(
            "type"         =>   $type,
        ));

        if($response->code == 200) return $randomString;
        else {
            $this->generateRandomString($length  , $type);
        }

        //return $randomString;

    }

    public function getContentsJson(){

        $aaa = print_r($_REQUEST , true);

        file_put_contents('log.txt' , "-------------  json print ---------------------".date('YmdHis')."----------------------\n\n".$aaa  . "\n\n" , FILE_APPEND);

        $request_params = (object)array(
            'contents_id'   => $this->input->get('contents_no', true),
            'save_path'     => $this->input->get('save_path',true),
        );

        if(strlen($request_params->contents_id) > 0 && strlen($request_params->save_path) > 0){
            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $request_params->contents_id
            ));

            $return_json = array();
            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));

                foreach($contents_file_info->data->rows as $key=>$val){
                    $contents_file_info->data->rows[$key]->realsize_str = $this->getFileSizeStr($val->realsize);
                    $return_json['contents'][$key] = $request_params->save_path ."\\" . $val->filename;
                    $target[$key] = $request_params->save_path ."\\" . $val->filename;
                }

                if( $contents_info->data->metainfo ) {
                    $contents_info->data->metainfo = json_decode($contents_info->data->metainfo);

                    $return_json['basicMeta'][0]['target'] = $target;
                    //$return_json['basicMeta'][0]['target'] = $contents_info->data->metainfo->target; 
                    $return_json['basicMeta'][0]['meta-type'] = $contents_info->data->metainfo->{'meta-type'};
                    $return_json['basicMeta'][0]['metadata']['vender_id'] = $contents_info->data->metainfo->metadata->vender_id;
                    $return_json['basicMeta'][0]['metadata']['country'] = $contents_info->data->metainfo->metadata->country;
                    $return_json['basicMeta'][0]['metadata']['original_spoken_locale'] = $contents_info->data->metainfo->metadata->original_spoken_locale;
                    $return_json['basicMeta'][0]['metadata']['title'] = $contents_info->data->metainfo->metadata->title;
                    $return_json['basicMeta'][0]['metadata']['synopsis'] = $contents_info->data->metainfo->metadata->synopsis;
                    $return_json['basicMeta'][0]['metadata']['production_company'] = $contents_info->data->metainfo->metadata->production_company;
                    $return_json['basicMeta'][0]['metadata']['copyright_cline'] = $contents_info->data->metainfo->metadata->copyright_cline;
                    $return_json['basicMeta'][0]['metadata']['theatrical_release_date'] = $contents_info->data->metainfo->metadata->theatrical_release_date;
                    $return_json['basicMeta'][0]['metadata']['genre'] = array($contents_info->data->metainfo->metadata->genre);
                    $return_json['basicMeta'][0]['metadata']['ratings'] = $contents_info->data->metainfo->metadata->ratings;

                    if( count($contents_info->data->metainfo->metadata->cast) ) {
                        $cast_index = 0;

                        foreach($contents_info->data->metainfo->metadata->cast as $cast) {
                            $return_json['basicMeta'][0]['metadata']['cast'][$cast_index]['name'] = $cast->name;
                            $return_json['basicMeta'][0]['metadata']['cast'][$cast_index]['artist_id'] = '';
                            $return_json['basicMeta'][0]['metadata']['cast'][$cast_index]['cast_name'] = $cast->cast_name;

                            $cast_index++;
                        }
                    }

                    if( count($contents_info->data->metainfo->metadata->crew) ) {
                        $crew_index = 0;

                        foreach($contents_info->data->metainfo->metadata->crew as $crew) {
                            $return_json['basicMeta'][0]['metadata']['crew'][$crew_index]['name'] = $crew->name;
                            $return_json['basicMeta'][0]['metadata']['crew'][$crew_index]['artist_id'] = '';
                            $return_json['basicMeta'][0]['metadata']['crew'][$crew_index]['role'] = $crew->role;

                            $crew_index++;
                        }
                    }

                    $return_json['basicMeta'][0]['metadata']['artwork']= array();

                    if( count($contents_info->data->metainfo->metadata->artwork) ) {
                        foreach($contents_info->data->metainfo->metadata->artwork as $artwork) {
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['title'] = $artwork->title;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['file_name'] = $artwork->file_name;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['file_size'] = $artwork->file_size;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['rep'] = $artwork->rep;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['height'] = $artwork->height;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['width'] = $artwork->width;
                            $return_json['basicMeta'][0]['metadata']['artwork'][0]['format'] = $artwork->format;
                        }
                    }
                } else {
                    $img_format_array = array('jpeg'=>'i01' , 'jpg'=>'i02' , 'png'=>'i03' , 'gif'=>'i04' , 'bmp'=>'i05');

                    $return_json['basicMeta'][0]['target'] = $target;
                    $return_json['basicMeta'][0]['meta-type']= "basic-movie.v1";
                    $return_json['basicMeta'][0]['metadata']['vender_id']= $contents_info->data->contents_id;
                    $return_json['basicMeta'][0]['metadata']['country']= $contents_info->data->contents_id != '768' ? 'US' : '';
                    $return_json['basicMeta'][0]['metadata']['original_spoken_locale']= $contents_info->data->contents_id != '768' ? 'en-US' : '';
                    $return_json['basicMeta'][0]['metadata']['title']= $contents_info->data->title;
                    $return_json['basicMeta'][0]['metadata']['synopsis']= $contents_info->data->contents;
                    $return_json['basicMeta'][0]['metadata']['production_company']= $contents_info->data->contents_id != '768' ? "MARVEL STUDIOS" : "";
                    $return_json['basicMeta'][0]['metadata']['copyright_cline']= $contents_info->data->contents_id != '768' ? "2018 MARVEL STUDIOS" : "";
                    $return_json['basicMeta'][0]['metadata']['theatrical_release_date']= $contents_info->data->contents_id != '768' ? "2018-04-25" : "";
                    $return_json['basicMeta'][0]['metadata']['genre']= array($contents_info->data->cate1);
                    $return_json['basicMeta'][0]['metadata']['rating']= "9";

                    if($contents_info->data->contents_id != '768'){
                        $return_json['basicMeta'][0]['metadata']['cast'][0]['name'] = '로버트 다우니 주니어';
                        $return_json['basicMeta'][0]['metadata']['cast'][0]['artist_id'] = '1234';
                        $return_json['basicMeta'][0]['metadata']['cast'][0]['cast_name'] = '아이언맨';
                        $return_json['basicMeta'][0]['metadata']['cast'][1]['name'] = '크리스 헴스워스';
                        $return_json['basicMeta'][0]['metadata']['cast'][1]['artist_id'] = '1235';
                        $return_json['basicMeta'][0]['metadata']['cast'][1]['cast_name'] = '토르';
                        $return_json['basicMeta'][0]['metadata']['cast'][2]['name'] = '크리스 에반스';
                        $return_json['basicMeta'][0]['metadata']['cast'][2]['artist_id'] = '1236';
                        $return_json['basicMeta'][0]['metadata']['cast'][2]['cast_name'] = '캡틴아메리카';

                        $return_json['basicMeta'][0]['metadata']['crew'][0]['name'] = '안소니 루소';
                        $return_json['basicMeta'][0]['metadata']['crew'][0]['artist_id'] = '1111';
                        $return_json['basicMeta'][0]['metadata']['crew'][0]['role'] = '감독';
                        $return_json['basicMeta'][0]['metadata']['crew'][1]['name'] = '조 루소';
                        $return_json['basicMeta'][0]['metadata']['crew'][1]['artist_id'] = '1112';
                        $return_json['basicMeta'][0]['metadata']['crew'][1]['role'] = '감독';
                    }else{
                        $return_json['basicMeta'][0]['metadata']['cast']= array();//array('name'=>"","artist_id"=>"","cast_name"=>"");
                        $return_json['basicMeta'][0]['metadata']['crew']= array();//array('name'=>"","artist_id"=>"","role"=>"");
                    }

                    $return_json['basicMeta'][0]['metadata']['artwork']= array();

                    if($contents_info->data->main_img){
                        $main_img_exp = explode('|', $contents_info->data->main_img);
                        $exp_main_img = explode('\\' , $main_img_exp[0]);
                        $ext = substr(strrchr(end($exp_main_img), '.'), 1);
    
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['title'] = $contents_info->data->title;
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['file_name'] = $main_img_exp[0];
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['file_size'] = (int) $main_img_exp[1];
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['rep'] = 'true';
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['height'] = (int)(@$main_img_exp[3] ? @$main_img_exp[3]  : 0 );
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['width'] = (int)(@$main_img_exp[2] ? @$main_img_exp[2]  : 0 );
                        $return_json['basicMeta'][0]['metadata']['artwork'][0]['format'] = $img_format_array[strtolower($ext)];
    
                    }
    
                    if($contents_info->data->sub_img){
                        $sub_img_exp = explode(',' , $contents_info->data->sub_img);
    
                        foreach($sub_img_exp as $key=>$val){
                            $num = $contents_info->data->main_img ? $key + 1 : $key;
                            $sub_img_exp = explode('|', $val);
                            $exp_main_img = explode('\\' , $sub_img_exp[0]);
                            $ext = substr(strrchr(end($exp_main_img), '.'), 1);
    
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['title'] = '';
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['file_name'] = $sub_img_exp[0];
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['file_size'] = (int)$sub_img_exp[1];
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['rep'] = 'false';
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['height'] = (int)(@$sub_img_exp[3] ? @$sub_img_exp[3]  : 0 );
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['width'] =(int)(@$sub_img_exp[2] ? @$sub_img_exp[2]  : 0 );
                            $return_json['basicMeta'][0]['metadata']['artwork'][$num]['format'] = $img_format_array[strtolower($ext)];
                        }
                    }
                }

                $contents_info =$this->getContentsInfoJson($request_params->contents_id);
                $return_json['basicMeta'][0]['metadata']['contents_info'] =  $contents_info->data['data'];
                $response = $return_json;

                //echo base64_decode($contents_info->data['data']);

            }else{
                $response = array('result'=>'FAIL' , 'errorMsg'=>"Require Params");
            }

        }else{
            $response = array('result'=>'FAIL' , 'errorMsg'=>"No data info");
        }
        $json_print = json_encode($response , JSON_UNESCAPED_UNICODE);
        file_put_contents('log.txt' , "\n\n".$json_print  . "\n\n" , FILE_APPEND);
        echo $json_print;

        exit;
        //http_response_code($response->status);
    }

    private function setContentsCcid(){

        $aaa = print_r($_REQUEST , true);
        file_put_contents('log.txt' , "-------------  ccid print ---------------------".date('YmdHis')."----------------------\n\n".$aaa ."\n\r" , FILE_APPEND);

        $request_params = (object)array(
            'contents_id'   => $this->input->get('contents_no', false) ? $this->input->get('contents_no', false) : $this->input->get('contents_no', false),
            'result'     => $this->input->get('result',false) ? $this->input->get('result',false) : $this->input->get('result',false),
        );

        if(strlen($request_params->contents_id) > 0 && strlen($request_params->result) > 0){

            $json_data = str_replace("%20","", stripslashes($request_params->result));
            //$json_data = json_decode(str_replace("{}}ult\":{}}","{}}", $json_data));
            $json_data = json_decode($json_data);
            /*
            $this->load->library('RestApi');
            $this->restapi = new RestApi('http://localhost:8080/ipfs');
            $state_response = $this->restapi->post("/QmfFzR7g7u1UNTuq2b4yAW77rxFV97W5Xn8t8Lu9vqbXtt/basicMeta/basicMeta0.json", array(
                "data"			=> array(1),
            ));*/

            if($json_data->result->ccid && $json_data->result->version && $request_params->contents_id){

              $ccid_response = $this->contents_model->modContentsInfo((object)array(
                  "contents_id"         => $request_params->contents_id,
                  "ccid"                   => $json_data->result->ccid,
                  "ccid_ver"             => $json_data->result->version,
                  "ipfs_json_data"           => str_replace("%20","", $request_params->result)
              ));


              //$response = array('result'=>'SUCCESS' , 'errorMsg' => '');


                if($ccid_response->code == 200){

                    $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                        "contents_id"          => $request_params->contents_id
                    ));

                    if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                        $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                            "contents_id"           => $contents_info->data->contents_id
                        ));
                        $contents_info->data->rows =  $contents_file_info->data->rows;
                    }


                    $ftype_arr = array('mp4'=>'V01','jpeg'=>'I01','jpg'=>'I02','png'=>'I03','gif'=>'I04','bmp'=>'I05');


                    //print_r($contents_info);


                    $res = array();
                    $res['date'] = date('YmdHis');
                    $res['cat1'] = $contents_info->data->cate1;
                    $res['cat2'] = $contents_info->data->cate1;
                    $res['ftype'] = 'V01';
                    $res['info']['contents_info'] = $contents_info->data;


                    $file_cnt = 0 ;
                    $img_cnt = 0;
                    $fileHashLists = array();
                    $chunkLists = array();

                    //print_r($json_data);
                    foreach($json_data->files as $key=>$val){
                        if( preg_match("/\.(json)$/i", strtolower($val->path))){
                            //json 확장자
                            $metainfo = $val->path;
                        }

                        if( preg_match("/\.(gif|jpg|jpeg|png|bmp)$/i", strtolower($val->path))){
                            //이미지확장자
                            $res['info']['img_rows'][$img_cnt]['img'] = $val->path;
                            $res['info']['img_rows'][$img_cnt]['img_size'] = $val->file_size;
                            $res['info']['img_rows'][$img_cnt]['cid'] = @$val->cid ? @$val->cid : '';

                            $img_cnt++;
                        }
                        if( preg_match("/\.(mp4|avi)$/i", strtolower($val->path))){
                            //동영상확장자
                            $res['info']['file_rows'][$file_cnt]['filename'] = $val->path;
                            $res['info']['file_rows'][$file_cnt]['cid'] = @$val->cid ? @$val->cid : '';
                            $res['info']['file_rows'][$file_cnt]['chunk'] =  ceil($json_data->result->chunk_size / $val->file_size);
                            $res['info']['file_rows'][$file_cnt]['filesize']  = $val->file_size;
                            $fileHashLists[$file_cnt] = $val->cid;
                            $chunkLists[$file_cnt] = ceil($json_data->result->chunk_size / $val->file_size);
                            $file_cnt++;
                        }
                    }

                    $cid = $contents_info->data->cid;
                    $ccid = $json_data->result->ccid;
                    $fee = $contents_info->data->cash;
                    $version = $json_data->result->version;
                    /*$fileHashList = implode(',' , $fileHashLists);
                    $chunkList = implode(',' , $chunkLists);*/
                    $json_encode =  json_encode($res , JSON_UNESCAPED_UNICODE);//print_r($res);
                    $fileHashList = json_encode($fileHashLists , JSON_UNESCAPED_UNICODE);
                    $chunkList = json_encode($chunkLists ,JSON_UNESCAPED_UNICODE);
                    ?>

                    <!--<script src="<?php /*echo COM_ASSETS_PATH; */?>/script/jquery.min.js<?php /*echo CSS_JS_UPDATE_DATE; */?>"></script>
                    <script src="<?php /*echo COM_ASSETS_PATH; */?>/script/bootstrap.min.js<?php /*echo CSS_JS_UPDATE_DATE; */?>"></script>
                    <script src="<?php /*echo COM_ASSETS_PATH; */?>/script/common.js<?php /*echo CSS_JS_UPDATE_DATE; */?>"></script>
                    <script>
                        $(document).ready(function () {
                            var params = {
                                cid : "<?php /*echo($cid)*/?>",
                                ccid : "<?php /*echo($ccid)*/?>",
                                version : "<?php /*echo($version)*/?>",
                                info : JSON.stringify(<?php /*echo($json_encode)*/?>),
                                fee : parseInt(<?php /*echo((int)$fee)*/?>),
                                fileHasheLists :<?php /*echo($fileHashList) */?>,
                                chunkLists : <?php /*echo($chunkList)*/?>,

                            }
                            var data = $.runsync(http_api_url + '/register/data' ,params , 'json' , true);
                            if(data.resultCode == 0 ){
                                var params_data = {contents_id : <?php /*echo($contents_info->data->contents_id)*/?> , dataid : data.dataId , metainfo : '<?php /*echo($metainfo)*/?>'}
                                $.runsync('/contents/setModContentsInfo' , params_data , 'html' , false);
                            }
                        });
                    </script>-->
                    <?
                }
            }
        }else{
            $response = array('result'=>'FAIL' , 'errorMsg'=>"No data info");
            echo json_encode($response);
            exit;
        }

    }

    private function _setModContentsInfo(){
        $request_params = (object)array(
            'contents_id'            => $this->input->post('contents_id' , true),
            'metainfo'                 => $this->input->post('metainfo' , true),
            'dataid'                   => $this->input->post('dataid' , true),
        );
        $response = $this->contents_model->modContentsInfo((object)array(
            "contents_id"               =>      $request_params->contents_id,
            "metainfo"                    =>      $request_params->metainfo,
            "dataid"                      =>       $request_params->dataid
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _setModContentsInfoStopPublish(){
        $request_params = (object)array(
            'contents_id'            => $this->input->post('contents_id' , true),
            'stop_publish'           => $this->input->post('stop_publish' , true),

        );
        $response = $this->contents_model->modContentsInfo((object)array(
            "contents_id"               =>      $request_params->contents_id,
            "stop_publish"                    =>      $request_params->stop_publish
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }


    private function _getContentsPackageState(){

        $request_params = (object)array(
            'user_id'       => $this->input->post('user_id' , true),
            'next_val'      => $this->input->post('next_val' ,true)
        );
        /*$request_params->user_id = '9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610';
        $request_params->next_val = '';*/
        $this->load->library('RestApi');
        $this->restapi = new RestApi(PACKAGE_API_URL);

        $state_response = $this->restapi->post("/drm/statusInfo.do", array(
            "data"			=> array(
                "user_id"          => $request_params->user_id,
                "next_val"         => $request_params->next_val,
            ),
        ));

        $return_json = json_decode($state_response['data']);


        if($return_json->code == 0){

            $contents_info = $this->contents_model->getContentsNextVal((object)array(
                'init_key'      => $request_params->next_val
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id && empty($contents_info->cid)){

                $return_json->status = !empty($return_json->status) ? $return_json->status : 0;

                if(strlen($contents_info->data->contents_id) > 0 && strlen(@$return_json->cid) > 0 ){
                    $this->contents_model->modContentsInfo((object)array(
                        "contents_id"       =>      $contents_info->data->contents_id,
                        "cid"                   =>      $return_json->cid,
                        "state"                =>       $return_json->status
                    ));
                }

                $response = Utils::customizeResponse("200", "200", "SUCCESS", array('status'=> $return_json->status));
            }else{
                $response = Utils::customizeResponse("200", "400", "FAIL.", array('status'=>2));
            }

            echo json_encode($response);
            http_response_code($response->status);
        }
    }

    private function tmp_upload(){

        $aaa = print_r($_REQUEST , true);
        $bbb = print_r($_FILES , true);
        file_put_contents('log.txt' , "-------------  upload print ---------------------".date('YmdHis')."----------------------\n\n".$aaa .$bbb ."\n\n" , FILE_APPEND);

        if($_FILES['filename']){
            if(isset($_FILES['filename']['tmp_name']) &&  $_FILES['filename']['error'] == 0){
                $upload_path = $_SERVER['DOCUMENT_ROOT'] ."/data";
                $upfile = $this->setFileUpload($upload_path , "filename");
            }
        }else{
            $upfile['result'] = false;
            $upfile['msg'] = "FAIL";
            $upfile['filename'] = "";
        }

        echo ",\"filename\":\"".($upfile['filename'])."\"";
        exit;

    }

    private function setFileUpload($upfile_path , $filename){

        $result = array();
        if(isset($_FILES[$filename]['tmp_name']) && $_FILES[$filename]['error'] == 0){
            //echo $_FILES[$filename]['name']."<br>";
            $path = pathinfo($_FILES[$filename]['name']);
            //print_R($path);
            $ext = strtolower($path['extension']);
            $ext_array = array('jpg','jpeg','gif','png','bmp');
            if (!in_array($ext, $ext_array)) {
                $result['result'] = false;
                $result['filename'] = "";
                $result['msg'] = $ext."확장자는 업로드 할 수 없습니다.";
            }
            $up_filename = mt_rand(100000 ,999999)  +  time()  .".".$ext;

            $year = date('Ymd');

            if(!is_dir($upfile_path."/".$year)){
                mkdir($upfile_path."/".$year, 0707);
                chmod($upfile_path."/".$year, 0707);
            }
            // - 업로드 파일을 새로 만든 파일명으로 변경 및 이동
            $local_imgurl = $upfile_path."/".$year."/".$up_filename;
            if (!move_uploaded_file($_FILES[$filename]['tmp_name'], $local_imgurl)) {

                $result['filename'] = "";
            }else{

                $result['filename'] = "/data/". $year ."/".$up_filename;
            }

        }else{

            $result['filename'] = "";
        }

        return $result;
    }

    private function _getAjaxContents(){
        $user =  $this->session->userdata('AUSER');

        $request_params = (object)array(
            'contents_id'       => $this->input->post('contents_id' , true),
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id
        ));

        if($contents_info->code == 200 && $contents_info->data->contents_id > 0) {

            $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                "contents_id"           => $contents_info->data->contents_id
            ));

            foreach($contents_file_info->data->rows as $key=>$val){
                $contents_file_info->data->rows[$key]->realsize_str = $this->getFileSizeStr($val->realsize);
            }
            $user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                "contents_id"           => $contents_info->data->contents_id,
                "userid"                   => $user->eth_account
            ));

            $contents_info->data->sell_cash = @$user_sell_info->data->cash ? @$user_sell_info->data->cash : '';

            $contents_info->data->rows =  $contents_file_info->data->rows;

            $data = $contents_info->data;
            $res = array();
            ob_start();
            ?>
            <form class="form-bordered contents_sell_info">
                <input type="hidden" name="contents_id" value="<?php echo($data->contents_id)?>">
                <input type="hidden" name="productId" value="<?php echo(@$user_sell_info->data->productId)?>">
                <div class="form-row progress_loading_area" style="display:none;">
                    <div class="card-body" style="border:1px solid #b3d7ff;margin-bottom:10px;">
                        <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" valign="absmiddle"/ ><span class="progress_loading_txt"> 상품코드 발급중....</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <div class="form-group" style="marign-bottom:10px;">
                            <label for="inputAddress">유통 가격(단위 : WEI)</label>
                            <input type="text" class="form-control" name="sell_cash" placeholder="유통 가격" value="<?php echo($data->sell_cash)?>">
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button class="btn btn-primary btn-submit"><?php echo(@$user_sell_info->data->productId ? '수정하기':'유통시작')?></button>
                                <button class="btn btn-default modal-dismiss">닫기</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <label for="inputAddress" style="font-size:11px;font-weight:bold;">파일개수 : <?php echo(count($data->rows))?>개 | 총용량 :
                            <?php echo($this->getFileSizeStr($data->size))?></label>
                        <div style="background: #e9ecef;margin:10px;overflow-y: scroll;height:100px;">
                            <ul style="list-style:none;float:left;text-align:left;width:100%;">
                                <?php foreach($data->rows as $key=>$val){ ?>
                                <li style="width:100%;text-align:left;text-overflow:ellipsis;overflow:hidden;padding:5px 0;font-size:11px;">
                                    <div style="float:left;width:80%;height:20px;line-height:20px;"> - <?php echo($val->filename)?></div>
                                    <div style="float:left;width:20%;height:20px;line-height:20px;text-align:right;padding-right:10px;"><?php echo($val->realsize_str)?></div>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <div class="form-row" style="margin-top:10px;">
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault">제목</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($data->title)?>">
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault" readonly>분류</label>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($this->menu->categoty_list[$data->cate1])?>">
                                </div>
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault" readonly>저작권료</label>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="inputDefault" name="cash" readonly value="<?php echo($data->cash)?>">
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault">내용</label>
                                <div class="col-lg-10">
                                    <textarea type="text" class="form-control" id="inputDefault" readonly><?php echo($data->contents)?></textarea>
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2"
                                       for="inputDefault">관심Tag</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($data->hash_tags)?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <label for="inputAddress" style="font-size:11px;font-weight:bold;">상세이미지</label>
                        <div style="background: #ddd;marign:20px;overflow-y: scroll;height:410px;">

                                <?php
                                if($data->main_img && $data->ccid_ver){

                                    $exp_main_img =explode('|' , $data->main_img);

                                    //$exp_main_img = explode('\\' , $main_img);
                                    //$main_filename = 'http://localhost:8080/ipfs/'.$data->ccid_ver ."/basicMeta/".end($exp_main_img);
                                    $main_filename =  end($exp_main_img);
                                    //$ext = substr(strrchr(end($exp_main_img), '.'), 1);
                                    ?>
                                    <div style="margin:10px; 0;"><img src="<?php echo($main_filename)?>" style="max-width:400px;"></div>
                                <?php } ?>
                                <?php
                                if($data->sub_img && $data->ccid_ver){

                                    $sub_img_exp = explode(','  , $data->sub_img);

                                    foreach($sub_img_exp as $key=>$val){

                                        $exp_sub_img = explode('|' , $val);
                                        //$exp_sub_img = explode('\\' , $sub_img);
                                        $sub_filename = end($exp_sub_img);
                                        $ext = substr(strrchr(end($exp_sub_img), '.'), 1);
                                        ?>
                                        <div style="margin:10px; 0;"><img src="<?php echo($sub_filename)?>" style="max-width:400px;"></div>
                                    <?php }} ?>
                               <!-- <li style="width:100%;text-align:left;text-overflow:ellipsis;overflow:hidden;padding:5px 0;">
                                    - dfklasdjlfkjasdlkfjasdkjfklasjdfljasdklfjlasdk
                                </li>-->

                        </div>
                    </div>
                </div>
            </form>
            <?
            $res['result']  = true;
            $res['contents_html'] = ob_get_contents();
            ob_end_clean();

        }else{
            $res['false']  = true;
            $res['contents_html'] = "데이터 정보가 없습니다.";
        }

        echo json_encode($res);
        exit;
    }


    private function _getAjaxApiContents(){
        $user =  $this->session->userdata('AUSER');

        $request_params = (object)array(
            'nowPage'         => $this->input->post('pageNum' , true) ? $this->input->post('pageNum' , true) : 1,
            'rowPerPage'     => $this->input->post('pageSize' , true) ? $this->input->post('pageSize' , true) : 20,
            'search_key'      => $this->input->post('search_key' , true) ? $this->input->post('search_key' , true) : "",
            'search_value'   => $this->input->post('search_key' , true) ? $this->input->post('search_value' , true) : "",
        );

        $api_data = $this->getTechOnMediaApi((object)array(
            'nowPage'         => $request_params->nowPage,
            'rowPerPage'     => $request_params->rowPerPage,
            'search_key'      => $request_params->search_key,
            'search_value'   => $request_params->search_value,
        ));

        $json = json_decode($api_data['data']);
        if($json->status == 'success' && strlen($json->result[0]->meta_container[0]->metadata->contents_info) > 0) {


            $data = json_decode(base64_decode($json->result[0]->meta_container[0]->metadata->contents_info));
            /*$user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                "ccid"                     => $json->result[0]->ccid,
                "vserion"                 => $json->result[0]->version,
                "userid"                   => $user->eth_account
            ));
            $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                "contents_id"           => $contents_info->data->contents_id
            ));
            */

            $user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                "contents_id"           => $data->contents_id,
                "userid"                   => $user->eth_account
            ));

            $data->sell_cash = @$user_sell_info->data->cash ? @$user_sell_info->data->cash : '';

            $res = array();
            ob_start();
            ?>
            <form class="form-bordered contents_sell_info">
                <input type="hidden" name="ccid" value="<?php echo($json->result[0]->ccid)?>">
                <input type="hidden" name="contents_id" value="<?php echo($data->contents_id)?>">
                <input type="hidden" name="productId" value="<?php echo(@$user_sell_info->data->productId)?>">
                <div class="form-row progress_loading_area" style="display:none;">
                    <div class="card-body" style="border:1px solid #b3d7ff;margin-bottom:10px;">
                        <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" valign="absmiddle"/ ><span class="progress_loading_txt"> 상품코드 발급중....</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <div class="form-group" style="marign-bottom:10px;">
                            <label for="inputAddress">유통 가격(단위 : WEI)</label>
                            <input type="text" class="form-control" name="sell_cash" placeholder="유통 가격" value="<?php echo($data->sell_cash)?>">
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button class="btn btn-primary btn-submit"><?php echo(@$user_sell_info->data->productId ? '수정하기':'유통시작')?></button>
                                <button class="btn btn-default modal-dismiss">닫기</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <label for="inputAddress" style="font-size:11px;font-weight:bold;">파일개수 : <?php echo(count($data->rows))?>개 | 총용량 :
                            <?php echo($this->getFileSizeStr($data->size))?></label>
                        <div style="background: #e9ecef;margin:10px;overflow-y: scroll;height:100px;">
                            <ul style="list-style:none;float:left;text-align:left;width:100%;">
                                <?php foreach($data->rows as $key=>$val){ ?>
                                    <li style="width:100%;text-align:left;text-overflow:ellipsis;overflow:hidden;padding:5px 0;font-size:11px;">
                                        <div style="float:left;width:80%;height:20px;line-height:20px;"> - <?php echo($val->filename)?></div>
                                        <div style="float:left;width:20%;height:20px;line-height:20px;text-align:right;padding-right:10px;"><?php echo($this->getFileSizeStr($val->size))?></div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <div class="form-row" style="margin-top:10px;">
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault">제목</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($data->title)?>">
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault" readonly>분류</label>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($this->menu->categoty_list[$data->cate1])?>">
                                </div>
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault" readonly>저작권료</label>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="inputDefault" name="cash" readonly value="<?php echo($data->cash)?>">
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2" for="inputDefault">내용</label>
                                <div class="col-lg-10">
                                    <textarea type="text" class="form-control" id="inputDefault" readonly><?php echo($data->contents)?></textarea>
                                </div>
                            </div>
                            <div class="form-group row col-lg-12">
                                <label class="col-lg-2 control-label text-lg-right pt-2"
                                       for="inputDefault">관심Tag</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="inputDefault" readonly value="<?php echo($data->hash_tags)?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <label for="inputAddress" style="font-size:11px;font-weight:bold;">상세이미지</label>
                        <div style="background: #ddd;marign:20px;overflow-y: scroll;height:410px;">
                            <?php
                            if(count($json->result[0]->meta_container[0]->metadata->artwork) > 0){
                                foreach($json->result[0]->meta_container[0]->metadata->artwork as $key=>$val){
                                    if( preg_match("/\.(gif|jpg|jpeg|png|bmp)$/i", strtolower($val->file_name))){
                                        //이미지확장자
                                        echo "<div style=\"margin:10px; 0;\"><img src=\"http://15.164.5.18:80/ccsearch/v1/ccontent/".$json->result[0]->ccid."/".$json->result[0]->version."/".$val->file_name."\" style=\"max-width:400px;\"></div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </form>
            <?
            $res['result']  = true;
            $res['contents_html'] = ob_get_contents();
            ob_end_clean();

        }else{
            $res['result']  = false;
            $res['contents_html'] = "데이터 정보가 없습니다.";
        }

        echo json_encode($res);
        exit;

    }


    private function _getAjaxContentsDown(){
        $user =  $this->session->userdata('AUSER');

        $res = array();
        $res['result']  = false;
        if($user->eth_account){
            $request_params = (object)array(
                'all_data'       => $this->input->post('all_data' , true),
                'next_val'      => $this->input->post('next_val' ,true)
            );
            /*$request_params->user_id = '9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610';
            $request_params->next_val = '';*/
            $this->load->library('RestApi');
            $this->restapi = new RestApi(PACKAGE_API_URL);

            $drm_response = $this->restapi->post("/drm/statusInfodetail.do", array(
                "data"			=> array(
                    "user_id"          => $user->eth_account,
                    "next_val"         => $request_params->next_val,
                    "all_data"          => $request_params->all_data
                ),
            ));
            $json = json_decode($drm_response['data']);
            $data = $json->req_data[0];
            if(count($data->info_data) > 0){
                $total_size = 0;
                foreach($data->info_data as $key=>$val){
                    $total_size += $val->down_file_size;
                }
            }

            $res = array();
            ob_start();
            ?>
            <form class="form-bordered contents_down">
                <input type="hidden" name="userid" value="<?php echo($user->eth_account)?>">
                <input type="hidden" name="next_val" value="<?php echo($request_params->next_val)?>">
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                        <label for="inputAddress" style="font-size:11px;font-weight:bold;">파일개수 : <?php echo(number_format($data->tot_cnt))?>개 | 총용량 : <?php echo($this->getFileSizeStr($total_size))?>
                            </label>
                        <div style="background: #e9ecef;margin:10px;overflow-y: scroll;height:100px;">
                            <ul style="list-style:none;float:left;text-align:left;width:100%;">
                                    <?php if(count($data->info_data) > 0){?>
                                        <?php foreach($data->info_data as $key=>$val){?>
                                            <li file_no="<?php echo($val->req_no)?>"  file_name="<?php echo($val->cont_name)?>" del_yn="N" file_size="<?php echo($val->down_file_size)?>" file_path="<?php echo($val->cont_name)?>">
                                                <div style="float:left;width:80%;padding-left:50px;border-bottom:1px solid #ddd;height:40px;line-height:40px;"> - <?php echo($val->cont_name)?></div>
                                                <div style="float:left;width:20%;border-bottom:1px solid #ddd;height:40px;line-height:40px;text-align:right;padding-right:50px;"><?php echo($this->getFileSizeStr($val->down_file_size))?></div>
                                            </li>
                                        <?php }?>
                                    <?php } ?>
                            </ul>
                        </div>
                        <div class="row">

                            <div class="col-md-12 text-center">
                                <button class="btn btn-primary btn-submit">다운로드</button>
                                <button class="btn btn-default modal-dismiss">닫기</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if($data->ccid && $data->version){
                 ?>
                <div class="form-row" style="margin-top:10px;">
                    <div class="card-body" style="border:1px solid #b3d7ff">
                    <?php

                        $contents_info = $this->contents_model->getContentsSingleInfoCcid((object)array(
                        "ccid"          => $data->ccid,
                        "version"      => $data->version
                    ));

                        $sub_img_exp = explode(','  , $contents_info->data->sub_img);

                        foreach($sub_img_exp as $key=>$val){

                        $sub_img = explode('|' , $val)[0];
                        $exp_sub_img = explode('\\' , $sub_img);
                        $sub_filename = 'http://localhost:8080/ipfs/'.$data->version ."/basicMeta/".end($exp_sub_img);
                        $ext = substr(strrchr(end($exp_sub_img), '.'), 1);
                        ?>
                    <div style="margin:10px; 0;"><img src="<?php echo($sub_filename)?>" style="border:0;max-width:760px;"></div>
                    <?php }?>
                    </div>
                </div>
                 <?php } ?>



            </form>
            <?
            $res['result']  = true;
            $res['title']  = $data->content_name;
            $res['contents_html'] = ob_get_contents();
            ob_end_clean();

        }else{
            $res['result']  = false;
            $res['title']  = '콘텐츠상세정보';
            $res['contents_html'] = "데이터 정보가 없습니다.";
        }

        echo json_encode($res);
        exit;

    }

    private function _postSellContentsReg(){
        $user =  $this->session->userdata('AUSER');

        $request_params = (object)array(
            'contents_id'       => $this->input->post('contents_id' , true),
            'cash'                => $this->input->post('sell_cash' , true),
            'productId'          => $this->input->post('productId' , true),
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id
        ));

        if($contents_info->code == 200 && $contents_info->data->contents_id > 0){

            $user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                "contents_id"           => $contents_info->data->contents_id,
                "userid"                   => $user->eth_account
            ));

            if($user_sell_info->code == 200 && $user_sell_info->data->user_sell_contents_id) {
                $response = $this->contents_model->modSellerContents((object)array(
                    'contents_id' => $request_params->contents_id,
                    'real_cash' => $request_params->cash,
                    'productId' => $request_params->productId,
                    'user_sell_contents_id' => $user_sell_info->data->user_sell_contents_id,
                    'seller_userid' => $user->eth_account,
                ));
                $action_type = "mod";
            }else{
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));
                $contents_info->data->rows =  $contents_file_info->data->rows;
                $response = $this->contents_model->postSellerContents((object)array(
                    'contents_id' => $request_params->contents_id,
                    'real_cash' => $request_params->cash,
                    'productId' => $request_params->productId,
                    'contents_data' => $contents_info->data,
                    'seller_userid' => $user->eth_account,
                ));

                $action_type = "reg";
            }
            if($response->code == 200){
                $this->load->library('RestApi');
                $this->restapi = new RestApi();

                $curl_send_response = $this->restapi->post("http://www.circler.co.kr/contents/setBlockChainContentsInfo", array(
                    "data"			=> array(
                        "action_type"       => $action_type,
                        "contents_info"     => json_encode($contents_info->data),
                        "productid"          => $request_params->productId,
                        "real_cash"         => $request_params->cash,
                    ),
                ));
            }

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }


    private function _postSellContentsApiReg(){
        
        $user =  $this->session->userdata('AUSER');

        $request_params = (object)array(
            'contents_id'       => $this->input->post('contents_id' , true),
            'cash'                => $this->input->post('sell_cash' , true),
            'productId'          => $this->input->post('productId' , true),
            'nowPage'         => $this->input->post('pageNum' , true) ? $this->input->get('pageNum' , true) : 1,
            'rowPerPage'     => $this->input->post('pageSize' , true) ? $this->input->get('pageSize' , true) : 20,
            'search_key'      => $this->input->post('search_key' , true) ? $this->input->post('search_key' , true) : "",
            'search_value'   => $this->input->post('search_key' , true) ? $this->input->post('search_value' , true) : "",
        );



        $api_data = $this->getTechOnMediaApi((object)array(
            'nowPage'         => $request_params->nowPage,
            'rowPerPage'     => $request_params->rowPerPage,
            'search_key'      => $request_params->search_key,
            'search_value'   => $request_params->search_value,
        ));

        $json = json_decode($api_data['data']);
        
        if($json->status == 'success' && strlen($json->result[0]->meta_container[0]->metadata->contents_info) > 0) {
            $data = json_decode(base64_decode($json->result[0]->meta_container[0]->metadata->contents_info));
            $user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                "contents_id"           => $data->contents_id,
                "userid"                   => $user->eth_account
            ));

            if($user_sell_info->code == 200 && $user_sell_info->data->user_sell_contents_id) {
                $response = $this->contents_model->modSellerContents((object)array(
                    'contents_id'               => $request_params->contents_id,
                    'real_cash'                 => $request_params->cash,
                    'productId'                  => $request_params->productId,
                    'user_sell_contents_id' => $user_sell_info->data->user_sell_contents_id,
                    'seller_userid'              => $user->eth_account,
                ));
                $action_type = "mod";
            }else{

                $response = $this->contents_model->postSellerContents((object)array(
                    'contents_id' => $request_params->contents_id,
                    'real_cash' => $request_params->cash,
                    'productId' => $request_params->productId,
                    'contents_data' => $data,
                    'seller_userid' => $user->eth_account,
                ));

                $action_type = "reg";
            }
            
            if($response->code == 200){
                $this->load->library('RestApi');
                $this->restapi = new RestApi();

                preg_match("/(([a-z0-9\-]+\.)*)([a-z0-9\-]+)\.([a-z]{3,4}|[a-z]{2,3}\.[a-z]{2})(\:[0-9]+)?$/", $_SERVER['HTTP_HOST'], $matches);
                $sub_domain = null;
                if($matches[1]) {
                    $sub_domain = substr($matches[1], 0, -1) ? substr($matches[1], 0, -1) : 'www';
                }

                $curl_send_response = $this->restapi->post("http://".$sub_domain.".circler.co.kr/contents/setBlockChainContentsInfoApi", array(
                    "data"			=> array(
                        "action_type"       => $action_type,
                        "contents_info"     => $api_data['data'],
                        "productid"          => $request_params->productId,
                        "real_cash"         => $request_params->cash,
                    ),
                ));
              
            }

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _packagingList(){
        $user =  $this->session->userdata('AUSER');

        $res = array();
        $res['result']  = false;

        if($user->eth_account){
            $request_params = (object)array(
                'all_data'       => $this->input->post('all_data' , true),
                'next_val'      => $this->input->post('next_val' ,true),
                'user_id'       => $user->eth_account,
                'cur_page'    => $this->input->post('cur_page' , true) ? $this->input->post('cur_page' , true)  : 2,
                'list_cnt'       => $this->input->post('list_cnt' , true) ? $this->input->post('list_cnt' , true) : 20,
                'from_date'    => $this->input->post('from_date' ,true),
                'to_date'       => $this->input->post('to_date' ,true),
            );
            /*$request_params->user_id = '9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610';
            $request_params->next_val = '';*/
            $this->load->library('RestApi');
            $this->restapi = new RestApi(PACKAGE_API_URL);

            $drm_response = $this->restapi->post("/drm/statusInfodetail.do", array(
                "data"			=> array(
                    "user_id"            => $user->eth_account,
                    "next_val"           => $request_params->next_val,
                    "all_data"            => $request_params->all_data,
                    "cur_page"         => $request_params->cur_page,
                    "list_cnt"            => $request_params->list_cnt,
                    "from_data"         => $request_params->from_date,
                    "to_date"            => $request_params->to_date,
                ),
            ));
            $res['result'] = true;
            $res['data'] = json_decode($drm_response['data']);
            $res['message'] = 'SUCCESS';
        }else{
            $res['data'] = '';
            $res['message'] = '로그인 후 이용해주세요';
        }

        echo json_encode($res);
        exit;
    }

    public function getContentsInfoProductData2(){

        $request_params = (object)array(
            'contents_id' => $this->input->post('contents_id' , true) ? $this->input->post('contents_id' , true) : $this->input->get('contents_id' , true),
        );
        if(strlen($request_params->contents_id) > 0){
            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $request_params->contents_id
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));
                $contents_info->data->rows =  $contents_file_info->data->rows;
                $json_data  = json_decode($contents_info->data->ipfs_json_data);

                $res = array();
                $res['date'] = date('YmdHis');
                $res['cat1'] = $contents_info->data->cate1;
                $res['cat2'] = $contents_info->data->cate1;
                $res['ftype'] = 'V01';
                $res['info']['contents_info'] = $contents_info->data;


                $file_cnt = 0 ;
                $img_cnt = 0;
                $fileHashLists = array();
                $chunkLists = array();

                //print_r($json_data);
                foreach($json_data->files as $key=>$val){
                    if( preg_match("/\.(json)$/i", strtolower($val->path))){
                        //json 확장자
                        $metainfo = $val->path;
                    }

                    if( preg_match("/\.(gif|jpg|jpeg|png|bmp)$/i", strtolower($val->path))){
                        //이미지확장자
                        $res['info']['img_rows'][$img_cnt]['img'] = $val->path;
                        $res['info']['img_rows'][$img_cnt]['img_size'] = $val->file_size;
                        $res['info']['img_rows'][$img_cnt]['cid'] = @$val->cid ? @$val->cid : '';

                        $img_cnt++;
                    }
                    if( preg_match("/\.(mp4|avi)$/i", strtolower($val->path))){
                        //동영상확장자
                        $res['info']['file_rows'][$file_cnt]['filename'] = $val->path;
                        $res['info']['file_rows'][$file_cnt]['cid'] = @$val->cid ? @$val->cid : '';
                        $res['info']['file_rows'][$file_cnt]['chunk'] =  ceil($val->file_size  / $json_data->result->chunk_size);
                        $res['info']['file_rows'][$file_cnt]['filesize']  = $val->file_size;
                        $fileHashLists[$file_cnt] = $val->cid;
                        $chunkLists[$file_cnt] = (int)ceil( $val->file_size/$json_data->result->chunk_size);
                        $file_cnt++;
                    }
                }

                $cid = $contents_info->data->cid;
                $ccid = $json_data->result->ccid;
                $fee = $contents_info->data->cash;
                $version = $json_data->result->version;

                $dataid = $contents_info->data->dataid;
                /*$fileHashList = implode(',' , $fileHashLists);
                $chunkList = implode(',' , $chunkLists);*/
                /*$json_encode =  json_encode($res , JSON_UNESCAPED_UNICODE);//print_r($res);
                $fileHashList = json_encode($fileHashLists , JSON_UNESCAPED_UNICODE);
                $chunkList = json_encode($chunkLists ,JSON_UNESCAPED_UNICODE);*/


                $return_json = array();
                $return_json['cid'] = $cid;
                $return_json['ccid'] = $ccid;
                $return_json['fee'] = $fee;
                $return_json['version'] = $version;
                $return_json['info'] = $res;
                $return_json['fileHashList'] = $fileHashLists;
                $return_json['chunkList'] = $chunkLists;
                $return_json['metainfo'] = $metainfo;

                $return_json['dataid'] = $dataid;

                $response = Utils::customizeResponse("200", "200", "SUCCESS", array('data'=>$return_json));

            }else{
                $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
            }

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }



        echo json_encode($response);
        http_response_code($response->status);
        exit;
    }

    private function getContentsInfoJson($contents_id){

        if(strlen($contents_id) > 0){
            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $contents_id
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));
                $contents_info->data->rows =  $contents_file_info->data->rows;
                $json_data  = json_decode($contents_info->data->ipfs_json_data);

                $contents_info->data;
                $return_json = base64_encode(json_encode($contents_info->data)); //$res;



                $response = $response = Utils::customizeResponse("200", "200", "SUCCESS", array('data'=>$return_json));

            }else{
                $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
            }

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }

        return $response;
        exit;

    }


    private function _getContentsInfoProductData(){
        $request_params = (object)array(
          'contents_id' => $this->input->post('contents_id' , true) ? $this->input->post('contents_id' , true) : $this->input->get('contents_id' , true),
        );

        if(strlen($request_params->contents_id) > 0){
            $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
                "contents_id"          => $request_params->contents_id
            ));

            if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
                $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                    "contents_id"           => $contents_info->data->contents_id
                ));
                $contents_info->data->rows =  $contents_file_info->data->rows;
                $json_data  = json_decode($contents_info->data->ipfs_json_data);

                $res = array();
                $res['date'] = date('YmdHis');
                $res['cat1'] = $contents_info->data->cate1;
                $res['cat2'] = $contents_info->data->cate1;
                $res['ftype'] = 'V01';
                $res['info']['contents_info'] = $contents_info->data;


                $file_cnt = 0 ;
                $img_cnt = 0;
                $fileHashLists = array();
                $chunkLists = array();

                //print_r($json_data);
                foreach($json_data->files as $key=>$val){
                    if( preg_match("/\.(json)$/i", strtolower($val->path))){
                        //json 확장자
                        $metainfo = $val->path;
                    }

                    if( preg_match("/\.(gif|jpg|jpeg|png|bmp)$/i", strtolower($val->path))){
                        //이미지확장자
                        $res['info']['img_rows'][$img_cnt]['img'] = $val->path;
                        $res['info']['img_rows'][$img_cnt]['img_size'] = $val->file_size;
                        $res['info']['img_rows'][$img_cnt]['cid'] = @$val->cid ? @$val->cid : '';
                        $fileHashLists[$img_cnt] = $val->cid;
                        $chunkLists[$img_cnt] = (int)ceil($val->file_size / $json_data->result->chunk_size);
                        $img_cnt++;
                    }
                    if( preg_match("/\.(mp4|avi)$/i", strtolower($val->path))){
                        //동영상확장자
                        $res['info']['file_rows'][$file_cnt]['filename'] = $val->path;
                        $res['info']['file_rows'][$file_cnt]['cid'] = @$val->cid ? @$val->cid : '';
                        $res['info']['file_rows'][$file_cnt]['chunk'] =  ceil($val->file_size / $json_data->result->chunk_size);
                        $res['info']['file_rows'][$file_cnt]['filesize']  = $val->file_size;
                        $fileHashLists[$file_cnt] = $val->cid;
                        $chunkLists[$file_cnt] = (int)ceil($val->file_size / $json_data->result->chunk_size);
                        $file_cnt++;
                    }
                }

                if($contents_info->data->is_adult == 'Y'){
                    $UsageRestriction = array(1,0,0,0,0);
                }else{
                    $UsageRestriction = array(0,0,0,0,0);
                }

                $cid = $contents_info->data->cid;
                $ccid = $json_data->result->ccid;
                $fee = $contents_info->data->cash;
                $version = $json_data->result->version;

                $dataid = $contents_info->data->dataid;
                /*$fileHashList = implode(',' , $fileHashLists);
                $chunkList = implode(',' , $chunkLists);*/
                /*$json_encode =  json_encode($res , JSON_UNESCAPED_UNICODE);//print_r($res);
                $fileHashList = json_encode($fileHashLists , JSON_UNESCAPED_UNICODE);
                $chunkList = json_encode($chunkLists ,JSON_UNESCAPED_UNICODE);*/


                $return_json = array();
                $return_json['cid'] = $cid;
                $return_json['ccid'] = $ccid;
                $return_json['fee'] = $fee;
                $return_json['version'] = $version;
                $return_json['info'] = base64_encode(json_encode($res)); //$res;
                $return_json['fileHashList'] = $fileHashLists;
                $return_json['chunkList'] = $chunkLists;
                $return_json['UsageRestriction'] = $UsageRestriction;
                $return_json['metainfo'] = $metainfo;

                $return_json['dataid'] = $dataid;

                $response = $response = Utils::customizeResponse("200", "200", "SUCCESS", array('data'=>$return_json));

            }else{
                $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
            }

        }else{
            $response = Utils::customizeResponse("200", "400", "콘텐츠 정보가 없습니다.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _contentsDownInfo(){

        $request_params = (object)array(
            'contents_id' => $this->input->post('contents_id' , true),
        );

        $contents_info = $this->contents_model->getContentsSingleInfo((object)array(
            "contents_id"          => $request_params->contents_id,
        ));

        if($contents_info->code == 200 && $contents_info->data->contents_id > 0){
            $contents_file_info = $this->contents_model->getContentFileInfo((object)array(
                "contents_id"           => $contents_info->data->contents_id
            ));

            foreach($contents_file_info->data->rows as $key=>$val){
                $contents_file_info->data->rows[$key]->realsize_str = $this->getFileSizeStr($val->realsize);
            }
            $contents_info->data->rows =  $contents_file_info->data->rows;
        }

        echo json_encode($contents_info);
        exit;
    }

    public function lists_api_D(){
        $user =  $this->session->userdata('AUSER');
        $this->load->library('Pagination');

        //print_r($user);

        $request_params = (object)array(
            'nowPage'         => $this->input->get('pageNum' , true) ? $this->input->get('pageNum' , true) : 1,
            'rowPerPage'     => $this->input->get('pageSize' , true) ? $this->input->get('pageSize' , true) : 20,
            'search_key'      => $this->input->get('search_key' , true) ? $this->input->get('search_key' , true) : "",
            'search_value'   => $this->input->get('search_key' , true) ? $this->input->get('search_value' , true) : "",
        );
        //print_r($request_params);
        $api_data = $this->getTechOnMediaApi((object)array(
            'nowPage'         => $request_params->nowPage,
            'rowPerPage'     => $request_params->rowPerPage,
            'search_key'      => $request_params->search_key,
            'search_value'   => $request_params->search_value,
        ));
        
        $json =  json_decode($api_data['data']);
        //print_r($json);
        
        if($json->status == 'success' && count($json->result) > 0){

            if(count($json->result) > 0){
                $data = new \stdClass();
                foreach($json->result as $key=>$val){
                    $data->rows[$key]= new \stdClass();
                    $data->rows[$key]->ccid = $val->ccid;
                    $data->rows[$key]->ccid_ver = $val->version;
                   if($val->meta_container[0]->metadata->contents_info) {
                       $contents_json = json_decode(base64_decode($val->meta_container[0]->metadata->contents_info));

                       $user_sell_info =  $this->contents_model->getUserSellSingleConntents((object)array(
                           "contents_id"           => $contents_json->contents_id,
                           "userid"                   => $user->eth_account
                       ));


                       $data->rows[$key]->contents_id = $contents_json->contents_id;
                       $data->rows[$key]->cate1 = $contents_json->cate1 ? $contents_json->cate1 : '';
                       $data->rows[$key]->cate2 = $contents_json->cate2 ? $contents_json->cate2 : '';
                       $data->rows[$key]->userid = $contents_json->userid;
                       $data->rows[$key]->title = $contents_json->title;
                       $data->rows[$key]->contents = $contents_json->contents;
                       $data->rows[$key]->size = $contents_json->size;
                       $data->rows[$key]->str_size = $this->getFileSizeStr($contents_json->size);
                       $data->rows[$key]->cash = $contents_json->cash;
                       $data->rows[$key]->is_adult = $contents_json->is_adult;
                       $data->rows[$key]->cid = $contents_json->cid;
                       $data->rows[$key]->drm = $contents_json->drm;
                       $data->rows[$key]->watermarking = $contents_json->watermarking;
                       $data->rows[$key]->regdate   = $val->owner_reg_date;
                       $data->rows[$key]->productId = @$user_sell_info->data->productId;
                   }else{
                       $data->rows[$key]->contents_id = $val->meta_container[0]->metadata->vender_id;
                       $data->rows[$key]->cate1 = $val->meta_container[0]->metadata->genre[0];
                       $data->rows[$key]->cate2 = '';
                       $data->rows[$key]->title = $val->meta_container[0]->metadata->title;
                       $data->rows[$key]->contents = $val->meta_container[0]->metadata->synopsis;

                       $total_size = 0;
                       foreach($val->meta_container[0]->metadata->artwork as $k=>$v){
                           if( preg_match("/\.(mp4|avi|mkv|)$/i", strtolower($v->file_name))){
                               //동영상확장자
                                $total_size += $v->file_size;
                           }
                       }
                       $data->rows[$key]->size = $total_size;
                       $data->rows[$key]->str_size = $total_size;
                       $data->rows[$key]->cash = 0;
                       $data->rows[$key]->is_adult = 'N';
                       $data->rows[$key]->cid = '';
                       $data->rows[$key]->drm = 'N';
                       $data->rows[$key]->watermarking = 'N';
                       $data->rows[$key]->regdate   = $val->owner_reg_date;
                       $data->rows[$key]->productId = '';
                   }

                }
            }else{
                $json->pagination->search_count = 0;
            }

            $data->total_rows = $json->pagination->search_count;
            $data->num_start  = $json->pagination->search_count - ($request_params->nowPage - 1) * $request_params->rowPerPage;

            $response = Utils::customizeResponse("200", "200", "SUCC", $data);



        }else{
            $json->pagination->search_count = 0;
            $response = Utils::customizeResponse("200", "400", "SUCC", (object)array('rows' => array() , 'total_rows'=>0));
        }

        $total_rows = $json->pagination->search_count;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(__FUNCTION__)."/", ($total_rows ? $total_rows : 0), $request_params->rowPerPage);


        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/contents/D/list_api",array(
            "request_params"        => $request_params,
            "data"                        => $response->data,
            "paging"                    => $paging
        ));
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");

    }

    public function getTechOnMediaApi($params){

        $this->load->library('RestApi');
        $this->restapi = new RestApi('http://15.164.5.18:80');
        $data = array('data'=>array(
            "nowPage"            => $params->nowPage,
            "rowPerPage"        => $params->rowPerPage,
            "sortField"             => 'owner_reg_date',
            "sortOrder"            => 'desc',
        ));
        if($params->search_key && $params->search_value){
            $data['data'][$params->search_key] = $params->search_value;
        }
        $response = $this->restapi->get("/ccsearch/v1/search.do",
        $data
        );

        return $response;
        //return $response['data'];
    }

    private function _getAjaxLastContentInfo(){
        $res = array(
            'result'=> false,
            'content'=> '',
            'message'=> '',
        );
        $user = $this->session->userdata('AUSER');

        $contents_info = $this->contents_model->getContentsLastSingleInfoByUserId((object)array(
            "eth_account" => $user->eth_account
        ));

        if($contents_info->code == 200 && $contents_info->data->contents_id > 0) {
            $res['result']  = true;
            $res['content'] = $contents_info->data;
        } else {
            $res['message'] = "데이터 정보가 없습니다.";
        }

        echo json_encode($res);
        exit;
    }




}
/*
		$this->load->library('JWT');
		$token = JWT::encode((object)array("aaa","bbb"), AUTH_TOKEN_KEY);
		$decoded = JWT::decode($token, AUTH_TOKEN_KEY, array('HS256'));
*/
?>