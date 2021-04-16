<?php
$first_segment = $this->uri->segment(1);
$left_segment = $this->uri->segment(2);
?>
<div id="snb">
    <ul class="snb_nav">
        <li class="<?php echo($left_segment == 'receive' ? 'on' :'')?>"><a href="/mypage/receive"><i class="fas fa-file-download"></i> 내가 받은 콘텐츠</a></li>
        <li class="<?php echo($left_segment == 'wish' ? 'on' :'')?>"><a href="/mypage/wish"><i class="fas fa-clipboard-check"></i> 찜한 콘텐츠</a></li>
        <li class="<?php echo($left_segment == 'aaa' ? 'on' :'')?>"><a href="#" class="wei_change_btn"><i class="fas fa-file-word"></i> WEI 전환</a></li>
        <li class="<?php echo($left_segment == 'usage' ? 'on' :'')?>"><a href="/mypage/usage"><i class="fas fa-file-alt"></i> WEI 이용내역</a></li>
        <li class="<?php echo($first_segment == 'board' ? 'on' :'')?>"><a href="/board/lists/notice"><i class="fas fa-file-audio"></i></i> 고객센터</a></li>
    </ul>
    <!-- 배너사이즈 width:214px / height:255px -->
    <!--
    <div class="leftban">
        <img src="<?php echo(COM_ASSETS_PATH)?>/img/leftban.jpg" alt="">
    </div>
    -->
</div>

