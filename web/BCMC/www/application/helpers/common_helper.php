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
?>