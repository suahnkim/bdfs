<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EnumHook{
   public function initialize(){
		 require_once(APPPATH.'config/enums.php');
   }
}
?>