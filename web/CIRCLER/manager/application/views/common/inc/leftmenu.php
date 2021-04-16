<?php
$_segment = $this->uri->segment(1);
$path_info = @$_SERVER['PATH_INFO'];
?>

<div id="snb">
    <div class="tab">
        <button class="tablinks <?php echo($path_info == '/user/lists' || $path_info == '' ? 'active' : '')?> " onclick="openCity(event, 'leftmenu01')"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_menu01.png" alt=""><br/>회원 관리</button>
        <button class="tablinks <?php echo($path_info == '/contents/distribution' ? 'active' : '')?>" onclick="openCity(event, 'leftmenu02')"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_menu02.png" alt=""><br/>콘텐츠 관리</button>
        <button class="tablinks <?php echo($path_info == '/contents/purchaseManage' || $_segment == '/contnets/weiExchange' ? 'active' : '')?>" onclick="openCity(event, 'leftmenu03')"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_menu03.png" alt=""><br/>구매 관리</button>
        <button class="tablinks <?php echo($path_info == '/contents/saleManage' ? 'active' : '')?>" onclick="openCity(event, 'leftmenu04')"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_menu04.png" alt=""><br/>판매 관리</button>
        <button class="tablinks <?php echo($_segment == 'board'  ? 'active' : '')?>" onclick="openCity(event, 'leftmenu05')"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon_menu05.png" alt=""><br/>고객센터</button>
    </div>

    <ul id="leftmenu01" class="tabcontent" style="display:<?php echo($path_info == '/user/lists' || $path_info == '/user/certify' || $_segment == '' ? '' : 'none')?>">
        <li><span class="submenu<?php echo($path_info == '/user/lists'? '_on' : '')?>"><a href="/user/lists">회원 목록</a></span></li>
        <li><span class="submenu<?php echo($path_info == '/user/certify'? '_on' : '')?>"><a href="/user/certify">성인인증요청 목록</a></span></li>
    </ul>

    <ul id="leftmenu02" class="tabcontent snbmap02" style="display:<?php echo($path_info == '/contents/distribution' ? '' : 'none')?>">
        <li><span class="submenu<?php echo($path_info == '/contents/distribution' ? '_on' : '')?>"><a href="/contents/distribution">콘텐츠 유통 관리</a></span></li>
    </ul>

    <ul id="leftmenu03" class="tabcontent snbmap03"  style="display:<?php echo($path_info == '/contents/purchaseManage' || $path_info == '/contents/weiExchange'? '' : 'none')?>">
        <li><span class="submenu<?php echo($path_info == '/contents/purchaseManage' ? '_on' : '')?>"><a href="/contents/purchaseManage">회원 구매 현황</a></span></li>
        <li><span class="submenu<?php echo($path_info == '/contents/weiExchange' ? '_on' : '')?>"><a href="/contents/weiExchange">WEI 전환 현황</a></span></li>
    </ul>

    <ul id="leftmenu04" class="tabcontent snbmap04"  style="display:<?php echo($path_info == '/contents/saleManage' ? '' : 'none')?>">
        <li><span class="submenu<?php echo($path_info == '/contents/saleManage' ? '_on' : '')?>"><a href="/contents/saleManage">콘텐츠 판매 현황</a></span></li>
    </ul>
    <ul id="leftmenu05" class="tabcontent snbmap05" style="display:<?php echo($_segment == 'board' ? '' : 'none')?>">
        <li><span class="submenu<?php echo($path_info == '/board/manage/list' ? '_on' : '')?>"><a href="/board/manage/list">게시판관리</a></span></li>
        <?php if(count($this->boardinfo->boardlist) > 0){ foreach($this->boardinfo->boardlist as $key => $val){ ?>
            <li><span class="submenu<?php echo($path_info == '/board/lists/'.$val->bbs_id ? '_on' : '')?>"><a href="/board/lists/<?php echo($val->bbs_id)?>"><?php echo($val->bbs_name)?></a></span></li>
        <?php }}?>
    </ul>
</div>

<script>
    function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    // Get the element with id="defaultOpen" and click on it
    //document.getElementById("defaultOpen").click();
</script>