<div class="contents">
    <p class="mytit"><img src="<?php echo COM_ASSETS_PATH; ?>/img/customer_tit.png" alt=""> 고객센터</p>
    <div class="table_wrap">
        <ul class="cutomer_tab">
            <li class="on" >공지사항</li>
            <li onclick="location.href='/board/form/qna/add'">1:1 문의</li>
            <li onclick="location.href='/board/lists/qna' ">내 문의 내역</li>
        </ul>
        <ul class="acco_tit">
            <li class="acco_no">번호</li><li class="acco_tit" style="text-align:center">제목</li><li class="acco_date">등록일</li><li class="acco_arrow"></li>
        </ul>
        <ul id="example2" class="accordion">
            <?php if(count($data->rows) > 0){ $num = $data->num_start; foreach($data->rows as $key=>$val){ ?>
                <li>
                    <h3 class="list_view">
                        <div class="acco_no"><?php echo($num)?></div><div class="acco_tit"><?php echo($val->subject)?></div><div class="acco_date"><?php echo(substr($val->regdate , 0 ,10))?></div><div class="acco_arrow"><img src="<?php echo(COM_ASSETS_PATH)?>/img/icon_arrow_down.png" alt=""></div>
                    </h3>
                    <div class="panel loading" style="display: none;">
                        <p><?php echo($val->contents)?></p>
                        <div class="acco_btnbox">
                            <button class="btn_graybox close_box">X 닫기</button>
                        </div>
                    </div>
                </li>
                <?php $num--;}} ?>
        </ul>
    </div>
<script type="text/javascript">
$(document).ready(function () {
    $('.accordion h3.list_view').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).siblings('.loading').slideToggle();
        var imgSrc = $(this).find('.acco_arrow img').attr('src');
        var changeSrc = imgSrc.indexOf('down') != -1 ? imgSrc.replace('down' , 'up') : imgSrc.replace('up' , 'down');
        $(this).find('.acco_arrow img').attr('src', changeSrc);
    });
    $('.accordion .close_box').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).parents('.loading').slideToggle();
        var imgSrc = $(this).parents().find('h3').children('.acco_arrow').find('img').attr('src');
        //console.log(imgSrc);
        var changeSrc = imgSrc.indexOf('down') != -1 ? imgSrc.replace('down' , 'up') : imgSrc.replace('up' , 'down');
        $(this).parents().find('h3').children('.acco_arrow').find('img').attr('src', changeSrc);
    });
});
</script>