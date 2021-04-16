<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class mypage extends CI_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('contents_model');

    }

    public function _remap($method, $params = array()){
        if($this->input->is_ajax_request()) $method = '_'.$method;
        if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "_check_eth_account" || $method == "_login_view"){
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


    public  function wish(){
        $user = $this->user_model->getUser();
        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
        );

        $response = $this->contents_model->getContentsZzimList((object)array(
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "accountId"                 => $user->eth_account,
        ));

        $this->load->view("/inc/header" , (object)array(
            "user"                 => $user
        ));
        $this->load->view("/inc/top");
        $this->load->view("/mypage/wish_list" , (object)array(
            "user"                  => $user,
            "data"                  => $response->data
        ));
        $this->load->view("/inc/footer_js");
        $this->load->view("/inc/footer");
    }

    public function receive(){

        $user = $this->user_model->getUser();
        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "receive_srch_value"                          => $this->input->get('receive_srch_value') ? $this->input->get('receive_srch_value') : '',
        );

        $response = $this->contents_model->getContentsReceiveList((object)array(
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "accountId"                 => $user->eth_account,
            "receive_srch_value"    => $request_params->receive_srch_value
        ));


            $total_rows = $response->data->total_rows;
            $paging = Pagination::makePage("/" . __CLASS__ . '/' . strtolower(str_replace("_", "/", __FUNCTION__) . "/"), ($total_rows ? $total_rows : 0), $request_params->page_size);

            $this->load->view("/inc/header", (object)array(
                "user" => $user
            ));
            $this->load->view("/inc/top");
            $this->load->view("/mypage/receive_list", (object)array(
                "user"                     => $user,
                "data"                     => $response->data,
                "request_params"     =>  $request_params,
                "paging"                  => $paging
            ));
            $this->load->view("/inc/footer_js");
            $this->load->view("/inc/footer");


    }


    public function usage(){
        $user = $this->user_model->getUser();
        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->post('pageNum', true) == "" ? "1" : $this->input->post('pageNum', true),
            "page_size"			                           => $this->input->post('pageSize', true) == "" ? "10" : $this->input->post('pageSize', true),
            "point_type"                                      => $this->input->post('point_type', true) == "" ? "" : $this->input->post('point_type', true),
            "start_date"                                      => $this->input->post('start_date', true) == "" ? "" : $this->input->post('start_date', true),
            "end_date"                                       => $this->input->post('end_date', true) == "" ? "" : $this->input->post('end_date', true),
        );

        $response = $this->contents_model->getContentsUseList((object)array(
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "accountId"                 => $user->eth_account,
            "point_type"                 => $request_params->point_type,
            "start_date"                 => $request_params->start_date,
            "end_date"                 => $request_params->end_date,
        ));

        if($response->code == 200){
            if(count($response->data->rows) > 0) {
                foreach ($response->data->rows as $key =>$val) {
                    $response->data->rows[$key]->point_type_str = ENUM_POINT_TYPE::_print($val->code);
                    $response->data->rows[$key]->regdate = date('Y.m.d', $val->wdate);
                    $response->data->rows[$key]->number_format_point_str =  ($val->point_type == 1 ? '-' : '+') . ' ' . number_format($val->point);
                }
            }
        }

        $add_total = $this->contents_model->getContentsTotal((object)array(
            'accountId'             => $user->eth_account ,
            'point_type'             => '1',
        ));

        $min_total = $this->contents_model->getContentsTotal((object)array(
            'accountId'             => $user->eth_account ,
            'point_type'             => '2',
        ));


        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/" . __CLASS__ . '/' . strtolower(str_replace("_", "/", __FUNCTION__) . "/"), ($total_rows ? $total_rows : 0), $request_params->page_size);

        $this->load->view("/inc/header", (object)array(
            "user" => $user
        ));
        $this->load->view("/inc/top");
        $this->load->view("/mypage/use_list", (object)array(
            "total"                         => array('min_total'=>$min_total , 'add_total'=>$add_total),
            "user"                         => $user,
            "data"                         => $response->data,
            "paging"                      => $paging,
            "request_params"         => $request_params,
        ));
        $this->load->view("/inc/footer_js");
        $this->load->view("/inc/footer");
    }

    public function _usage(){

        $user = $this->user_model->getUser();
        $this->load->library('Pagination');

        $request_params = (object)array(
            "page_num"			                           => $this->input->post('pageNum', true) == "" ? "1" : $this->input->post('pageNum', true),
            "page_size"			                           => $this->input->post('pageSize', true) == "" ? "10" : $this->input->post('pageSize', true),
            "point_type"                                      => $this->input->post('point_type', true) == "" ? "" : $this->input->post('point_type', true),
            "start_date"                                      => $this->input->post('start_date', true) == "" ? "" : $this->input->post('start_date', true),
            "end_date"                                       => $this->input->post('end_date', true) == "" ? "" : $this->input->post('end_date', true),
        );

        $response = $this->contents_model->getContentsUseList((object)array(
            "page_yn"                   => 'Y',
            "page_num"			     => $request_params->page_num,
            "page_size"			     => $request_params->page_size,
            "accountId"                 => $user->eth_account,
            "point_type"                 => $request_params->point_type,
            "start_date"                 => $request_params->start_date,
            "end_date"                 => $request_params->end_date,
        ));

        if($response->code == 200){
            if(count($response->data->rows) > 0) {
                foreach ($response->data->rows as $key =>$val) {
                    $response->data->rows[$key]->point_type_str = ENUM_POINT_TYPE::_print($val->code);
                    $response->data->rows[$key]->regdate = date('Y.m.d', $val->wdate);
                    $response->data->rows[$key]->number_format_point_str =  ($val->point_type == 1 ? '-' : '+') . ' ' . number_format($val->point);
                }
            }
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _contentsReceiveDel(){
        $request_params = (object)array(
            "contents_ids"	=> $this->input->post('contents_ids', true),
        );
        $response = $this->contents_model->setContentsReceiveDel((object)array(
            "contents_ids"    => $request_params->contents_ids
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _zzimDel(){
        $request_params = (object)array(
            "contents_ids"	=> $this->input->post('contents_ids', true),
        );

        $response = $this->contents_model->setContentsZzimDel((object)array(
            "contents_ids"    => $request_params->contents_ids
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