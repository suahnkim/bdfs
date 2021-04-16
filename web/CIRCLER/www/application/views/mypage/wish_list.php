<style>
    .wish_btnbox button.on {display:none;}
    .wish_btnbox button.off {display:;}
</style>
<div class="contents">
    <p class="mytit"><img src="<?php echo(COM_ASSETS_PATH)?>/img/wishlist_tit.png" alt=""> 찜한 컨텐츠</p>
    <div class="table_wrap">
    <div class="wish_btnbox">
        <span class="choiceNum"><span>1</span>개 선택</span><button class="btn_whitebox all_del_btn off">전체 삭제</button><button class="btn_whitebox all_sel_btn on">전체 선택</button><button class="btn_whitebox cancle_btn on">취소</button><button class="btn_violetbox confirm_btn on">확인</button>
    </div>
    <ul class="wishlist contentsList">

        <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){

                    if($val->main_img && $val->ccid_ver){
                        if(strpos($val->main_img , 'coimg.circler.co.kr') !== false){
                            $data_src = $val->main_img;
                        }else{
                            $exp_main_img = explode('|' , $val->main_img);
                            //$exp_main_img = explode('\\' , $main_img);
                            $data_src ="http://www.mediablockchain.co.kr".end($exp_main_img);
                        }
                    }else{
                        $data_src = COM_ASSETS_PATH . "/img/common/no-img.jpg";
                    }

            ?>

            <li class="pic" contents_id="<?php echo($val->contents_id)?>">
                <!-- 선택박스 -->
                <div class="selcetLine" style="display:none;">
                    <input type="checkbox" name="chk[]" class="delcheck" value="<?php echo($val->contents_id)?>">
                </div>
                <!-- //선택박스 -->
                <div class="con_iconbox">
                    <?php if($val->drm == 'Y'){ ?><div class="drm">DRM</div><?php } ?>
                </div>
                <div class="imgbox">
                    <img class='lazy' data-src="<?php echo($data_src)?>" alt="" width="100%">
                </div>
                <div class="titbox">
                    <a href="javascript:;"><?php echo($val->title)?></a>
                </div>
                <span class="way"><?php echo(number_format($val->real_cash))?>WEI</span><span class="date"><?php echo(get_datetime($val->wdate))?></span>
            </li>
            <?php $num_start--; }} ?>


    </ul>
    </div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/wish.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>