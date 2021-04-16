<?php
defined('BASEPATH') OR exit('No direct script access allowed');
# KCAPTCHA PROJECT VERSION 1.2.6

# Automatic test to tell computers and humans apart

# Copyright by Kruglov Sergei, 2006, 2007, 2008
# www.captcha.ru, www.kruglov.ru

# System requirements: PHP 4.0.6+ w/ GD

# KCAPTCHA is a free software. You can freely use it for building own site or software.
# If you use this software as a part of own sofware, you must leave copyright notices intact or add KCAPTCHA copyright notices to own.
# As a default configuration, KCAPTCHA has a small credits text at bottom of CAPTCHA image.
# You can remove it, but I would be pleased if you left it. ;)

# See kcaptcha_config.php for customization


class Kcaptcha extends CI_Controller{

    public $config;

    public function __construct()
    {
        parent::__construct();
        $params = (object)array();
        $this->_config($params);
    }

    private  function _config($params){

        $params->symbol_index = @$params->symbol_index ? @$params->symbol_index : 0;
        $params->length = @$params->length ? @$params->length : 6;
        $params->width =  @$params->width ? @$params->width : 198;
        $params->height = @$params->height ? @$params->height : 72;
        $params->fluctuation_amplitude = @$params->fluctuation_amplitude ? @$params->fluctuation_amplitude : 5;

        $allowed_symbols_arr = array(
            '0123456789',
            '0123456789abcdef',
            'abcdeghkmnpqsuvxyz',
            '23456789abcdeghkmnpqsuvxyz',
        );
        $this->config = (object)array(
            'alphabet'               => '0123456789abcdefghijklmnopqrstuvwxyz',
            'allowed_symbols'   => $allowed_symbols_arr[$params->symbol_index],
            'fontsdir'                 => 'fonts',
            'length'                   => $params->length,
            'width'                    => $params->width,
            'height'                   => $params->height,
            'fluctuation_amplitude'     => 5 , // 5 or 11
            'white_noise_density'      => 1/6 , //  0 or 1/6
            'black_noise_density'      => 1/20, // 0 or 1/20
            'no_spaces'                   => false,
            'show_credits'                => false,
            'credits'                         => 'www.captcha.ru',
            'foreground_color'           => array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100)) , //array(0, 0, 0), //array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
            'background_color'          => array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255)),//array(255, 255, 255), //array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255))
            'jpeg_quality'                  => 90,
            'wave'                           => true,
        );
    }
    // generates keystring and image
    function image(){

        $fonts=array();
        $fontsdir_absolute=dirname(__FILE__).'/../../assets/kcaptcha/'.$this->config->fontsdir;
        if ($handle = opendir($fontsdir_absolute)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/\.png$/i', $file)) {
                    $fonts[]=$fontsdir_absolute.'/'.$file;
                }
            }
            closedir($handle);
        }

        $alphabet_length=strlen($this->config->alphabet);


        $font_file=$fonts[mt_rand(0, count($fonts)-1)];
        $font=imagecreatefrompng($font_file);
        imagealphablending($font, true);
        $fontfile_width=imagesx($font);
        $fontfile_height=imagesy($font)-1;
        $font_metrics=array();
        $symbol=0;
        $reading_symbol=false;

        // loading font
        for($i=0;$i<$fontfile_width && $symbol<$alphabet_length;$i++){
            $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

            if(!$reading_symbol && !$transparent){
                $font_metrics[$this->config->alphabet{$symbol}]=array('start'=>$i);
                $reading_symbol=true;
                continue;
            }

            if($reading_symbol && $transparent){
                $font_metrics[$this->config->alphabet{$symbol}]['end']=$i;
                $reading_symbol=false;
                $symbol++;
                continue;
            }
        }

        $img=imagecreatetruecolor($this->config->width, $this->config->height);
        imagealphablending($img, true);
        $white=imagecolorallocate($img, 255, 255, 255);
        $black=imagecolorallocate($img, 0, 0, 0);

        imagefilledrectangle($img, 0, 0, $this->config->width-1, $this->config->height-1, $white);

        // draw text
        $x=1;
        $odd=mt_rand(0,1);
        if($odd==0) $odd=-1;
        for($i=0;$i<$this->config->length;$i++){
            $m=$font_metrics[$this->keystring{$i}];

            $y=(($i%2)*$this->config->fluctuation_amplitude - $this->config->fluctuation_amplitude/2)*$odd
                + mt_rand(-round($this->config->fluctuation_amplitude/3), round($this->config->fluctuation_amplitude/3))
                + ($this->config->height-$fontfile_height)/2;

            if($this->config->no_spaces){
                $shift=0;
                if($i>0){
                    $shift=10000;
                    for($sy=3;$sy<$fontfile_height-10;$sy+=1){
                        for($sx=$m['start']-1;$sx<$m['end'];$sx+=1){
                            $rgb=imagecolorat($font, $sx, $sy);
                            $opacity=$rgb>>24;
                            if($opacity<127){
                                $left=$sx-$m['start']+$x;
                                $py=$sy+$y;
                                if($py>$this->config->height) break;
                                for($px=min($left,$this->config->width-1);$px>$left-200 && $px>=0;$px-=1){
                                    $color=imagecolorat($img, $px, $py) & 0xff;
                                    if($color+$opacity<170){ // 170 - threshold
                                        if($shift>$left-$px){
                                            $shift=$left-$px;
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                    if($shift==10000){
                        $shift=mt_rand(4,6);
                    }

                }
            }else{
                $shift=1;
            }
            imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfile_height);
            $x+=$m['end']-$m['start']-$shift;
        }

        //noise
        $white=imagecolorallocate($font, 255, 255, 255);
        $black=imagecolorallocate($font, 0, 0, 0);
        for($i=0;$i<(($this->config->height-30)*$x)*$this->config->white_noise_density;$i++){
            imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $this->config->height-15), $white);
        }
        for($i=0;$i<(($this->config->height-30)*$x)*$this->config->black_noise_density;$i++){
            imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $this->config->height-15), $black);
        }

        $center=$x/2;

        // credits. To remove, see configuration file
        $img2=imagecreatetruecolor($this->config->width, $this->config->height+($this->config->show_credits?12:0));
        $foreground=imagecolorallocate($img2, $this->config->foreground_color[0], $this->config->foreground_color[1], $this->config->foreground_color[2]);
        $background=imagecolorallocate($img2, $this->config->background_color[0], $this->config->background_color[1], $this->config->background_color[2]);
        imagefilledrectangle($img2, 0, 0, $this->config->width-1, $this->config->height-1, $background);
        imagefilledrectangle($img2, 0, $this->config->height, $this->config->width-1, $this->config->height+12, $foreground);
        $credits=empty($credits)?$_SERVER['HTTP_HOST']:$credits;
        imagestring($img2, 2, $this->config->width/2-imagefontwidth(2)*strlen($credits)/2, $this->config->height-2, $credits, $background);

        // periods
        $rand1=mt_rand(750000,1200000)/10000000;
        $rand2=mt_rand(750000,1200000)/10000000;
        $rand3=mt_rand(750000,1200000)/10000000;
        $rand4=mt_rand(750000,1200000)/10000000;
        // phases
        $rand5=mt_rand(0,31415926)/10000000;
        $rand6=mt_rand(0,31415926)/10000000;
        $rand7=mt_rand(0,31415926)/10000000;
        $rand8=mt_rand(0,31415926)/10000000;
        // amplitudes
        $rand9=mt_rand(330,420)/110;
        $rand10=mt_rand(330,450)/110;

        //wave distortion

        for($x=0;$x<$this->config->width;$x++){
            for($y=0;$y<$this->config->height;$y++){
                if ($this->config->wave) {
                    $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$this->config->width/2+$center+1;
                    $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;
                }
                else {
                    $sx=$x-$this->config->width/2+$center+1;
                    $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*1.5;
                }

                if($sx<0 || $sy<0 || $sx>=$this->config->width-1 || $sy>=$this->config->height-1){
                    continue;
                }else{
                    $color=imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
                    $color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
                    $color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
                }

                if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
                    continue;
                }else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
                    $newred=$this->config->foreground_color[0];
                    $newgreen=$this->config->foreground_color[1];
                    $newblue=$this->config->foreground_color[2];
                }else{
                    $frsx=$sx-floor($sx);
                    $frsy=$sy-floor($sy);
                    $frsx1=1-$frsx;
                    $frsy1=1-$frsy;

                    $newcolor=(
                        $color*$frsx1*$frsy1+
                        $color_x*$frsx*$frsy1+
                        $color_y*$frsx1*$frsy+
                        $color_xy*$frsx*$frsy);

                    if($newcolor>255) $newcolor=255;
                    $newcolor=$newcolor/255;
                    $newcolor0=1-$newcolor;

                    $newred=$newcolor0*$this->config->foreground_color[0]+$newcolor*$this->config->background_color[0];
                    $newgreen=$newcolor0*$this->config->foreground_color[1]+$newcolor*$this->config->background_color[1];
                    $newblue=$newcolor0*$this->config->foreground_color[2]+$newcolor*$this->config->background_color[2];
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
            }
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');

        if(function_exists("imagejpeg")){
            header("Content-Type: image/jpeg");
            imagejpeg($img2, null, $this->config->jpeg_quality);
        }else if(function_exists("imagegif")){
            header("Content-Type: image/gif");
            imagegif($img2);
        }else if(function_exists("imagepng")){
            header("Content-Type: image/x-png");
            imagepng($img2);
        }
    }

    // returns keystring
    function getKeyString(){
        return $this->keystring;
    }

    function setKeyString($str){

        //echo "<script>alert('$str')</script>";
        //exit;

        $this->keystring = $str;
    }

    function get_session($session_name)
    {
        return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
    }

    function set_session($session_name ,  $_session_value){
        $$session_name = $_SESSION[$session_name] = $_session_value;
    }

    public  function kcaptcha_image(){
        $this->setKeyString($this->get_session('ss_captcha_key'));
        $this->getKeyString();
        $this->image();
    }

    public function kcaptcha_mp3(){
       $this->make_mp3();
    }

    public function make_mp3(){


        $cf_captcha_mp3 = "basic";
        $number = $this->get_session("ss_captcha_key");

        if ($number == "") return;
        if ($number == $this->get_session("ss_captcha_save")) return;

        $mp3s = array();
        for($i=0;$i<strlen($number);$i++){
            $file = $_SERVER['DOCUMENT_ROOT'].'/assets/kcaptcha/mp3/'.$cf_captcha_mp3.'/'.$number[$i].'.mp3';
            $mp3s[] = $file;
        }

        $ip = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
        $mp3_file = 'data/cache/kcaptcha-'.$ip.'_'.time().'.mp3';

        $contents = '';
        foreach ($mp3s as $mp3) {
            $contents .= file_get_contents($mp3);
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] .'/'.$mp3_file, $contents);

        // 지난 캡챠 파일 삭제
        if (rand(0,99) == 0) {
            foreach (glob($_SERVER['DOCUMENT_ROOT'] .'/data/cache/kcaptcha-*.mp3') as $file) {
                if (filemtime($file) + 86400 < time()) {
                    @unlink($file);
                }
            }
        }

        $this->set_session("ss_captcha_save", $number);

        return  '/'.$mp3_file;
    }

    public function kcaptcha_result(){

        $count = $this->get_session("ss_captcha_count");
        if ($count >= 5) { // 설정값 이상이면 자동등록방지 입력 문자가 맞아도 오류 처리
            $result = false;
        } else {
            $this->set_session("ss_captcha_count", $count + 1);
            $result = ($this->get_session("ss_captcha_key") == $this->input->post('captcha_key' , true)) ? true : false;
        }

        echo  $result;
        exit;
    }

    public function kcaptcha_session(){
        while(true){
            $this->keystring ='';
            for($i=0;$i<$this->config->length;$i++){
                $this->keystring.=$this->config->allowed_symbols{mt_rand(0,strlen($this->config->allowed_symbols)-1)};
            }
            if(!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->keystring)) break;
        }

        $this->set_session("ss_captcha_count", 0);
        $this->set_session("ss_captcha_key", $this->keystring);
        $this->setKeyString($this->get_session('ss_captcha_key'));
    }

    // 캡챠 HTML 코드 출력
    function captcha_html($class="captcha")
    {

        /*$userapi = Loader::getInstance('USER_APIS');

        if($userapi->is_mobile())
            $class .= ' m_captcha';*/
        $html = "";
        $html .= "\n".'<script>var captcha_url  = "'.CAPTCHA_URL.'";</script>';
        //$html .= "\n".'<script>var g5_captcha_path = "'.G5_CAPTCHA_PATH.'";</script>';
        $html .= "\n".'<script src="'.CAPTCHA_URL.'/kcaptcha.js"></script>';
        $html .= "\n".'<fieldset id="captcha" class="'.$class.'">';
        $html .= "\n".'<legend><label for="captcha_key">자동등록방지</label></legend>';
        //if ($userapi->is_mobile()) $html .= '<audio src="#" id="captcha_audio" controls></audio>';
        //$html .= "\n".'<img src="#" alt="" id="captcha_img">';
        $html .= "\n".'<img src="javascript:void(0);" alt="" id="captcha_img">';
        //if (!$userapi->is_mobile()) $html .= "\n".'<button type="button" id="captcha_mp3"><span></span>숫자음성듣기</button>';
        $html .= "\n".'<button type="button" id="captcha_reload"><span></span>새로고침</button>';
        $html .= '<input type="text" name="captcha_key" id="captcha_key" required class="captcha_box required" size="6" maxlength="6">';
        $html .= "\n".'<span id="captcha_info">자동등록방지 숫자를 순서대로 입력하세요.</span>';
        $html .= "\n".'</fieldset>';
        return $html;
    }

    // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함
    function chk_captcha_js()
    {
        return "if (!chk_captcha()) return false;\n";
    }

    // 세션에 저장된 캡챠값과 $_POST 로 넘어온 캡챠값을 비교
    function chk_captcha()
    {
        $captcha_count = (int)$this->get_session('ss_captcha_count');
        if ($captcha_count > 5) {
            return false;
        }

        if (!isset($_POST['captcha_key'])) return false;
        if (!trim($_POST['captcha_key'])) return false;
        if ($_POST['captcha_key'] != $this->get_session('ss_captcha_key')) {
            $_SESSION['ss_captcha_count'] = $captcha_count + 1;
            return false;
        }
        return true;
    }

}



?>