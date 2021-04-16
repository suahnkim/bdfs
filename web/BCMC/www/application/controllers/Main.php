<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('user_model');
	}

	public function _remap($method, $params = array()){
		if( $method !== 'programDownload' ) {
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
		} else {
			if(method_exists($this, $method)){
				call_user_func_array(array($this, $method), $params);
			}
		}
	}

	public function index(){
        $user =  $this->session->userdata('AUSER');

        switch($user->user_auth){
            case '1' : $url = "/contents/lists/P"; break;
            case '3' : $url = "/contents/lists_api_D"; break;
            case '9' : $url = "/auth/request_list"; break;
            default : $url = '/user/logout'; break;
        }
        header('Location: '.$url);
        exit;



        $this->load->view(MC_VIEWS_PATH."/inc/header" , array(
            "user"          => $user
        ));
        $this->load->view(MC_VIEWS_PATH."/index/default");
        $this->load->view(MC_VIEWS_PATH."/inc/footer_js");
        $this->load->view(MC_VIEWS_PATH."/inc/footer");
	}

	public function programDownload(){
		$filename = 'MediaBlockChain.zip';
		$file_url = stripslashes(trim($_SERVER['DOCUMENT_ROOT'] . COM_ASSETS_PATH . '/' . $filename));

		header("Expires: 0");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
        header("Pragma: no-cache");
        header("Content-type: application/zip");
        header("Content-Disposition:attachment; filename=" . $filename);
        header("Content-Type: application/force-download");

        readfile($file_url);
        exit();
	}
}
?>