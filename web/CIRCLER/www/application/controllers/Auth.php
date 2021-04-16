<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class auth extends CI_Controller{
	function __construct(){
		parent::__construct();	
		$this->load->model('user_model');
	}

	public function _remap($method, $params = array()){
		if($this->input->is_ajax_request()) $method = '_'.$method;
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

	public function email(){
		$request_params = (object)array(
			"token"				=> $this->input->get('token', true),
		);

		$this->load->library('JWT');

		$token = JWT::decode($request_params->token, AUTH_TOKEN_KEY, array('HS256'));

		if($token->code == "200"){
			$this->load->view(MC_VIEWS_PATH."/inc/header");
			$this->load->view(MC_VIEWS_PATH."/auth/email", array(
				"request_params"	=> $request_params,
				"token"						=> $token->data,
			));
			$this->load->view(MC_VIEWS_PATH."/inc/footer_js");
			$this->load->view(MC_VIEWS_PATH."/inc/footer");
		}else{
			show_error($token->message);
		}
	}

	public function _auth_view($fname){
		$request_params = (object)array(
			"account"					=> $this->input->post("account", true),
			"email"						=> $this->input->post("email", true),
		);

		$this->load->view(MC_VIEWS_PATH."/auth/".$fname, array(
			"request_params"		=> $request_params,
		));
	}

	private function _result(){
		$request_params = (object)array(
			"token"				=> $this->input->post('token', true),
			"account"			=> $this->input->post('account', true),
			"password"		=> $this->input->post('password', true),
		);

		$this->load->library('JWT');

		$token = JWT::decode($request_params->token, AUTH_TOKEN_KEY, array('HS256'));

		if($token->code == "200"){
			$token->data->account = $request_params->account;
			$token->data->password = $request_params->password;
			
			$response = $this->user_model->getUserInfoSingle((object)array(
				"user_info_id"			=> $token->data->user_info_id,
			));

			if($response->code == "200"){
				switch($response->data->state){
					case "0":
						$response = $this->user_model->postAuthResult((object)array(
							"account"						=> $token->data->account,
							"password"					=> $token->data->password,
							"user_info_id"			=> $token->data->user_info_id,
						));

						if($response->code == "200"){
							$response = Utils::customizeResponse("200", "200", "SUCC", (object)array("email" => $token->data->email, "account" => $token->data->account));
						}
						break;
					case "1":
						$response = $response = Utils::customizeResponse("200", "200", "기 처리된 계정입니다.", (object)array("email" => $token->data->email, "account" => $token->data->account));
						break;
					default:
						$response = $response = Utils::customizeResponse("200", "995", "확인되지 않는 계정입니다.", (object)array());
						break;
				}
			}
		}else{
			$response = $token;
		}

		echo json_encode($response);
		http_response_code($response->status);
	}
}
?>