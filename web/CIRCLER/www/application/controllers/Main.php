<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('user_model');
        $this->load->model('contents_model');
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

	    $user = $this->user_model->getUser();

        $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
        );

        $this->load->model('contents_model', 'popular_contents_model');
		$this->load->model('contents_model', 'recommand_contents_model');
		$this->load->model('contents_model', 'recent_contents_model');

        //$popular_response = $this->contents_model->getContentsPopularTop((object)array(
        //    "limit"                         => 10,
		//));

        //$recommand_response = $this->contents_model->getContentsRecommandTop((object)array(
        //    "limit"                         => 10,
		//));

		//$recent_response = $this->contents_model->getContentsTop((object)array(
        //    "limit"                         => 18,
		//));

		$popular_response = $this->popular_contents_model->getContentsPopularTop((object)array("limit" => 10));
		
		$recommand_response = $this->recommand_contents_model->getContentsRecommandTop((object)array("limit" => 10));

		$recent_response = $this->recent_contents_model->getContentsTop((object)array("limit" => 10));

		$this->load->view("/inc/header" , (object)array(
		    "user" => $user
        ));
		$this->load->view("/inc/top");
		$this->load->view("/index/default" , (object)array(
		    "recent_data"       => $recent_response->data,
            "recommand_data"    => $recommand_response->data,
			"popular_data"      => $popular_response->data,
            "user"              => $user
        ));
		$this->load->view("/inc/footer_js");
		$this->load->view("/inc/footer");
	}

	public function programDownload(){
		$filename = 'MediaBlockChain.zip';
		$file_url = stripslashes(trim($_SERVER['DOCUMENT_ROOT'] . MC_ASSETS_PATH . '/' . $filename));

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