<div class="contents">
    <div class="main_outer">
        <div class="main_inner">
            <h4>인기콘텐츠</h4>
            <div class="tb_best">
                <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                    <li class="item pic" contents_id="<?php echo($val->contents_id)?>">
                        <div class="play">20.307</div>
                        <?php if($val->drm == 'Y'){ ?><div class="icon">DRM</div><?php } ?>
                        <div class="thum">
                            <a href="#" ><img class="lazy" data-src="<?php echo(COM_ASSETS_PATH)?>/img/thum/thum01.jpg" alt="best_item" class="image"></a>
                            <div class="middle">
                                <div class="icon_plus">+</div>
                            </div>
                        </div>
                        <div class="info">
                            <p class="subj"><a href="#" ><?php echo($val->title)?></a></p>
                            <p class="line">
                                <span class="author"><?php echo($this->menu->categoty_list[$val->cate1])?><span class="bar"></span></span>
                                <span class="price"><?php echo(number_format($val->real_cash))?> Way</span>
                                <span class="date"><?php echo(get_datetime($val->wdate))?></span>
                            </p>
                        </div>
                    </li>
                    <?php $num_start--; }} ?>
            </div>
            <p class="LineBanner"><a href="#"><img src="<?php echo(COM_ASSETS_PATH)?>/img/lineban.jpg" ></a></p>
            <h4>새로운콘텐츠</h4>
            <div class="tb_best">
                <?php if($data->total_rows > 0){ $num_start = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                <li class="item pic">
                    <div class="play">20.307</div>
                    <div class="icon">DRM</div>
                    <div class="thum">
                        <a href="#" ><img src="<?php echo(COM_ASSETS_PATH)?>/img/thum/thum01.jpg" alt="best_item" class="image"></a>
                        <div class="middle">
                            <div class="icon_plus">+</div>
                        </div>
                    </div>
                    <div class="info">
                        <p class="subj"><a href="#" >스파이더맨 파 프롬 홈 보기전 알아야 할 5가지 총정리</a></p>
                        <p class="line">
                            <span class="author">나혼자산다 방성훈<span class="bar"></span></span>
                            <span class="price">130 Way</span>
                            <span class="date">5일전</span>
                        </p>
                    </div>
                </li>
                 <?php $num_start--; }} ?>
            </div>
            <p class="LineBanner"><a href="#"><img src="<?php echo(COM_ASSETS_PATH)?>/img/lineban.jpg" ></a></p>
            <h4>추천콘텐츠</h4>
            <div class="tb_best">
                <li class="item pic">
                    <div class="play">20.307</div>
                    <div class="icon">DRM</div>
                    <div class="thum">
                        <a href="#" ><img src="<?php echo(COM_ASSETS_PATH)?>/img/thum/thum01.jpg" alt="best_item" class="image"></a>
                        <div class="middle">
                            <div class="icon_plus">+</div>
                        </div>
                    </div>
                    <div class="info">
                        <p class="subj"><a href="#" >스파이더맨 파 프롬 홈 보기전 알아야 할 5가지 총정리</a></p>
                        <p class="line">
                            <span class="author">나혼자산다 방성훈<span class="bar"></span></span>
                            <span class="price">130 Way</span>
                            <span class="date">5일전</span>
                        </p>
                    </div>
                </li>
            </div>
        </div>
    </div>

</div>