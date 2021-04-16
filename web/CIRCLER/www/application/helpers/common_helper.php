<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alert 띄우기
 */
if ( ! function_exists('getFileSizeStr')) {
    function getFileSizeStr($size){
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
    }

}

if ( ! function_exists('get_datetime')) {
    function get_datetime($date, $type='m.d') {

        $diff = time() - $date;

        $s = 60; //1분 = 60초
        $h = $s * 60; //1시간 = 60분
        $d = $h * 24; //1일 = 24시간
        $y = $d * 10; //1년 = 1일 * 10일

        if ($diff < $s) {
            $time = $diff."초전";
        } else if ($h > $diff && $diff >= $s) {
            $time = round($diff/$s)."분전";
        } else if ($d > $diff && $diff >= $h) {
            $time = round($diff/$h)."시간전";
        } else if ($y > $diff && $diff >= $d) {
            $time = round($diff/$d)."일전";
        } else {
            $time = date($type, $date);
        }

        return $time;
    }
}
if ( ! function_exists('get_dayhours')) {
    function get_dayhours($_time)
    {
        $cur_time = time();

        if($_time > $cur_time){

            $remain_time = $_time - $cur_time;

            $days = floor($remain_time / (60 * 60 * 24));
            $time = $remain_time - ($days * (60 * 60 * 24));
            $hours = floor($time / (60 * 60));
            $min = floor( $time / (60 * 60 * $hours));

            $expire_date = $days ? $days . '일' : '';
            $expire_date .= $hours ? $hours . '시간' : '';
            //$expire_date .= $min ? $min . '분' : '';


            return $expire_date;
        }else{
            return '기간만료';
        }
    }
}
?>