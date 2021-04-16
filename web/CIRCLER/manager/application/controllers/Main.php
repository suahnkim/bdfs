<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('contents_model');
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
            "search_date"       =>       '2019-09-18',
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

	public function test(){
        $this->load->view(MC_VIEWS_PATH."/index/test");
    }
}
?>