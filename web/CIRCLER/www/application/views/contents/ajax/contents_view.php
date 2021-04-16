<style type="text/css">
    <!--
    .conPop {width:800px; background:#fff; }
    .conPop .tit {width:100%; height:52px; padding:12px 0 0 25px; font-size:16px; color:#fff; background-color: #7973df; /* For browsers that do not support gradients */  background-image: linear-gradient(to right, #7973df , #2bb2e2); /* Standard syntax (must be last) */}
    .conPop .contop {padding:25px; border-bottom:1px solid #dbdbdb; overflow:hidden;}
    .conPop .contop .contit {font-size:22px; ; color:#000; margin:0 0 20px 0}
    .conPop .contop .contit img {vertical-align:middle;}
    .conPop .contop .datepay {border-bottom:1px solid #dbdbdb; overflow:hidden; margin:0 0 25px 0}
    .conPop .contop .datepay .uploadDate {width:50%; height:35px; float:left; font-size:16px; color:#666}
    .conPop .contop .datepay .conpay {width:50%; height:35px; float:left; font-size:20px; color:#7973df; font-weight:bold;text-align:right}
    .conPop .contop .btnbox {width:800px; padding:25px 0 ; overflow:hidden; text-align:center;margin:0;}
    .conPop .contop .btnbox button.btn_close {width:265px; height:60px; border:1px solid #7973df; background:#fff; font-size:20px; color:#7973df; font-weight:500; margin-right:20px;}
    .conPop .contop .btnbox button.btn_download {width:265px; height:60px; border:1px solid #7973df; background:#7973df; font-size:20px; color:#fff; }
    .conPop .contop .btnbox button.btn_purchase {width:265px; height:60px; border:1px solid #7973df; background:#7973df; font-size:20px; color:#fff; }
    .conPop .contop .btnbox button.btn_redownload {width:265px; height:60px; border:1px solid #007ba7; background:#2bb2e2; font-size:20px; color:#fff; }
    .conPop .conbom {background:#f0f0f0; padding:25px; }
    .conPop .conbom .containBox01 {background:#fff; border:1px solid #dbdbdb;overflow:hidden;}
    .conPop .conbom .containBox01 ul.sty01 {width:100%; height:45px; overflow:hidden; background:#f9f9f9; border-bottom:1px solid #7973df; padding-top:11px}
    .conPop .conbom .containBox01 ul.sty01 li {height:45px; float:left;font-size:14px; color:#000; font-weight:500}
    .conPop .conbom .containBox01 ul.sty01 li:nth-child(1) {width:70%; padding-left:25px;}
    .conPop .conbom .containBox01 ul.sty01 li:nth-child(2),.conPop .conbom .containBox01 ul.sty01 li:nth-child(3) {width:30%; text-align:center}
    .conPop .conbom .containBox01 ul.sty02 {overflow:hidden;border-bottom:1px solid #dbdbdb;}
    .conPop .conbom .containBox01 ul.sty02 li {height:45px; float:left;font-size:14px; color:#666;padding-top:11px}
    .conPop .conbom .containBox01 ul.hide_list {display:none;}
    .conPop .conbom .containBox01 ul.sty02 li:nth-child(1) {width:70%; padding-left:25px}
    .conPop .conbom .containBox01 ul.sty02 li:nth-child(2),.conPop .conbom .containBox01 ul.sty02 li:nth-child(3) {width:30%; text-align:center}
    .conPop .conbom .containBox01 button.btn_listmore {width:90%; height:40px; background:#fbf5e1; margin:25px; border:1px solid #ece2c0; font-size:14px; color:#786420}
    .conPop .conbom .containBox02 {background:#fff; border:1px solid #dbdbdb; overflow:hidden;padding:25px; margin-top:25px;}
    .conPop .contop .datepay .conpay .likebox {display:inline-block; font-size:20px; color:#666; font-weight:400; margin-right:30px; line-height:100%; vertical-align:top; margin-top:5px;}
    .conPop .contop .datepay .conpay .likebox img {margin-top:2px; vertical-align:top}

    .likebox img:hover{ /* img 마우스 오버시*/
         cursor:pointer;
         transform:scale(1.3); /* 마우스 오버시 이미지 크기를 1.1 배만큼 확대시킨다. */
         -o-transform:scale(1.3);
         -moz-transform:scale(1.3);
         -webkit-transform:scale(1.3);
         transition: transform .35s;
         -o-transition: transform .35s;
         -moz-transition: transform .35s;
         -webkit-transition: transform .35s;
         /* 마우스 오버시 이미지가 즉시 커지지않고 30.5 second 의 시간에 걸쳐 커진다 애니메이션 효과*/
     }
    //-->
</style>
<?php
$drm = $data->drm == 'Y' ? '<img src= "' .COM_ASSETS_PATH. '/img/ic_drmb.png" alt="DRM">' : '';
?>

<form class="down-form">
    <input type="hidden" name="next_val" value="<?php echo($data->init_key)?>">
    <input type="hidden" name="contents_id" value="<?php echo($data->contents_id)?>">
    <input type="hidden" name="contents_ccid" value="<?php echo($data->ccid)?>">
    <input type="hidden" name="contents_version" value="<?php echo($data->ccid_ver)?>">
    <input type="hidden" name="user_id" value="<?php echo($data->userid)?>">
    <input type="hidden" name="account_id" value="<?php echo($user->eth_account)?>">
    <input type="hidden" name="file_type" value="<?php echo($data->folder_name)?>">
    <input type="hidden" name="status" value="">
    <input type="hidden" name="title" value="<?php echo($data->title)?>">
    <input type="hidden" name="productId" value="<?php echo($data->productId)?>">
    <input type="hidden" name="zzim_status" value="<?php echo($data->zzim)?>">
 <div class="conPop content_view" style="position:relative;">

    <div class="tit">
        콘텐츠 상세보기
    </div>
    <div class="contop">
        <p class="contit"><?php echo($data->title)?> <?php echo($drm)?></p>
        <ul class="datepay">
            <li class="uploadDate"><?php echo get_datetime($data->wdate)?></li>
            <li class="conpay"><span class="likebox zzim_btn" style="margin-right:30px;"><img src="<?php echo(COM_ASSETS_PATH)?>/img/icon_zzim<?php echo($data->zzim == 1 ? '_on' : '')?>.png" alt="" title="찜하기"> 찜하기</span><span class="likebox recommand_btn" style=""><img src="<?php echo(COM_ASSETS_PATH)?>/img/icon_like.png" alt="" title="추천하기"> <span class="recommand_cnt"><?php echo(number_format($data->eva))?></span></span><img src="<?php echo(COM_ASSETS_PATH)?>/img/ic_pay.png" alt=""> <?php echo(number_format($data->real_cash))?> WEI</li>
        </ul>
        <div class="btnbox down_btn_area" style="background:#fff;position:">
            <div id="popup_loading_ajax">
                <div class="img" style="line-height:60px;vertical-align: top;"><img src="<?php echo COM_ASSETS_PATH; ?>/img/common/buying.gif" alt="로딩" style="width:50px;height:50px;vertical-align: middle;" >구매진행중...</div>
            </div>
            <button class="btn_close contents_layer_close_btn">닫 기</button>
            <?php if($data->purchase == 1){ ?>
                <?php if($data->user_down > 0){ ?>
                <button class="btn_redownload">다시다운받기</button>
                <?php }else{ ?>
                <button class="btn_download">다운로드</button>
                <?php } ?>
            <?php }else{ ?>
                <button class="btn_purchase">구매하기</button>
            <?php } ?>
        </div>
    </div>
    <div class="conbom">
        <div class="containBox01">
            <ul class="sty01">
                <li>파일명</li><li>용량</li>
            </ul>
            <?php
            $view_cnt = 1;
            foreach($data->rows as $key=>$val){?>
            <ul class="sty02 <?php echo($view_cnt > 2 ? 'hide_list' : '')?>" file_no="<?php echo($val->sort)?>" contents_file_no="<?php echo($val->contents_file_id)?>" file_name="<?php echo($val->filename)?>" del_yn="N" file_size="<?php echo($val->realsize)?>" file_path="<?php echo($val->filename)?>" >
                <li><?php echo($val->filename)?></li><li><?php echo(getFileSizeStr($val->realsize))?></li>
            </ul>
            <?php $view_cnt++; }?>
            <?php if(count($data->rows) > 2){ ?>
            <div style="width:100%;text-align:center;both:clear;"><button class="btn_listmore">더 보 기</button></div>
            <?php } ?>
        </div>
        <div class="containBox02">
            <?php echo($data->contents)?>
        </div>
        <div class="containBox02 pic"><!-- 이미지 가로 사이즈  498px-->
            <?php

            if($data->main_img && $data->ccid_ver){

                if(strpos($data->main_img , 'coimg.circler.co.kr') !== false){
                    $data_src = $data->main_img;
                }else{
                    $exp_main_img = explode('|' , $data->main_img);
                    //$exp_main_img = explode('\\' , $main_img);
                    $data_src ="http://www.mediablockchain.co.kr".end($exp_main_img);
                }
                //$ext = substr(strrchr(end($exp_main_img), '.'), 1);
                ?>
                <div style="margin:10px; 0;"><img class="lazyload" data-src="<?php echo($data_src)?>" style="max-width:700px;"></div>
            <?php } ?>
            <?php
            if($data->sub_img && $data->ccid_ver){

                $sub_img_exp = explode(','  , $data->sub_img);
                foreach($sub_img_exp as $key=>$val){

                    if(strpos($data->sub_img , 'coimg.circler.co.kr') !== false) {
                        $sub_data_src = $val;
                    }else{
                        $exp_sub_img = explode('|' , $val);
                        //$exp_sub_img = explode('\\' , $sub_img);
                        $sub_data_src ="http://www.mediablockchain.co.kr".end($exp_sub_img);
                    }

                    ?>
                    <div style="margin:10px; 0;"><img class="lazyload" data-src="<?php echo($sub_data_src)?>" style="max-width:700px;"></div>
                <?php }} ?>
        </div>
    </div>

</div>
</form>

