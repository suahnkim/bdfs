<link rel="stylesheet" type="text/css" href="<?php echo COM_ASSETS_PATH; ?>/css/main.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="contents">
    <div class="maintop">
        <div class="main_tit">
            <span class="tit_txt">#주간 HOT 영상 </span><button class="more_txt" onclick="location.href='/contents/lists/popular'">+</button>
        </div>
        <div id="lista1" class="als-container">
            <span class="als-prev"><img src="<?php echo COM_ASSETS_PATH; ?>/img/roll_arrw.png" alt="prev" title="previous" /></span>
            <div class="als-viewport">
                <ul class="als-wrapper contentsList">
                    <?php if($popular_data->total_rows > 0){ $num_start = $popular_data->num_start; foreach($popular_data->rows as $key=>$val){

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
                        <li class="als-item pic" contents_id="<?php echo($val->contents_id)?>" adult="<?php echo($val->is_adult)?>">
                            <?php if($val->is_adult =='Y' && $user->is_adult == 'N'){ ?><div class="adult"></div><?php } ?>
                            <?php if($val->is_adult =='Y' && $user->is_adult == 'Y'){ ?><div class="adultlogin "></div><?php } ?>

                            <div class="con_iconbox">
                                <?php if($val->drm == 'Y'){ ?>  <span class="drm">DRM</span><?php } ?>
                            </div>
                            <div class="imgbox">
                                <img class="lazy "  data-src="<?php echo($data_src)?>" alt="best_item" style="width;386px;height:auto;" onerror="this.src='/assets/common/img/common/no-img.jpg'"></div>
                            <div class="als-textbox">
                                <div class="hotnum">
                                    <img class="lazy " data-src="<?php echo COM_ASSETS_PATH; ?>/img/hot_num_<?php echo($key+1)?>.png" alt="">
                                </div>
                                <div class="contxt">
                                    <p class="tit"><a href="#"><?php echo($val->title)?> </a></p>
                                    <div class="">
                                        <span class="way"><?php echo(number_format($val->real_cash))?>WEI</span><span class="date"><?php echo(substr(date('Y.m.d' ,$val->wdate ), 2 , 9))?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php $num_start--; if($key >= 9) break;}} ?>
                </ul>
            </div>
            <span class="als-next"><img src="<?php echo COM_ASSETS_PATH; ?>/img/roll_arrw.png" alt="next" title="next" /></span>
        </div>
    </div>
    <!-- 추천영상 -->
    <div class="main_tit">
        <span class="tit_txt">#추천 영상 </span><button class="more_txt" onclick="location.href='/contents/lists/recommand'">+</button>
    </div>
    <?php
    if($recommand_data->total_rows > 0){
        $num_start = $recommand_data->num_start;

        $_div = 0;
        $_div_arr = array();
        $_li = 0;
        $_li_arr = array();
        //shuffle($data->rows);
        foreach($recommand_data->rows as $key=>$val){

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

            $_DRM = $val->drm == "Y" ? "<span class=\"drm\">DRM</span>".PHP_EOL : "";
            $_ADULT = $val->is_adult =='Y' && $user->is_adult == 'N' ? "<div class=\"adult\"></div>".PHP_EOL : "";
            $_SMALL_ADULT = $val->is_adult =='Y' && $user->is_adult == 'Y' ? "<div class=\"adultlogin\"></div>".PHP_EOL : "";
     ?>
        <?php
        if($recommand_data->total_rows > 5){

            if($key == 0 || $key == 1){

                $_div_arr[$_div] = "<div class=\"main_best01 item pic\"  contents_id=\"".$val->contents_id."\" adult=\"".$val->is_adult."\">
                                                   ".$_ADULT. $_SMALL_ADULT ."
                                                    <div class=\"con_iconbox\">
                                                        ".$_DRM."<span class=\"way\">".number_format($val->real_cash)."WEI</span>
                                                    </div>
                                                    <div class=\"imgbox\">
                                                    <img class=\"lazy \"  data-src=\"".$data_src."\" alt=\"best_item\" style=\"width;400px;height:auto;\" onerror=\"this.src='/assets/common/img/common/no-img.jpg'\"></div>
                                                    <div class=\"tit\">
                                                        <span class=\"date\">".get_datetime($val->wdate)."</span><a href=\"javascript:;\">".$val->title."</a>
                                                    </div>
                                                </div>";
                $_div++;

            }else{


                $_li_div[$_li] = " <li class='item pic' contents_id=\"".$val->contents_id."\" adult=\"".$val->is_adult."\">
                                                    ".$_ADULT. $_SMALL_ADULT ."
                                                    <div class=\"con_iconbox\" >
                                                        ".$_DRM."
                                                    </div>
                                                    <div class=\"imgbox\">
                                                          <img class=\"lazy \"  data-src=\"".$data_src."\" alt=\"best_item\" style=\"width;200px;height:auto;;\" onerror=\"this.src='/assets/common/img/common/no-img.jpg'\">
                                                    </div>
                                                    <div class=\"titbox\">
                                                        <a href=\"javascript:;\">".$val->title."</a>
                                                    </div>
                                                    <span class=\"way\">".number_format($val->real_cash)."WEI</span><span class=\"date\">".get_datetime($val->wdate)."</span>
                                                </li>";

                $_li++;
            }
        }else{
            if($key == 0){

                $_div_arr[$_div] = "<div class=\"main_best01 item pic\"  contents_id=\"".$val->contents_id."\" adult=\"".$val->is_adult."\">
                                                    ".$_ADULT. $_SMALL_ADULT ."
                                                    <div class=\"con_iconbox\">
                                                        ".$_DRM."<span class=\"way\">".number_format($val->real_cash)."WEI</span>
                                                    </div>
                                                    <div class=\"imgbox\">
                                                    <img class=\"lazy \"  data-src=\"".$data_src."\" alt=\"best_item\" style=\"width;400px;height:auto;\" onerror=\"this.src='/assets/common/img/common/no-img.jpg'\"></div>
                                                    <div class=\"tit\">
                                                        <span class=\"date\">".get_datetime($val->wdate)."</span><a href=\"javascript:;\">".$val->title."</a>
                                                    </div>
                                                </div>";
                $_div++;

            }else{


                $_li_div[$_li] = " <li class='item pic' contents_id=\"".$val->contents_id."\" adult=\"".$val->is_adult."\">
                                                    ".$_ADULT. $_SMALL_ADULT ."
                                                    <div class=\"con_iconbox\" >
                                                        ".$_DRM."
                                                    </div>
                                                    <div class=\"imgbox\">
                                                          <img class=\"lazy \"  data-src=\"".$data_src."\" alt=\"best_item\" style=\"width;200px;height:auto;;\" onerror=\"this.src='/assets/common/img/common/no-img.jpg'\">
                                                    </div>
                                                    <div class=\"titbox\">
                                                        <a href=\"javascript:;\">".$val->title."</a>
                                                    </div>
                                                    <span class=\"way\">".number_format($val->real_cash)."WEI</span><span class=\"date\">".get_datetime($val->wdate)."</span>
                                                </li>";

                $_li++;
            }
        }
        ?>
    <?php
            }}
           $sort_arr = array(0,1,2,3);
           $sort_arr2 = array(4,5,6,7);
           $contents_html = "";
            foreach($_div_arr as $key=>$val){
                $contents_html .= "<div class=\"main_con contentsList\">".PHP_EOL;
                $contents_html .= $val.PHP_EOL;
                $contents_html .= "<ul class=\"main_best02\">".PHP_EOL;
                if($key == 0 ){
                    foreach($sort_arr as $k=>$v){
                        $contents_html .= @$_li_div[$v].PHP_EOL;
                    }
                }else{
                    foreach($sort_arr2 as $k=>$v){
                        $contents_html .= @$_li_div[$v].PHP_EOL;
                    }
                }
                $contents_html .= "</ul>";
                $contents_html .= "</div>".PHP_EOL;
            }

            echo $contents_html;
    ?>

    <!-- //추천영상 -->
    
    <div class="linebanner">
        <!--<img src="<?php echo(COM_ASSETS_PATH)?>/img/linebanner.jpg" alt="">--><!-- 배너사이즈 width:1248px / height:90px -->
    </div>
    
    <!-- 새로운영상 -->
    <div class="main_tit">
        <span class="tit_txt">#최근 등록된 영상 </span><button class="more_txt" onclick="location.href='/contents/lists/recent'">+</button>
    </div>
    <ul class="main_new contentsList">
        <?php if($recent_data->total_rows > 0){ $num_start = $recent_data->num_start; foreach($recent_data->rows as $key=>$val){

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
    <!--</div>
</div>-->
<script>
    $(function () {
       // $.account_balance_callback();
        $("#lista1").als({
            visible_items: 3,
            scrolling_items: 1,
            orientation: "horizontal",
            circular: "yes",
            autoscroll: "yes",
            interval: 5000,
            speed: 500,
            easing: "linear",
            direction: "left",
            start_from: 0
        });

        var ll = new LazyLoad({
            threshold: 0
        });
    });
</script>
