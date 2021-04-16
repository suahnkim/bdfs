<style>
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 100; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;

    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal_multi {z-index:9999;}
</style>

<div class="footer">
    <p class="foot_btnArea">
        <a href="javascript:contents_maskLayer($('.policy'));" class="myBtn_multi">이용약관</a>
        <a href="javascript:contents_maskLayer($('.privacy'));" class="myBtn_multi">개인정보처리방침</a>
    </p>
    <p>본 콘텐츠의 저작권은 저작권자에 있으며, 이를 무단으로 이용하는 경우 저작권법 등에 따라 법적 책임을 질 수 있습니다.</p>


    <p>㈜피플앤스토리 | 사업자등록번호 : 105-88-10325 | 통신판매업신고번호 : 제2014-서울마포-1544호 | 대표 : 김남철</p>
    <p>주소: 서울시 마포구 월드컵북로5가길 12 서교빌딩 4층 (우) 04001 | 문의전화 : 02)322-2900</p>

    <p>Copyright ⓒPeople&Story. All right reserved.</p>

</div>
<!-- The Modal -->
<div class="modal modal_multi policy" >
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close close_multi" onClick="$('.modal_multi , #mask').fadeOut();">×</span>
        <?php include_once(dirname(__FILE__) . "/policy.php"); ?>
    </div>

</div>

<div  class="modal modal_multi privacy">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close close_multi" onClick="$('.modal_multi , #mask').fadeOut();">×</span>
        <?php include_once(dirname(__FILE__) . "/privacy.php"); ?>
    </div>
</div>

<!--- adult popup  start-->
<div class="adultPop">
    <div class="adultbox">
        <div class="leftimg">
            <img src="<?php echo(COM_ASSETS_PATH)?>/img/ic_adultpop.png" alt="">
        </div>
        <div class="righttxt">
            <p class="tit">이 정보 내용은 청소년에게 유해한 정보를 포함하고 있어 성인인증 절차를 거쳐야합니다.</p>
            <p class="txt">이 정보 내용은 청소년유해매체물로써 정보통신망 이용촉진 및 정보보호등에 관한 법률 및 청소년보호법의 규정에 의하여 19세 미만의 청소년이 이용할 수 없습니다.</p>
        </div>
    </div>
    <div class="adultbtnbox">
        <button class="grayline" onClick="maskLayer($('.adultPop'));">닫기</button><button class="redline adult_certify_btn">성인인증</button>
    </div>
</div>
<!--- adult popup  end-->
<form class="certify_form" action="/user/certify" method="post">
<div class="certify" >
    <div id="certify_loading" style="background-color: rgba(0,0,0,0.7);position: absolute;z-index: 9999;display:;left:0;top:0;width:500px;height:453px;vertical-align: middle;text-align:center;display:none;">
        <div class="img" style="line-height:450px;vertical-align: top;color:#fff;"><img src="<?php echo COM_ASSETS_PATH; ?>/img/common/buying.gif" alt="로딩" style="width:50px;height:50px;vertical-align: middle;" >&nbsp;&nbsp;&nbsp;인증요청중...</div>
    </div>
    <p class="tit">휴대폰 간편인증</p>
    <div class="categoryTxt">
        이름
    </div>
    <div class="inputbox">
        <input type="text" name="name"/>
    </div>
    <div class="categoryTxt">
        휴대폰 번호
    </div>
    <div class="inputbox">
        <input type="text" name="hp" placeholder="‘-’  없이 숫자만 입력"/>
    </div>
    <div class="securitybox">
        <div class="secuNum">
            <img src="" alt="" id="captcha_img">
        </div>
        <div class="secuIcon">
            <button type="button" id="captcha_reload"><i class="fas fa-sync-alt"></i></button><button type="button" id="captcha_mp3"><i class="fas fa-volume-up"></i></button>
        </div>
        <div class="secuchek">
            <p class="tit">보안숫자입력</p>
            <div class="writeinp">
                <input type="text" name="captcha_key" id="captcha_key"/>
            </div>
        </div>
    </div>
    <div class="adultbtnbox" style="margin-top:40px;">
        <button type="button" class="grayline" onclick="maskLayer($('.certify'));$('form.certify_form')[0].reset();">취소</button><button type="button" class="graylbox btn_ceritfy_submit">확인</button>
    </div>
</div>
</form>

<!-- The Modal -->

</div>

<div id="loading_ajax">
    <div class="img"><img src="<?php echo COM_ASSETS_PATH; ?>/img/common/loading.gif" alt="로딩" /></div>
</div>
<div id="mask" style="position:absolute;left:0px;top:0px;display:none;background-color:#000;z-index:9996;"></div>
<div class="layer_contents_pop" id="layer_contents_pop" style="display:none;position:absolute;z-index:9997;background:#fff;top:0;overflow-y: auto;height:100%;"></div>
<div class="popup_top" style="display:none;cursor:pointer;"><img src="<?php echo(COM_ASSETS_PATH)?>/img/common/top.png"></div>
<div class="popup_close_btn" style="display:none;cursor:pointer;"><img src="<?php echo(COM_ASSETS_PATH)?>/img/common/close.png"></div>

<iframe id="cifrm" name="cifrm" src="" style="width:0px;height:0px" class="hide"></iframe>
</body>
</html>
