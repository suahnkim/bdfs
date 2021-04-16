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

class auth extends CI_Controller{
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

    public function lists($qre1){

        $method = strtolower(__FUNCTION__)."_".$qre1;
        if(method_exists($this, $method)){
            $this->{$method}();
        }else{
            show_404();
        }
    }

    public function request_list(){
        $user =  $this->session->userdata('AUSER');

        $this->load->view(MC_VIEWS_PATH."/inc/header" , (object)array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/auth/request/list");
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
    }

    public function user_list(){
        $user =  $this->session->userdata('AUSER');
        $this->load->helper('alert');
        $this->load->library('Pagination');

        if($user->user_auth < 9){
            alert();
        }else{

            $request_params = (object)array(
                "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
                "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            );

            $response = $this->user_model->getUserList((object)array(
                "user_auth"         => $user->user_auth,
                "state"                => 1,
                "page_yn"           => 'Y',
                "page_num"         => $request_params->page_num,
                "page_size"         => $request_params->page_size,
            ));


            $total_rows = $response->data->total_rows;
            $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


            $this->load->view(MC_VIEWS_PATH."/inc/header" , (object)array(
                "user"          => $user
            ));
            $this->load->view(MC_VIEWS_PATH."/auth/user/list" , (object)array(
                "data"          => $response->data,
                "paging"     => $paging
            ));
            $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
            $this->load->view(MC_VIEWS_PATH."/inc/footer");
        }


    }

}
/*
		$this->load->library('JWT');
		$token = JWT::encode((object)array("aaa","bbb"), AUTH_TOKEN_KEY);
		$decoded = JWT::decode($token, AUTH_TOKEN_KEY, array('HS256'));
*/
?>