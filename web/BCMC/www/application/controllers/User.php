<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user extends CI_Controller{
	function __construct(){
		parent::__construct();	
		$this->load->model('user_model');
	}

	public function _remap($method, $params = array()){
		if($this->input->is_ajax_request()) $method = '_'.$method;
		if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "signup" || $method == "generate" || $method == "_check_eth_account" || $method == "_login_view"  || $method == '_vertify' || $method == '_putUser'){
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
		$this->load->view(COM_VIEWS_PATH."/inc/header");
		$this->load->view(COM_VIEWS_PATH."/login/default");
		$this->load->view(COM_VIEWS_PATH."/inc/footer_js");
		$this->load->view(COM_VIEWS_PATH."/inc/footer");
	}

    public function signup(){
        $this->load->view(COM_VIEWS_PATH."/inc/header");
        $this->load->view(COM_VIEWS_PATH."/login/join");
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

    public function generate(){
        $this->load->view(COM_VIEWS_PATH."/inc/header");
        $this->load->view(COM_VIEWS_PATH."/join/generate");
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");
    }

	public function logout(){
		$this->user_model->doSignOut();
		header("Location: /");
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

	private function _login_view($fname){
		$request_params = (object)array(
			"user_info_id"		=> $this->input->post("user_info_id", true),
			"account"					=> $this->input->post("account", true),
			"email"						=> $this->input->post("email", true),
		);
		$this->load->view(MC_VIEWS_PATH."/login/".$fname, array(
			"request_params"		=> $request_params,
		));
	}

	private function _signin(){
		$request_params = (object)array(
			"account"		=> $this->input->post('account', true),
			"password"	=> $this->input->post('password', true),
            "role"	=> $this->input->post('role', true),
		);

        $response = $this->user_model->procCreateSignedSession((object)array(
            "account"						=> $request_params->account,
            "password"					=> $request_params->password,
            "role"                            => $request_params->role,
        ));


		echo json_encode($response);
		http_response_code($response->status);
	}

	private function _signup(){
		$request_params = (object)array(
			"account"		=> $this->input->post('account_id', true),
			"join_type"			=> $this->input->post('account_type', true),
			"password"	=> $this->input->post('password', true),
		);

		$response = $this->user_model->chkSignEmail((object)array(
			"email"					=> $request_params->email,
		));

		if($response->code == "200"){
			$response = $this->user_model->putSignInforDirect((object)array(
				"state"					=> 0,
				"email"					=> $request_params->email,
				"create_ip"			=> $_SERVER["REMOTE_ADDR"],
			));

			if($response->code == "200"){
				$this->load->library('JWT');
				$this->load->library('PHPMailer');

				$token = JWT::encode((object)array("user_info_id" => $response->data->user_info_id, "account" => $request_params->account, "password" => $request_params->password, "email" => $request_params->email, "expire_datetime" => date("Y-m-d H:i:s", mktime(date("H"), date("i") + 30, date("s"), date("m"), date("d"), date("Y")))), AUTH_TOKEN_KEY);

				$mail = new PHPMailer(true);
				$mail->IsSMTP();

				$mail->ContentType = MAIL_CONTENT_TYPE;
				$mail->Charset = MAIL_CHARSET;
				$mail->Host = MAIL_HOST;
				$mail->Port = MAIL_PORT;
				$mail->SMTPAuth = MAIL_SMTPAuth;
				$mail->SMTPSecure = MAIL_SMTPSecure;
				$mail->Username = MAIL_AUTH_ID;
				$mail->Password = MAIL_AUTH_PW;
				
				$mail->setFrom('sender@cicler.co.kr', '써클러 인증팀');
				$mail->addAddress($request_params->email);
				$mail->Subject = '가입 인증 메일입니다.';

				$mail_contents_html = file_get_contents(APPPATH."/views/".MC_VIEWS_PATH.'/email/email_auth.php');
				$mail_contents_html = str_replace("[LINK]", "http://www.circler.co.kr/auth/email?token=".$token, $mail_contents_html);
				$mail_contents_html = str_replace("[IMAGE_DOMAIN]", "http://www.circler.co.kr/assets/default", $mail_contents_html);
				$mail_contents_html = str_replace("[SITE_NAME]", SITE_NAME, $mail_contents_html);

				$mail->msgHTML($mail_contents_html);
				if(!$mail->send()){ 
					$response = $response = Utils::customizeResponse("200", "997", "인증메일 발송 실패", (object)array());
				}else{
					$response = $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("email" => $request_params->email));
				}
			}
		}
		echo json_encode($response);
		http_response_code($response->status);
	}


	private function _vertify(){
        $request_params = (object)array(
            "account"		=> $this->input->post('account_id', true),
            "join_type"		=> $this->input->post('join_type', true),
        );

        $user = $this->user_model->getUserVertify((object)array(
            'account'       => $request_params->account ,
            'join_type'     => $request_params->join_type,
        ));

        if($user->code == 200){
            $response = Utils::customizeResponse("200", "998", "이미 신청중이거나 심사중입니다.", (object)array());
        }else{
             $response = Utils::customizeResponse("200", "200", "SUCCESS", (object)array());
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function  _putUser(){
        $this->load->library('Crypt');

	    $request_params = (object)array(
	        "account"                                      =>      $this->input->post('account_id' , true),
            "join_type"                                     =>      $this->input->post('account_type' , true),
            "password_hash"                           =>      $this->input->post('ethereum_password' , true),
            "company_cname"                          =>      $this->input->post('company_cname' , true) ? $this->input->post('company_cname' , true) : '',
            "company_name"                            =>      $this->input->post('company_name' , true) ? $this->input->post('company_name' , true) : '',
            "company_number"                         =>      $this->input->post('company_number' , true) ? $this->input->post('company_number' , true) : '',
            "company_type"                              =>      $this->input->post('company_type' , true) ? $this->input->post('company_type' , true) : '',
            "company_kind"                              =>      $this->input->post('company_kind' , true) ? $this->input->post('company_kind' , true) : '',
            "company_addr"                             =>      $this->input->post('company_addr' , true) ? $this->input->post('company_addr' , true) : '',
            "company_com_number"                  =>      $this->input->post('company_com_number' , true) ? $this->input->post('company_com_number' , true) : '',
            "site_name"                                    =>      $this->input->post('site_name' , true) ? $this->input->post('site_name' , true) : '',
            "site_url"                                        =>      $this->input->post('site_url' , true) ? $this->input->post('site_url' , true) : '',
            "manage_name"                              =>      $this->input->post('manage_name' , true) ? $this->input->post('manage_name' , true) : '',
            "manage_email"                              =>      $this->input->post('manage_email' , true) ? $this->input->post('manage_email' , true) : '',
            "manage_hp"                                 =>      $this->input->post('manage_hp' , true) ? $this->input->post('manage_hp' , true) : '',
            "manage_fax"                                 =>      $this->input->post('manage_fax' , true) ? $this->input->post('manage_fax' , true) : '',
        );

	    $response = $this->user_model->putUserData((object)array(
            "account"                                      =>      $request_params->account,
            "join_type"                                     =>      $request_params->join_type,
            "password_hash"                           =>      Crypt::Encrypt($request_params->password_hash),
            "company_cname"                          =>      $request_params->company_cname,
            "company_name"                            =>      $request_params->company_name,
            "company_number"                         =>      $request_params->company_number,
            "company_type"                              =>      $request_params->company_type,
            "company_kind"                              =>      $request_params->company_kind,
            "company_addr"                             =>      $request_params->company_addr,
            "company_com_number"                  =>      $request_params->company_com_number,
            "site_name"                                    =>      $request_params->site_name,
            "site_url"                                        =>      $request_params->site_url,
            "manage_name"                              =>      $request_params->manage_name,
            "manage_email"                              =>      $request_params->manage_email,
            "manage_hp"                                 =>      $request_params->manage_hp,
            "manage_fax"                                 =>      $request_params->manage_fax,
        ));

        if($response->code == 200){
            $response = Utils::customizeResponse("200", "200", "SUCCESS", $response->data->user_info_id);
        }else{
            $response = Utils::customizeResponse("200", "999", "FAIL", (object)array());
        }

        echo json_encode($response);
        http_response_code($response->status);

    }


    private function userAuthCommit(){
        $user =  $this->session->userdata('AUSER');
        $this->load->helper('alert');
        if($user->user_auth < 9){
            $response = Utils::customizeResponse("999", "999", '권한이 없습니다.', "");
        }else{
            $request_params = (object)array(
                "userAccounts"		=> $this->input->post('userAccounts', true),
            );

            $response = $this->user_model->modeUserAuthCommit((object)array(
                "userAccounts"          =>  $request_params->userAccounts,
                "state"                       => 1,
            ));
        }

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