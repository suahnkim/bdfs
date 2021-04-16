<p class="subTit">콘텐츠 유통 관리</p>
<form method="get">
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>콘텐츠 등록일</th>
        <td colspan="3"><span class="alignment01 "><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" /></span><span class="alignment02 btn_calender_list"><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' ))?>" end_date="<?php echo(date('Y-m-d' ))?>">당일</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 1 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">1개월</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 3 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">3개월</button></span></td>
    </tr>
    <!--<tr>
        <th>유통 시작일</th>
        <td colspan="3"><span class="alignment01"><input type="text" name="" placeholder="전체" class="inp01"/> ~ <input type="text" name="" placeholder="전체" class="inp01" /></span><span class="alignment02"><button class="btn_gray01">당일</button><button class="btn_gray01">1개월</button><button class="btn_gray01">3개월</button></span></td>
    </tr>-->
    <tr>
        <th>콘텐츠 번호</th>
        <td><input type="text" name="contents_id" placeholder="콘텐츠 번호 입력"  class="inp03" value="<?php echo($request_params->contents_id)?>"></td>
        <th>콘텐츠 제목</th>
        <td><input type="text" name="title" placeholder="콘텐츠 제목 입력"  class="inp03" value="<?php echo($request_params->title)?>"></td>
    </tr>
</table>
<p class="btnbox"><button type="submit" class="btn_blue01">검색</button></p>
<ul class="memSearch">
    <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span></li>
    <li><button class="btn_red01 all_del_btn">선택 삭제</button>
        <div class="select">
            <select name="pageSize" style="width:130px;">
                <option value="20" <?php echo($request_params->page_size == 20 ? "selected" : "")?>>20개씩 보기</option>
                <option value="50" <?php echo($request_params->page_size == 50 ? "selected" : "")?>>50개씩 보기</option>
                <option value="100" <?php echo($request_params->page_size == 100 ? "selected" : "")?>>100개씩 보기</option>
            </select>
        </div>
    </li>
</ul>
</form>
<table class="sty03">
    <colgroup>
        <col width="5%"/><col width="10%"/><col width="10%"/><col width="30%"/><col width="8%"/><col width="8%"/><!--<col width="15%"/>--><col width="10%"/><col width="*"/>
    </colgroup>
    <tr>
        <th><input type="checkbox" name="all_chk"></th><th>콘텐츠 등록일</th><th>콘텐츠 번호</th><th>콘텐츠 제목</th><th>용량</th><th>저작권료</th><!--<th>유통 시작일</th>--><th>콘텐츠 가격</th><th>관리</th>
    </tr>
    <?php if(count($data->rows) > 0){ foreach($data->rows as $key=>$val){?>
    <tr contents_id="<?php echo($val->contents_id)?>">
        <td><input type="checkbox" name="chk[]" value="<?php echo($val->contents_id)?>"></td>
        <td><?php echo(date('Y-m-d' , $val->wdate))?><br/><?php echo(date('H:i:s' , $val->wdate))?></td>
        <td><?php echo(number_format($val->contents_id))?></td>
        <td class="contents_title"><?php echo($val->title)?></td>
        <td class="contents_size_str"><?php echo(getFileSizeStr($val->size))?></td>
        <td class="price"><?php echo(number_format($val->cash))?></td>
       <!-- <td>2019-09-20<br/>16:02:35</td>-->
        <td class="real_price"><?php echo(number_format($val->real_cash))?></td>
        <td><button class="btn_red01 del_btn">삭제</button><button class="btn_blue02 price_modify_btn">가격 수정</button></td>
    </tr>
    <?php }} ?>

</table>
<?php echo($paging)?>

