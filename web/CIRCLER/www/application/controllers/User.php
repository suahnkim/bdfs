<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->model('user_model');
	}

	public function _remap($method, $params = array()){
		if($this->input->is_ajax_request()) $method = '_'.$method;
		if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "_check_eth_account" || $method == "_login_view"){
			if($this->user_model->isSignedIn() && $method == "signin" || $method == '_kcaptcha'){
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
		);

		$response = $this->user_model->getEthAccountSingle((object)array(
			"account"			=> $request_params->account,
		));
		if($response->code == "200"){
			$response = $this->user_model->procCreateSignedSession((object)array(
				"user_info_id"			        => $response->data->user_info_id,
				"account"						=> $request_params->account,
				"password"					=> $request_params->password,
				"email"							=> $response->data->email,
                "is_adult"                       => $response->data->is_adult,
			));
		}

		echo json_encode($response);
		http_response_code($response->status);
	}

	private function _signup(){
		$request_params = (object)array(
			"account"		=> $this->input->post('account', true),
			"email"			=> $this->input->post('email', true),
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
					$response = $response = Utils::customizeResponse("200", "997", "인증메일 발송 실패 error-" . $mail->ErrorInfo , (object)array(''));
				}else{
					$response = $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("email" => $request_params->email));
				}
			}
		}
		echo json_encode($response);
		http_response_code($response->status);
	}

    private function _certify(){
	    $user = $this->user_model->getUser();

	    $request_params = (object)array(
	        'captcha_key'   => $this->input->post('captcha_key' , true),
            'user_info_id'    => $user->user_info_id,
        );

	    $certifiy_info = $this->user_model->getCertifySingle((object)array(
	        "user_info_id" => $request_params->user_info_id,
        ));

	    if($certifiy_info->code == 200){
	        if($certifiy_info->data->adult_yn == 'N')  $response = Utils::customizeResponse("200", "999", '승인대기중입니다.', (object)array());
	        else $response = Utils::customizeResponse("200", "999", '이미 인증된 회원입니다..', (object)array());
        }else{
            $result = $this->chk_captcha($request_params->captcha_key);

            if($result){

                $this->load->library('RestApi');
                $this->restapi = new RestApi("http://203.229.154.79:55446");

                $registerid_data = $this->restapi->post("/registerid", array(
                    "data"=> array(),
                ));

				$json = json_decode($registerid_data['data']);
				
                if($json->resultCode == 0){
                    $id = $json->id;
                    $key = $json->key;

                    $identify_data = $this->restapi->post("/addattr", array(
                        "data"=> array(
                            'id'    => $id,
                            'attr'     => 99,
                        ),
                    ));
                    $json2 = json_decode($identify_data['data']);

                    if($json2->resultCode == 0){
                        $certify_response = $this->user_model->postUserCertify((object)array(
                            'user_info_id'  => $request_params->user_info_id,
                            'identify_id'     => $json2->targetId,
                            'attribute'        => $json2->attribute,
                            'attributeId'     => $json2->attributeId,
                            'adult_yn'       => 'N',
                        ));
                        if($certify_response->code == 200){
                            $response =  Utils::customizeResponse("200", "200", "인증요청이 정상적으로 신청되었습니다.", array('certify_id'=>$certify_response->data->certify_id));
                        }else{
                            $response = Utils::customizeResponse("200", "998", $certify_response->message, (object)array());
                        }
                    }else{
                        $response = $response = Utils::customizeResponse("200", "998", "인증계정권한요청에 실패하였습니다.", (object)array());
                    }


                }else{
                    $response = $response = Utils::customizeResponse("200", "998", "인증계정생성에 실패하였습니다.", (object)array());
                }

            }else{
                $response = $response = Utils::customizeResponse("200", "997", "보안숫자가 일치하지 않습니다.", (object)array());
            }
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    public function getCertifyInfo(){

        $request_params = (object)array(
            "start_date"        => $this->input->get('start_date' , true) == "" ? "" : $this->input->get('start_date' , true),
            "end_date"         => $this->input->get('end_date' , true) == "" ? "" : $this->input->get('end_date' , true),
            "search_key"		=> $this->input->get('search_key', true) == "" ? "" : $this->input->get('search_key', true),
            "search_value"	=> $this->input->get('search_value', true) == "" ? "" : $this->input->get('search_value', true),
            "page_num"		=> $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"		=> $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
        );

        $response = $this->user_model->getCertifySearch((object)array(
            'start_date'            => $request_params->start_date,
            'end_date'             => $request_params->end_date,
            'search_key'         => $request_params->search_key,
            'search_value'      => $request_params->search_value,
            'page_num'          => $request_params->page_num,
            'page_size'          => $request_params->page_size,
        ));

        if($response->code == 200){

        }

    }

    // 세션에 저장된 캡챠값과 $_POST 로 넘어온 캡챠값을 비교
    function chk_captcha($captcha_key)
    {
        $captcha_count = (int)$this->session->userdata('ss_captcha_count');
        if ($captcha_count > 5) {
            return false;
        }

        if (!isset($captcha_key)) return false;
        if (!trim($captcha_key)) return false;
        if ($captcha_key != $this->session->userdata('ss_captcha_key')) {
            $_SESSION['ss_captcha_count'] = $captcha_count + 1;
            return false;
        }
        return true;
    }


    public function authIdentity(){
	    $user = $this->user_model->getUser();

        echo shell_exec("/data/web/blockchain/identity/identity.exe registerid");
	    //shell_exec( "../../../identity/identity.exe registerid");

	}

	private function _userVertify(){

	    $user = $this->user_model->getUser();


	    $response = $this->user_model->getCertifySingle((object)array(
	        'user_info_id'      => $user->user_info_id ,
            'del_yn'              => 'N',
        ));



        $this->load->library('RestApi');
        $this->restapi = new RestApi("http://203.229.154.79:55446");

        $verify_data = $this->restapi->post("/verify", array(
            "data"=> array(
                'id'    => $response->data->identify_id,
                'attr'  => $response->data->attribute,
            ),
        ));
        $json = json_decode($verify_data['data']);


        if($json->resultCode != 0){
            $response = $response = Utils::customizeResponse("200", "998", "성인컨텐츠 접근권한이 없습니다.", (object)array());
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