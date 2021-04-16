<?php

class Boardinfo {
    var $ci;
    var $boardlist = array();

    public function __construct() {
        $this->ci = & get_instance();
        date_default_timezone_set('Asia/Seoul');

        $this->getBoardInfoList();

    }

    private function  getBoardInfoList(){

        $this->conn = new MysqlConnection();
        $this->conn->query('select bbs_id , bbs_name  from BOARD_INFO  where state = "0"');
        $rows = $this->conn->fetchAll('object');


        $this->boardlist = $rows;
    }
}

?>