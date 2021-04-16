<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user extends CI_Controller{
	function __construct(){
		parent::__construct();	
		$this->load->model('user_model');
        $this->load->model('contents_model');
	}

	public function _remap($method, $params = array()){

	   		if($this->input->is_ajax_request()) $method = '_'.$method;
		if($method == "signin" || $method == "_signin" || $method == "_signup" || $method == "_check_eth_account" || $method == "_login_view" || $method == "_setManager" || $method == "_getMangerInfo"){
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
		//$this->load->view(COM_VIEWS_PATH."/inc/header");
		$this->load->view(COM_VIEWS_PATH."/login/default");
		$this->load->view(COM_VIEWS_PATH."/inc/footer_js");
		//$this->load->view(COM_VIEWS_PATH."/inc/footer");
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
				"user_info_id"			=> $response->data->user_info_id,
				"account"						=> $request_params->account,
				"password"					=> $request_params->password,
				"email"							=> $response->data->email,
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
					$response = $response = Utils::customizeResponse("200", "997", "인증메일 발송 실패", (object)array());
				}else{
					$response = $response = Utils::customizeResponse("200", "200", "SUCC", (object)array("email" => $request_params->email));
				}
			}
		}
		echo json_encode($response);
		http_response_code($response->status);
	}


	public function _setManager(){
	    $request_params = (object)array(
	        "manager_id"           => $this->input->post('manager_id' , true),
            "manager_pwd"       => $this->input->post('manager_pwd' , true),
        );


	    $response = $this->user_model->postManager((object)array(
	        "manager_id"                     =>  $request_params->manager_id,
	        "manager_pwd"                 =>   Crypt::Encrypt($request_params->manager_pwd),
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }

    private function _getMangerInfo(){
	    $request_params = (object)array(
	        "manager_id"        =>  $this->input->post('manager_id' , true),
            "manager_pwd"     => $this->input->post('manager_pwd' ,true),
        );

        $manager_info = $this->user_model->getManagerSingleInfo((object)array(
	        "manager_id"        => $request_params->manager_id,
            "manager_pwd"     => Crypt::Encrypt($request_params->manager_pwd),
        ));

        if($manager_info->code == "200"){
            $response = $this->user_model->procCreateManagerSession((object)array(
                "manager_info_id"			=> $manager_info->data->manager_info_id,
                "manager_id"					=> $manager_info->data->manager_id,
                "manager_level"				=> $manager_info->data->level,
                "manager_name"             => $manager_info->data->nickname,
                "user_info_id"                 => $manager_info->data->user_info_id,
                "level"                           => $manager_info->data->user_level,
                "email"                          =>  $manager_info->data->email,
            ));
        }else{
            $response = Utils::customizeResponse(200, 998, "일치하는 정보가 없습니다.", null);
        }

        echo json_encode($response);
        http_response_code($response->status);
    }

    public function getUserStatistic(){

	    $request_params = (object)array(
	        "search_key"    =>  $this->input->post('search_key' , true),
            "search_date"    =>  $this->input->post('search_date' , true),
        );

	    $response = $this->user_model->getUserStatisticInfo((object)array(
            "search_key"    =>  $request_params->search_key,
            "search_date"   =>  $request_params->search_date,
        ));

        echo json_encode($response);
        http_response_code($response->status);
    }

    public function lists(){
        $user = $this->user_model->getUser();
        $this->load->library('Pagination');
	    $request_params = (object)array(
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "start_date"                                       => $this->input->get('start_date', true) == "" ? "" : $this->input->get('start_date', true) ,
            "end_date"                                        => $this->input->get('end_date', true) == "" ? "" : $this->input->get('end_date', true) ,
            "email"                                              => $this->input->get('email', true) == "" ? "" : $this->input->get('email', true) ,
            "state"                                              => $this->input->get('state', true) == "" ? 1 : $this->input->get('state', true) ,
        );

	    $response = $this->user_model->getUserSearch((object)array(
	        "start_date"           => $request_params->start_date,
            "end_date"            => $request_params->end_date,
            "state"                  => $request_params->state,
            "email"                  => $request_params->email,
            "page_yn"             => 'Y',
            "page_num"          => $request_params->page_num,
            "page_size"         =>  $request_params->page_size,
        ));

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);

        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                  => $user,
        ));
        $this->load->view(MC_VIEWS_PATH."/user/list" ,array(
            'request_params'          =>  $request_params,
            'data'                          =>  $response->data,
            'paging'                       =>  $paging
        ));
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");

    }

    public function popupInfo(){
        $this->load->library('Pagination');

        $request_params = (object)array(
            'email'                                              => $this->input->get('email' , true) ? $this->input->get('email' , true) : '',
            "page_num"			                           => $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"			                           => $this->input->get('pageSize', true) == "" ? "10" : $this->input->get('pageSize', true),
            "point_type"                                      => $this->input->get('point_type', true) == "" ? "" : $this->input->get('point_type', true),
            "start_date"                                      => $this->input->get('start_date', true) == "" ? "" : $this->input->get('start_date', true),
            "end_date"                                       => $this->input->get('end_date', true) == "" ? "" : $this->input->get('end_date', true),
        );

        $user_info = $this->user_model->getUserSearchEmail((object)array(
            'email'                 =>  $request_params->email
        ));



       if($user_info->code == 200) {

           $contents_info = $this->contents_model->getContentsUseList((object)array(
               "page_yn"    => 'Y',
               "page_num" => $request_params->page_num,
               "page_size" => $request_params->page_size,
               "accountId"  => $user_info->data->account,
               "point_type"  => $request_params->point_type,
               "start_date"  => $request_params->start_date,
               "end_date"   => $request_params->end_date,
           ));

       }

        if(@$contents_info->code == 200){
            if(count(@$contents_info->data->rows) > 0) {
                foreach ($contents_info->data->rows as $key =>$val) {
                    $contents_info->data->rows[$key]->point_type_str = ENUM_POINT_TYPE::_print($val->code);
                    $contents_info->data->rows[$key]->regdate = date('Y.m.d', $val->wdate);
                    $contents_info->data->rows[$key]->number_format_point_str =  ($val->point_type == 1 ? '-' : '+') . ' ' . number_format($val->point);
                }
            }
        }

        $add_total = $this->contents_model->getContentsTotal((object)array(
            'accountId'             => @$user_info->data->account ,
            'point_type'             => '1',
        ));

        $min_total = $this->contents_model->getContentsTotal((object)array(
            'accountId'             => @$user_info->data->account ,
            'point_type'             => '2',
        ));

        $total_rows = @$contents_info->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);


        $this->load->view(MC_VIEWS_PATH."/user/popup" ,array(
            "request_params"        => $request_params,
            "user"                        => @$user_info->data,
            "data"                        =>  @$contents_info->data,
            "paging"                      => @$paging,
            "total"                         => array('min_total'=>$min_total , 'add_total'=>$add_total),
        ));
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");

    }

    public function certify(){
	    $user = $this->user_model->getUser();
        $this->load->library('Pagination');
        $request_params = (object)array(
            "start_date"        => $this->input->get('start_date' , true) == "" ? "" : $this->input->get('start_date' , true),
            "end_date"         => $this->input->get('end_date' , true) == "" ? "" : $this->input->get('end_date' , true),
            "search_key"		=> $this->input->get('search_key', true) == "" ? "" : $this->input->get('search_key', true),
            "search_value"	=> $this->input->get('search_value', true) == "" ? "" : $this->input->get('search_value', true),
            "page_num"		=> $this->input->get('pageNum', true) == "" ? "1" : $this->input->get('pageNum', true),
            "page_size"		=> $this->input->get('pageSize', true) == "" ? "20" : $this->input->get('pageSize', true),
            "email"              => $this->input->get('email', true) == "" ? "" : $this->input->get('email', true),
            "adult_yn"          => $this->input->get('adult_yn', true) == "" ? "" : $this->input->get('adult_yn', true),
        );

        $response = $this->user_model->getCertifySearch((object)array(
            'start_date'            => $request_params->start_date,
            'end_date'             => $request_params->end_date,
            'search_key'         => $request_params->search_key,
            'search_value'      => $request_params->search_value,
            'page_num'          => $request_params->page_num,
            'page_size'          => $request_params->page_size,
            'adult_yn'             => $request_params->adult_yn,
            'email'                 => $request_params->email,
            'page_yn'            => 'Y',
        ));

        $total_rows = $response->data->total_rows;
        $paging = Pagination::makePage("/".__CLASS__.'/'.strtolower(str_replace("_", "/", __FUNCTION__)."/"), ($total_rows ? $total_rows : 0), $request_params->page_size);

        $this->load->view(COM_VIEWS_PATH."/inc/header" , array(
            'user'                  => $user,
        ));
        $this->load->view(MC_VIEWS_PATH."/user/certify" ,array(
            'request_params'          =>  $request_params,
            'data'                          =>  $response->data,
            'paging'                       =>  $paging
        ));
        $this->load->view(COM_VIEWS_PATH."/inc/footer_js");
        $this->load->view(COM_VIEWS_PATH."/inc/footer");

    }

    public function _setCertify(){
	    $request_params = (object)array(
	       'user_info_ids'       => $this->input->post('user_info_ids' , true),
           'certify_ids'            => $this->input->post('certify_ids' , true),
           'adult_yn'              => $this->input->post('adult_yn' , true),
        );

	    $response = $this->user_model->modCertityInfo((object)array(
	        'user_info_ids'         => $request_params->user_info_ids ,
            'certify_ids'              => $request_params->certify_ids,
            'adult_yn'                => $request_params->adult_yn,
        ));
        echo json_encode($response);
        http_response_code($response->status);
    }

    public function _setSingleCertify(){
        $request_params = (object)array(
            'user_info_id'       => $this->input->post('user_info_id' , true),
            'certify_id'            => $this->input->post('certify_id' , true),
            'adult_yn'              => $this->input->post('adult_yn' , true),
            'identify_id'           => $this->input->post('identify_id' , true),
            'attributeId'           => $this->input->post('attributeId' , true),
        );


        if($request_params->adult_yn == 'Y'){
            $this->load->library('RestApi');
            $this->restapi = new RestApi("http://203.229.154.79:55446");

            $approve_data = $this->restapi->post("/approve", array(
                "data"=> array(
                    'id'        => $request_params->identify_id,
                    'attrId'   => $request_params->attributeId
                ),
            ));
            $json = json_decode($approve_data['data']);
            if($json->resultCode != 0){
                $response = Utils::customizeResponse(200, 998, "사용권한 승인에 실패하였습니다.", null);
                echo json_encode($response);
                http_response_code($response->status);
                exit;
            }
        }

        $response = $this->user_model->modSingleCertityInfo((object)array(
            'user_info_id'         => $request_params->user_info_id ,
            'certify_id'             => $request_params->certify_id,
            'adult_yn'              => $request_params->adult_yn,
        ));



        echo json_encode($response);
        http_response_code($response->status);
    }


}
?>