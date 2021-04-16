<?php

function make_mp3()
{

    global $config;

	$cf_captcha_mp3 = "basic";
    $number = get_session("ss_captcha_key");

    if ($number == "") return;
    if ($number == get_session("ss_captcha_save")) return;

    $mp3s = array();
    for($i=0;$i<strlen($number);$i++){
        $file = dirname(__FILE__). '/mp3/'.$cf_captcha_mp3.'/'.$number[$i].'.mp3';
        $mp3s[] = $file;
    }

    $ip = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
    $mp3_file = 'data/cache/kcaptcha-'.$ip.'_'.time().'.mp3';

    $contents = '';
    foreach ($mp3s as $mp3) {
        $contents .= file_get_contents($mp3);
    }

    file_put_contents(_DR .'/'.$mp3_file, $contents);

    // 지난 캡챠 파일 삭제
    if (rand(0,99) == 0) {
        foreach (glob(_DR .'/data/cache/kcaptcha-*.mp3') as $file) {
            if (filemtime($file) + 86400 < time()) {
                @unlink($file);
            }
        }
    }

    $userapi->set_session("ss_captcha_save", $number);

    return  '/'.$mp3_file;
}

echo make_mp3();
?>