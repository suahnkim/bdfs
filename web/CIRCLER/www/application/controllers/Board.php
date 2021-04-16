<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Board extends CI_Controller {
    function __construct(){
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('board_model');
    }

    public function _remap($method, $params = array()){
        if($this->input->is_ajax_request()) $method = '_'.$method;
        if(!$this->user_model->isSignedIn()){
            if($this->input->is_ajax_request()){
                http_response_code(401);
                echo json_encode(array("code"=>401, "message"=>"로그인 후 이용하실 수 있습니다."));
                return;
            }else{
                header("Location: /user/signin");
                return;
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
                http_response_code(404);
                echo json_encode(array("code"=>404, "message"=>"404"));
            }else{
                show_404();
            }
            return;
        }
    }

    public function manage($qre1){
        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}();
        }else{
            show_404();
        }
    }

    public function manage_write(){
        $user = $this->user_model->getUser();

        $request_params = (object)array(
            "board_info_id"     =>  $this->input->get('board_info_id' , true)  ?   $this->input->get('board_info_id' , true) : "",
        );

        $response = $this->board_model->getBoardInfoSingleSearch((object)array(
            "board_info_id"           =>    $request_params->board_info_id,
        ));

        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                             =>  $user,
        ));


        $this->load->view(MC_VIEWS_PATH."/board/manage/write" , array(
            "data"      => @$response->data,
        ));
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

    public function manage_list(){
        $user = $this->user_model->getUser();
        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "board_info_id"			                       => $this->input->get('board_info_id', true) == "" ? "" : $this->input->get('board_info_id' , true),
            "search_key"                                     => $this->input->get('search_key' , true) == "" ? "" : $this->input->get('search_key' , true),
            "search_value"                                  => $this->input->get('search_value' , true) == "" ? "" : $this->input->get('search_value' , true),
        );


        $response = $this->board_model->getBoardInfoSearch((object)array(
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "search_key"              => $request_params->search_key,
            "search_value"            => $request_params->search_value,
            'state'                        => 0.
        ));

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                             =>  $user,
            'request_params'             =>  $request_params,
            'data'                             =>  $response->data,
            'paging'                         =>  $paging,
        ));
        $this->load->view(MC_VIEWS_PATH."/board/manage/list");
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

    public function lists(){

        $this->load->library('Pagination');
        $user = $this->user_model->getUser();
        $bbs_id = $this->uri->segment(3);
        $manage_level = @$user->manager_level;

        $request_params = (object)array(
            "start_date"        => $this->input->get('start_date' , true) == "" ? "" : $this->input->get('start_date' , true),
            "end_date"         => $this->input->get('end_date' , true) == "" ? "" : $this->input->get('end_date' , true),
            "search_key"		=> $this->input->get('search_key', true) == "" ? "" : $this->input->get('search_key', true),
            "search_value"	=> $this->input->get('search_value', true) == "" ? "" : $this->input->get('search_value', true),
            "page_num"		=> $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"		=> $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "bbs_id"            => $bbs_id,
        );

        $board_config = $this->board_model->getBoardInfoSingle((object)array(
            'bbs_id'        => $bbs_id,
        ));

        $response = $this->board_model->getBoardSearch((object)array(
            'search_key'      =>  $request_params->search_key,
            'search_value'   =>   $request_params->search_value,
            'page_num'       =>  $request_params->page_num,
            'page_size'       =>  $request_params->page_size,
            'bbs_id'            =>  $board_config->data->bbs_id,
            "start_date"       => $request_params->start_date,
            "end_date"        => $request_params->end_date,
            "page_yn"         => 'Y',
            "depth"             => $board_config->data->bbs_id == 'qna' ? 0 : '',
            "user_info_id"    => $board_config->data->bbs_id == 'qna' ? $user->user_info_id : '',
        ));

        if($response->code == 200 && $request_params->bbs_id == 'qna'){
            foreach($response->data->rows as $key=>$val){
                $reply_data = $this->board_model->getBoardReplySearch((object)array(
                    'bbs_id'    => $val->bbs_id,
                    'wgroup'   => $val->wgroup,
                    'depth'      => 1,
                ));
                if($reply_data->code == 200) $response->data->rows[$key]->reply = $reply_data->data;
                else $response->data->rows[$key]->reply = array();
                //else $response->data->rows->reply_data[$key] = (object)array();

            }
        }

        $response->data->board_config = $board_config->data;

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".strtolower(__CLASS__).'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/".$board_config->data->bbs_id), ($total_rows ? $total_rows : 0), $request_params->page_size);

        $this->load->view("/inc/header" , (object)array(
            "user"                 => $user
        ));
        $this->load->view("/inc/top");

        $this->load->view("/board/{$bbs_id}_list" , (object)array(
            'request_params'          =>  $request_params,
            'data'                          =>  $response->data,
            'paging'                       =>  $paging
        ));
        $this->load->view("/inc/footer_js");
        $this->load->view("/inc/footer");

    }

    public function view(){
        $this->load->helper('alert');
        $user = $this->user_model->getUser();

        $bbs_id = $this->uri->segment(3);

        $request_params = (object)array(
            "board_id"	        => $this->input->get('board_id', true) == "" ? "" : $this->input->get('board_id', true),
            "bbs_id"            => $bbs_id,
        );

        $board_config = $this->board_model->getBoardInfoSingle((object)array(
            'bbs_id'        => $bbs_id,
        ));

        $response = $this->board_model->getBoardSingle((object)array(
            'bbs_id'           => $board_config->data->bbs_id,
            "board_id"       => $request_params->board_id,
        ));

        if($response->code != 200){
            $response->data = new stdClass();
        }
        $response->data->board_config = $board_config->data;

        if($response->code != 200){
            alert('잘못된 접급입니다.');
        }else{

            $file_data = $this->board_model->getBoardFile((object)array(
                'board_id'      => $response->data->board_id,
            ));
            $prev_board  = $this->board_model->getBoardPrevNo((object)array(
                'bbs_id'     => $request_params->bbs_id,
                'board_id'  => $request_params->board_id,
            ));

            $next_board  = $this->board_model->getBoardNextNo((object)array(
                'bbs_id'     => $request_params->bbs_id,
                'board_id'  => $request_params->board_id,
            ));

            $response->data->prev_board_id = $prev_board->code == 200 ? $prev_board->data->board_id : '';
            $response->data->next_board_id = $next_board->code == 200 ? $next_board->data->board_id : '';

            if($file_data->code == 200) $response->data->file_rows = $file_data->data->rows;
            else $response->data->file_rows = array();

            if(isset($response->data->board_id)){
                $reply_data = $this->board_model->getBoardReplySearch((object)array(
                    'bbs_id'    => $response->data->bbs_id,
                    'wgroup'   => $response->data->wgroup,
                    'depth'      => 1,
                ));
                if($reply_data->code == 200){
                    $response->data->reply_data = $reply_data->data;
                    $reply_file_data = $this->board_model->getBoardFile((object)array(
                        'board_id'      => $reply_data->data->board_id,
                    ));
                    if($reply_file_data->code == 200) $response->data->reply_data->file_rows = $reply_file_data->data->rows;
                    else $response->data->reply_data->file_rows = array();
                }else{
                    $response->data->reply_data = array();
                }

            }
        }

        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                  => $user,
        ));


        $this->load->view(MC_VIEWS_PATH."/board/skin/".$board_config->data->skin_name . '/view' ,array(
            'request_params'          =>  $request_params,
            'data'                          =>  $response->data,
        ));
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

    public function form(){
        $this->load->helper('alert');
        $user = $this->user_model->getUser();

        $bbs_id = $this->uri->segment(3);
        $act = $this->uri->segment(4);
        $manage_level = @$user->manager_level;

        $request_params = (object)array(
            "board_id"	        => $this->input->get('board_id', true) == "" ? "" : $this->input->get('board_id', true),
            "bbs_id"            => $bbs_id,
            "act"                 => $act,
        );

        $board_config = $this->board_model->getBoardInfoSingle((object)array(
            'bbs_id'        => $bbs_id,
        ));

        $response = $this->board_model->getBoardSingle((object)array(
            'bbs_id'           => $board_config->data->bbs_id,
            "board_id"       => $request_params->board_id,
        ));

        if($response->code != 200){
            $response->data = new stdClass();
        }
        $response->data->board_config = $board_config->data;
        $file_data = $this->board_model->getBoardFile((object)array(
            'board_id'      =>@$response->data->board_id,
        ));
        if($file_data->code == 200) $response->data->file_rows = $file_data->data->rows;
        else $response->data->file_rows = array();

        if($request_params->act == 'mod' || $request_params->act == 'reply'){
            if(empty($request_params->bbs_id)){
                alert('잘못된 접급입니다.');
            }else{
                if($response->code != 200){
                    alert('잘못된 접급입니다.');
                }
            }
        }

        $this->load->view("/inc/header" , (object)array(
            "user"                 => $user
        ));
        $this->load->view("/inc/top");
        $this->load->view("/board/qna_write" , (object)array(
            'request_params'          =>  $request_params,
            'data'                          =>  $response->data,
        ));
        $this->load->view("/inc/footer_js");
        $this->load->view("/inc/footer");
    }


    private function _setBoardManage(){
        $request_params = (object)array(
            'act'                 => $this->input->post('act' , true) ,
            'board_info_id'   => $this->input->post('board_info_id' , true),
            'bbs_id'            => $this->input->post('bbs_id' , true),
            'bbs_name'       => $this->input->post('bbs_name' , true),
            'skin_name'       => $this->input->post('skin_name' , true),
            'file_use'           => $this->input->post('file_use' , true),
            'file_cnt'           => $this->input->post('file_cnt' , true),
            'reply'              => $this->input->post('reply' , true),
            'comment'        => $this->input->post('comment' , true),
            'secret'            => $this->input->post('secret' , true),
            'ca_title'           => $this->input->post('ca_title' , true),
            'ca_name'        => $this->input->post('ca_name' , true),
            'list_cnt'           => $this->input->post('list_cnt' , true),
            'editor_use'      => $this->input->post('editor_use' , true),
            'board_info_id'  => $this->input->post('board_info_id' , true) ? $this->input->post('board_info_id' , true) : '',
        );

        if($request_params->act == "reg"){
            $response = $this->board_model->postBoardManage((object)array(
                'board_info_id'   => $request_params->board_info_id,
                'bbs_id'            => $request_params->bbs_id,
                'bbs_name'       => $request_params->bbs_name,
                'skin_name'       => $request_params->skin_name,
                'file_use'           => $request_params->file_use,
                'file_cnt'           => $request_params->file_cnt,
                'ca_title'           => $request_params->ca_title,
                'ca_name'        => $request_params->ca_name,
                'list_cnt'           => $request_params->list_cnt,
                'editor_use'      => $request_params->editor_use,
                'reply'              => $request_params->reply,
                'comment'        => $request_params->comment,
                'secret'            => $request_params->secret,
            ));
        }else{
            $response = $this->board_model->modBoardManage((object)array(
                'board_info_id'   => $request_params->board_info_id,
                'bbs_id'            => $request_params->bbs_id,
                'bbs_name'       => $request_params->bbs_name,
                'skin_name'       => $request_params->skin_name,
                'file_use'           => $request_params->file_use,
                'file_cnt'           => $request_params->file_cnt,
                'ca_title'           => $request_params->ca_title,
                'ca_name'        => $request_params->ca_name,
                'list_cnt'           => $request_params->list_cnt,
                'editor_use'      => $request_params->editor_use,
                'board_info_id'  => $request_params->board_info_id,
                'reply'              => $request_params->reply,
                'comment'        => $request_params->comment,
                'secret'            => $request_params->secret,
            ));
        }



        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _checkField(){
        $request_params = (object)array(
            'search_key'        => $this->input->post('search_key' , true) ,
            'search_value'     => $this->input->post('search_value' , true) ,
        );



        $result = array();
        $result['result'] = false;
        switch ($request_params->search_key){
            case 'bbs_id'  :

                $first_char = substr($request_params->search_value,0,1);

                if (preg_match("/[^0-9a-z_]+/i", $request_params->search_key)) {
                    $result['msg']  = "유효하지 않은 게시판 아이디입니다.";
                } else if (strlen($request_params->search_value) < 2) {
                    $result['msg']  = "최소 2자 이상 입력하세요 ";
                    // echo "120"; // 4보다 작은 회원아이디
                } else if (strlen($request_params->search_value) > 20) {
                    $result['msg']  = "최대 20자 이내로 입력하세요 ";
                } else if (!preg_match("/[^0-9_]+/i", $first_char)) {
                    $result['msg']  = "첫자는 반드시 영문으로 하셔야합니다.";
                } else {
                    $response = $this->board_model->getBoardInfoRedundancyCheck((object)array(
                        'search_key'        => $request_params->search_key,
                        'search_value'      => $request_params->search_value,
                    ));

                    if ($response->code == 200 && $response->message == 'ADD') {
                        $result['msg']  = "사용가능";
                        $result['result'] = true;
                    } else {
                        $result['msg']  = "사용불가능";
                    }
                }

                break;
            case 'bbs_name'  :

                if ( preg_match('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]/u',$request_params->search_value) ) {
                    $result['msg'] = "공백없이 한글, 영문, 숫자만 입력 가능합니다.";
                }else {

                    $response = $this->board_model->getBoardInfoRedundancyCheck((object)array(
                        'search_key' => $request_params->search_key,
                        'search_value' => $request_params->search_value,
                    ));
                    if ($response->code == 200 && $response->message == 'ADD') {
                        $result['msg'] = "사용가능";
                        $result['result'] = true;
                    } else {
                        $result['msg'] = "사용불가능";
                    }
                }
                break;
        }

        echo json_encode($result);
    }

    public function index(){
        $user = $this->user_model->getUser();

        $to_day = date('Y-m-d');
        $to_month = date('Y-m');


        $_graph1 = array();
        $_graph1['name']    =  '';
        $_graph1['type']    = 'line';
        $_graph1['showInLegend'] = true;
        $_graph1['dataPoints'] = array();
        for($d=0; $d<15; $d++){
            //echo date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))) . "<br>";
            $current_date = date('m-d' , strtotime('- '.$d.' day' , strtotime($to_day)));
            $stistic_info  = $this->user_model->getUserStatisticInfo((object)array(
                "search_key"        =>      'day' ,
                "search_date"       =>       date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))),
                "state"                 =>       1,
            ));

            if($stistic_info->code == 200){
                $response = array('y'=>(int)$stistic_info->data->cnt , 'label'=>$current_date);
            }else{
                $response = array('y'=>(int)0 , 'label'=>$current_date);
            }
            $_graph1['dataPoints'][$d] = $response;
        }

        $_graph2 = array();
        $_graph2['name']    = '';
        $_graph2['type']    = 'column';
        $_graph2['showInLegend']    = true;
        $_graph2['dataPoints'] = array();
        for($m=0; $m<6; $m++){
            $current_date = date('m' , strtotime('- '.$m.' month' , strtotime($to_month)));
            $stistic_info  = $this->user_model->getUserStatisticInfo((object)array(
                "search_key"        =>      'month' ,
                "search_date"       =>       date('Y-m' , strtotime('- '.$m.' month' , strtotime($to_day))),
                "state"                 =>       1,
            ));

            if($stistic_info->code == 200){
                $response = array('y'=>(int)$stistic_info->data->cnt , 'label'=>$current_date . '월');
            }else{
                $response = array('y'=>(int)0 , 'label'=>$current_date . '월');
            }
            $_graph2['dataPoints'][$m] = $response;
        }

        for($d=0; $d<7; $d++){
            //echo date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))) . "<br>";
            $current_date = date('Y.m.d' , strtotime('- '.$d.' day' , strtotime($to_day)));
            $stistic_info  = $this->user_model->getUserStatisticInfo((object)array(
                "search_key"        =>      'day' ,
                "search_date"       =>       date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))),
                "state"                 =>       8,
            ));

            if($stistic_info->code == 200){
                $response = array('date'=>$current_date , 'cnt'=>$stistic_info->data->cnt);
            }else{
                $response = array('date'=>$current_date , 'cnt'=>0);
            }

            $secede_data[] = $response;
        }


        $_graph3 = array();
        $_graph3['name']    =  '';
        $_graph3['type']    = 'line';
        $_graph3['showInLegend'] = true;
        $_graph3['indexLabel'] = "{y}";
        $_graph3['dataPoints'] = array();
        for($d=0; $d<15; $d++){
            //echo date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))) . "<br>";
            $current_date = date('m/d' , strtotime('- '.$d.' day' , strtotime($to_day)));
            $stistic_info  = $this->contents_model->getSellProductStatisticInfo((object)array(
                "search_date"       =>       date('Y-m-d' , strtotime('- '.$d.' day' , strtotime($to_day))),
                "state"                 =>       'Y',
            ));
            if($stistic_info->code == 200){
                $response = array('y'=>(int)$stistic_info->data->sum_point , 'label'=>$current_date);
            }else{
                $response = array('y'=>(int)0 , 'label'=>$current_date);
            }
            $_graph3['dataPoints'][$d] = $response;
        }


        $yesterday_point_data = $this->contents_model->getSellContentsPointTop((object)array(
            "search_date"       =>       '2019-09-18',//date('Y-m-d' , strtotime('- 1 day' , strtotime($to_day))),
            "state"                 =>        'Y',
            'limit'                    =>        6,
        ));

        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                             =>  $user,
            'graph1'                          => json_encode($_graph1),
            'graph2'                          => json_encode($_graph2),
            'graph3'                          => json_encode($_graph3),
            'secede_data'                 => $secede_data,
            'yesterday_point_data'      => $yesterday_point_data->data
        ));
        $this->load->view(MC_VIEWS_PATH."/index/default");
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

    private function _form_submit(){

      /*  if(is_array(@$this->input->post('del_board_file',true))){
            $board_file_ids = implode(',' , $this->input->post('del_board_file',true));
            $file_data = $this->board_model->getBoardFile
        }*/

        $user = $this->user_model->getUser();
        $request_params = (object)array(
            'act'               => $this->input->post('act' , true),
            'bbs_id'          => $this->input->post('bbs_id', true),
            'board_id'       => $this->input->post('board_id', true) ? $this->input->post('board_id', true) : "",
            'user_info_id'  => $user->user_info_id,
            'user_email'    => $user->email,
            'pwd'             => $this->input->post('pwd', true) ? $this->input->post('pwd', true) : "",
            'email'             => $this->input->post('email', true) ? $this->input->post('email', true) : "",
            'subject'         => $this->input->post('subject', true) ,
            'contents'       => $this->input->post('contents', true),
            'pnum'            => $this->input->post('pnum', true) ? $this->input->post('punm', true) : 0,
            'sort'              => $this->input->post('sort', true) ? $this->input->post('sort', true) : 0,
            'depth'            => $this->input->post('depth', true) ? $this->input->post('depth', true) : 0,
            'wgroup'          => $this->input->post('wgroup', true) ? $this->input->post('wgroup', true) : 0,
            'secret'           => $this->input->post('secret', true) ? $this->input->post('secret', true) : "",
            'ca_name'       => $this->input->post('ca_name', true) ? $this->input->post('ca_name', true) : "",
            'phone'           => $this->input->post('phone', true) ? $this->input->post('phone', true) : "",
            'hp'                => $this->input->post('hp', true) ? $this->input->post('hp', true) : "",
            'extra1'           => $this->input->post('extra1', true) ? $this->input->post('extra1', true) : "",
            'extra2'           => $this->input->post('extra2', true) ? $this->input->post('extra2', true) : "",
            'extra3'           => $this->input->post('extra3', true) ? $this->input->post('extra3', true) : "",
            'extra4'           => $this->input->post('extra4', true) ? $this->input->post('extra4', true) : "",
            'extra5'           => $this->input->post('extra5', true) ? $this->input->post('extra5', true) : "",
            'fix'                => $this->input->post('fix', true) ? $this->input->post('fix', true) : "",

        );

        $absolute_path = "/data/upload/circler";
        $ftp_mak_dir  = date('Y').'/'.date('m').'/'. date('d');

       switch ($request_params->act){
           case 'add' :
               $response = $this->board_model->addBoard($request_params);

               if($response->code == 200){
                   if(is_array(@$_FILES['filename'])){


                       $fileData = array();
                       for($i=0; $i<count($_FILES['filename']['name']); $i++){
                           if($_FILES['filename']['error'][$i] == 0) {
                               $ext = pathinfo($_FILES['filename']['name'][$i], PATHINFO_EXTENSION);
                               $fileData['file_name'][$i] = time() . '_' . md5($_FILES['filename']['name'][$i]) .'.'. $ext;
                               $fileData['file_path'][$i] = '/' .$ftp_mak_dir;
                               $fileData['file_origin_name'][$i] = $_FILES['filename']['name'][$i];
                               $fileData['file_size'][$i] = $_FILES['filename']['size'][$i];
                               $fileData['file_type'][$i]  =  $ext;

                               $upfile_result = $this->ftpUpload($absolute_path , $ftp_mak_dir , $fileData['file_name'][$i] , $_FILES['filename']['tmp_name'][$i]);
                               if(!$upfile_result['result']){
                                   $response = Utils::customizeResponse("200", "302", "업로드에 실패하였습니다.", null);
                                   echo json_encode($response);
                                   http_response_code($response->status);
                                   exit;
                               }
                           }
                       }
                   }

                   if(count(@$fileData) > 0) {
                       $file_response = $this->board_model->postBoardFile((object)array(
                           "data" => (object)$fileData,
                           "board_id" => $response->data->board_id,
                           "ftp_domain" => CONTENTS_IMAGE_URL,
                       ));
                   }

               }
                break;
           case 'mod' :

               $response = $this->board_model->modBoard((object)array(
                   'board_id'       => $request_params->board_id,
                   'subject'         => $request_params->subject,
                   'secret'           => $request_params->secret,
                   'ca_name'       => $request_params->ca_name,
                   'phone'           => $request_params->phone,
                   'hp'                => $request_params->hp,
                   'extra1'           => $request_params->extra1,
                   'extra2'           => $request_params->extra2,
                   'extra3'           => $request_params->extra3,
                   'extra4'           => $request_params->extra4,
                   'extra5'           => $request_params->extra5,
                   'fix'                => $request_params->fix,
                   'contents'       => $request_params->contents,
               ));

               if($response->code == 200) {

                   if (count(@$this->input->post('del_board_file', true)) > 0) {
                       foreach ($this->input->post('del_board_file', true) as $key => $val) {
                           if($val) {
                               $file_data = $this->board_model->getBoardFileSingle((object)array(
                                   'board_file_no' => $val,
                               ));
                               $return = $this->ftpDelete($file_data->data->file_path, $file_data->data->file_name);
                               $this->board_model->setDelBoardFile((object)array(
                                   'board_file_no' => $val,
                                   'del_yn' => 'Y',
                               ));
                           }
                          /* if ($return['result']) {

                           }*/
                       }
                   }

                   if (count(@$this->input->post('change_board_file_no', true)) > 0) {
                       foreach ($this->input->post('change_board_file_no', true) as $key => $val) {
                           if($val) {
                               $file_data = $this->board_model->getBoardFileSingle((object)array(
                                   'board_file_no' => $val,
                               ));
                               $return = $this->ftpDelete($file_data->data->file_path, $file_data->data->file_name);
                               $this->board_model->setDelBoardFile((object)array(
                                   'board_file_no' => $val,
                                   'del_yn' => 'Y',
                               ));
                           }
                           /*if ($return['result']) {

                           }*/
                       }
                   }

                   if (is_array(@$_FILES['filename'])) {
                       $fileData = array();
                       for ($i = 0; $i < count($_FILES['filename']['name']); $i++) {
                           if ($_FILES['filename']['error'][$i] == 0) {
                               $ext = pathinfo($_FILES['filename']['name'][$i], PATHINFO_EXTENSION);
                               $fileData['file_name'][$i] = time() . '_' . md5($_FILES['filename']['name'][$i]) . '.' . $ext;
                               $fileData['file_path'][$i] = '/' . $ftp_mak_dir;
                               $fileData['file_origin_name'][$i] = $_FILES['filename']['name'][$i];
                               $fileData['file_size'][$i] = $_FILES['filename']['size'][$i];
                               $fileData['file_type'][$i] = $ext;

                               $upfile_result = $this->ftpUpload($absolute_path, $ftp_mak_dir, $fileData['file_name'][$i], $_FILES['filename']['tmp_name'][$i]);
                               if (!$upfile_result['result']) {
                                   $response = Utils::customizeResponse("200", "302", "업로드에 실패하였습니다.", null);
                                   echo json_encode($response);
                                   http_response_code($response->status);
                                   exit;
                               }
                           }
                       }
                   }

                   if (count(@$fileData) > 0) {
                       $file_response = $this->board_model->postBoardFile((object)array(
                           "data" => (object)$fileData,
                           "board_id" => $response->data->board_id,
                           "ftp_domain" => CONTENTS_IMAGE_URL,
                       ));
                   }

               }

                break;
           case 'reply' : //$response = $this->board_model->replyBoard($request_params);
               $response = $this->board_model->replyBoard($request_params);

               if($response->code == 200){
                   if(is_array(@$_FILES['filename'])){


                       $fileData = array();
                       for($i=0; $i<count($_FILES['filename']['name']); $i++){
                           if($_FILES['filename']['error'][$i] == 0) {
                               $ext = pathinfo($_FILES['filename']['name'][$i], PATHINFO_EXTENSION);
                               $fileData['file_name'][$i] = time() . '_' . md5($_FILES['filename']['name'][$i]) .'.'. $ext;
                               $fileData['file_path'][$i] = '/' .$ftp_mak_dir;
                               $fileData['file_origin_name'][$i] = $_FILES['filename']['name'][$i];
                               $fileData['file_size'][$i] = $_FILES['filename']['size'][$i];
                               $fileData['file_type'][$i]  =  $ext;

                               $upfile_result = $this->ftpUpload($absolute_path , $ftp_mak_dir , $fileData['file_name'][$i] , $_FILES['filename']['tmp_name'][$i]);
                               if(!$upfile_result['result']){
                                   $response = Utils::customizeResponse("200", "302", "업로드에 실패하였습니다.", null);
                                   echo json_encode($response);
                                   http_response_code($response->status);
                                   exit;
                               }
                           }
                       }
                   }

                   if(count(@$fileData) > 0) {
                       $file_response = $this->board_model->postBoardFile((object)array(
                           "data" => (object)$fileData,
                           "board_id" => $response->data->board_id,
                           "ftp_domain" => CONTENTS_IMAGE_URL,
                       ));
                   }

               }
                break;
       }

        echo json_encode($response);
        http_response_code($response->status);

    }

    private function _delBoard(){
        $request_params = (object)array(
          'board_id'    =>  $this->input->post('board_id' , true),
        );
        $response = $this->board_model->setDeltBoard((object)array('board_id' =>$request_params->board_id));
        if($response->code == 200){
            if(count($response->data->rows) > 0){
                foreach($response->data->rows as $key=>$val) {
                    $loca_upload_path = $val->file_path;
                    $return = @$this->ftpDelete($loca_upload_path , $val->file_name);
                }
            }
        }
        echo json_encode($response);
        http_response_code($response->status);
    }



    private function ftpUpload($absolute_path ,  $ftp_make_dir , $filename , $origin_file){

        $result = array();

        $conn_id = ftp_connect(FTP_IP, FTP_PORT); // 7777->FTP_PORT
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
        }

        return $result;
    }

    public function ftpDelete($file_path , $file_name){
        $result = array();
        $conn_id = ftp_connect(FTP_IP, FTP_PORT); // 7777->FTP_PORT
        $conn_login = ftp_login($conn_id, FTP_ID, FTP_PASS);

        $result['result'] = true;
        $result['msg'] = '';
        if(!$conn_id || !$conn_login){
            $result['result'] = false;
            $result['msg'] = 'FTP 연결에 실패하였습니다.';
        }else{
            ftp_pasv($conn_id, true);
        }
        @ftp_chdir($conn_id, $file_path);
        //$res = ftp_size($conn_id, $file_path .'/'.$file_name);
        //if ($res != -1) {
            if(!@ftp_delete($conn_id, $file_name))
            {
                $result['result'] = false;
                $result['msg'] = '파일을 지정한 디렉토리에서 삭제 하는 데 실패했습니다.';
            }else{
                $result['result'] = true;
                $result['msg'] = 'SUCC';
            }
        //}

        return $result;
    }


    public function uploadFile($files , $filename){
        if (is_array(@$files[$filename])) {
            $fileData = array();
            for ($i = 0; $i < count($_FILES[$filename]['name']); $i++) {
                if ($_FILES['filename']['error'][$i] == 0) {
                    $ext = pathinfo($_FILES[$filename]['name'][$i], PATHINFO_EXTENSION);
                    $fileData['file_name'][$i] = time() . '_' . md5($_FILES[$filename]['name'][$i]) . '.' . $ext;
                    $fileData['file_path'][$i] = '/' . $ftp_mak_dir;
                    $fileData['file_origin_name'][$i] = $_FILES[$filename]['name'][$i];
                    $fileData['file_size'][$i] = $_FILES[$filename]['size'][$i];
                    $fileData['file_type'][$i] = $ext;

                    $upfile_result = $this->ftpUpload($absolute_path, $ftp_mak_dir, $fileData['file_name'][$i], $_FILES[$filename]['tmp_name'][$i]);
                    if (!$upfile_result['result']) {
                        $response = Utils::customizeResponse("200", "302", "업로드에 실패하였습니다.", null);
                        echo json_encode($response);
                        http_response_code($response->status);
                        exit;
                    }
                }
            }
        }
    }

    public function download(){

        $request_params = (object)array(
          "board_file_no"       => $this->input->get('board_file_no' , true),
        );
       $response = $this->board_model->getBoardFileSingle((object)array(
            "board_file_no"     => $request_params->board_file_no,
        ));

       $data = $response->data;

        $url = "http://".$response->data->file_domain  . $response->data->file_path .'/'.$response->data->file_name; //'http://coimg.circler.co.kr/2019/10/21/1571638144_d41d8cd98f00b204e9800998ecf8427e.jpg';
        //echo $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec ($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code == 200) {
            // 파일 다운로드
            $filename = $data->file_origin_name;
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-type: application/octet-stream");
            header("Content-Transfer-Encoding: binary");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $file = curl_exec ($ch);
            curl_close($ch);
        }else{
            echo "<script></script>";
            exit;
        }

        exit;


     /*   if($response->code == 200){

            //$data = $response->data;
            $result = array();
            $conn_id = ftp_connect(FTP_IP, FTP_PORT); // 7777->FTP_PORT
            $conn_login = ftp_login($conn_id, FTP_ID, FTP_PASS);


            if(!$conn_id || !$conn_login){
                $result['result'] = false;
                $result['msg'] = 'FTP 연결에 실패하였습니다.';
            }
            ftp_chdir($conn_id, $response->data->file_path);
            ftp_get($conn_id,  $response->data->file_origin_name,  $response->data->file_name,FTP_BINARY);
        }*/


    }



}
?>