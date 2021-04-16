<div class="contents">
    <div class="main_tit">
        <span class="tit_txt">#<?php echo($sub_title)?> </span>
    </div>
    <ul class="main_new contentsList">
        <?php if($data->total_rows > 0){ $num_start = $data->num_start; $data->rows;foreach($data->rows as $key=>$val){

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
            <li class="pic" contents_id="<?php echo($val->contents_id)?>" adult="<?php echo($val->is_adult)?>">
                <?php if($val->is_adult =='Y' && $user->is_adult == 'N'){ ?><div class="adult"></div><?php } ?>
                <?php if($val->is_adult =='Y' && $user->is_adult == 'Y'){ ?><div class="adultlogin "></div><?php } ?>
                <div class="con_iconbox">
                    <?php if($val->drm == 'Y'){ ?><div class="drm">DRM</div><?php } ?>
                </div>
                <div class="imgbox" style="text-align:center;position:relative;overflow:hidden;display:flex;;justify-content:center;align-items:center;">
                    <a href="javascript:;" ><img class="lazy "  data-src="<?php echo($data_src)?>" alt="best_item" style="width;230px;height:auto;" onerror="this.src='/assets/common/img/common/no-img.jpg'"></a>
                </div>
                <div class="titbox">
                    <a href="javascript:;"><?php echo($val->title)?></a>
                </div>
                <span class="way"><?php echo(number_format($val->real_cash))?>WEI</span><span class="date"><?php echo(get_datetime($val->wdate))?></span>
            </li>
            <?php $num_start--; }} ?>
    </ul>
</div>
<script>
    var segment = "<?php echo($this->uri->segment(3));?>";
</script>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/contents.lists.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>