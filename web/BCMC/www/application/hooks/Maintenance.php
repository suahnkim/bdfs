<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Maintenance
{
	function __construct(){
		parent::__construct();
		
		$this->lang->load('common', LANG_TYPE);
	}

   var $CI;    
   public function index()
   {
		// 정기점검시간설정
		$_error =& load_class('Exceptions', 'core');
		//echo $_error->show_error("", "", 'error_maintenance', 200);
		echo $_error->show_error($this->lang->line('lang_10021'), $this->lang->line('lang_10022')); //"점검 중 입니다." / "잠시 후에 다시 시도해주세요."
		exit;
   }
}