<div id="popup1" class="Pstyle">
<span class="b-close"><img src="../img/ic_close.png" alt=""></span>
<div class="popwidth">
    <input type="hidden" name="contents_id">
    <p class="tit">가격 수정</p>
    <div class="poplay">
        <table  class="sty01">
            <colgroup>
                <col width="25%"/><col width="25%"/><col width="25%"/><col width="25%"/>
            </colgroup>
            <tr>
                <th>제목</th><td colspan="3" class="modal_title">U20 월드컵 분석</td>
            </tr>
            <tr>
                <th>저작권료</th><td class="modal_price">100</td><th>용량</th><td class="modal_size">1.2GB</td>
            </tr>
            <tr>
                <th>콘텐츠 가격</th><td colspan="3"><input type="text" name="real_price" class="inp03" style="width:100%;"/></td>
            </tr>
        </table>
    </div>
    <button class="btn_pop01" onclick="$('#popup1').bPopup().close();">닫기</button><button class="btn_pop02 contents_modify_btn">수정하기</button>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#popupButton1").click(function() {
            $('#popup1').bPopup();
        });
        $("#popupButton2").click(function() {
            $('#popup2').bPopup();
        });
        $("#popupButton3").click(function() {
            $('#popup3').bPopup();
        });
        
        $('.btn_calender_list button').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('input[name=start_date]').val($(this).attr('start_date'));
            $('input[name=end_date]').val($(this).attr('end_date'));
        });

        $('.price_modify_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var title = $(this).parent().parent().find('td.contents_title').text();
            var size = $(this).parent().parent().find('td.contents_size_str').text();
            var price = $(this).parent().parent().find('td.price').text();
            var contents_id = $(this).parents('tr').attr('contents_id');
            var real_price = $(this).parent().parent().find('td.real_price').text().replace(/[^0-9]/g,"");
            $('.modal_title').text(title);
            $('.modal_size').text(size);
            $('.modal_price').text(price);
            $('input[name=contents_id]').val(contents_id);
            $('input[name=real_price]').val(real_price);
            $('#popup1').bPopup();
        });

        $('.del_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var contens_ids = [];
            if(confirm('선택한 콘텐츠를 삭제하시겠습니까?\n\n삭제한 콘텐츠는 복구 할 수 없습니다.\n\n그래도 정말 삭제하시겠습니까?')){
                var contents_id = $(this).parents('tr').attr('contents_id');
                contens_ids.push(contents_id);
                var params = {contents_ids : contens_ids};

                var data = $.runsync('/contents/delContents/' , params , 'json' , true);
                if(data.code == 200){
                    alert('정상적으로 삭제되었습니다.');
                    document.location.reload();
                }else{
                    aler(datat.message);
                }
            }
        });

        $('.all_del_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var contents_ids = [];

            if($('input[name="chk[]"]:checked').length > 0){
                if(confirm('선택한 콘텐츠를 삭제하시겠습니까?\n\n삭제한 콘텐츠는 복구 할 수 없습니다.\n\n그래도 정말 삭제하시겠습니까?')){
                    var contents_id = $(this).parents('tr').attr('contents_id');
                    $('input[name="chk[]"]:checked').each(function () {
                        contents_ids.push($(this).val());
                    });
                    var params = {contents_ids : contents_ids};

                    var data = $.runsync('/contents/delContents/' , params , 'json' , true);
                    if(data.code == 200){
                        alert('정상적으로 삭제되었습니다.');
                        document.location.reload();
                    }else{
                        aler(datat.message);
                    }
                }
            }else{
                alert('선택된 콘텐츠가 없습니다.');
            }
        });
        $('.contents_modify_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            if(confirm('콘텐츠 가격을 수정하시겠습니까?')){
                var params = {contents_id : $('input[name=contents_id]').val() , real_price : $('input[name=real_price]').val()};

                var data = $.runsync('/contents/setContentsModifyPrice' , params , 'json' , false);
                if(data.code == 200){
                    alert('정상적으로 수정되었습니다.');
                    document.location.reload();
                }else{
                    aler(datat.message);
                }
            }
        });

        $('input[name=all_chk]').on('click' , function () {

            $('input[name="chk[]"]').prop('checked' , $(this).is(':checked') ? true : false);
        });

        $('select[name=pageSize]').on('change' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('form').submit();
        });
    })
    
    
</script>