<p class="subTit">회원 목록</p>
<form method="get" name="search_form">
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>계정 생성일</th>
        <td colspan="3"><span class="alignment01"><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->start_date)?>"> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->end_date)?>"></span><span class="alignment02 btn_date_list"><button type="button" class="btn_gray01" startDate="<?php echo(date('Y-m-d'))?>" endDate="<?php echo(date('Y-m-d'))?>">당일</button><button type="button" class="btn_gray01" endDate="<?php echo(date('Y-m-d'))?>" startDate="<?php echo(date("Y-m-d", strtotime('-  1 month' ,strtotime(date('Y-m-d')))))?>">1개월</button><button  type="button" class="btn_gray01" endDate="<?php echo(date('Y-m-d'))?>" startDate="<?php echo(date("Y-m-d", strtotime('-  3 month' ,strtotime(date('Y-m-d')))))?>">3개월</button></span></td>
    </tr>
    <tr>
        <th>회원 상태</th>
        <td>
            <div class="select">
                <select name="state" style="width:150px;">
                    <option value="" >전체</option>
                    <?php if(count(ENUM_USER_STATE::getItems()) > 0){ foreach(ENUM_USER_STATE::getItems() as $key=>$val){ ?>
                        <option value="<?php echo($key)?>" <?php echo($request_params->state == $val ? "selected" : "")?> ><?php echo($val)?></option>
                    <?php }} ?>
                </select>
            </div>
        </td>
        <th>이메일 주소</th>
        <td><input type="text" name="email" placeholder="이메일 주소 입력"  class="inp02" value="<?php echo($request_params->email)?>"></td>
    </tr>
</table>
<p class="btnbox"><button class="btn_blue01">검색</button></p>
<ul class="memSearch">
    <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span></li>
    <li><button class="btn_green01" onclick="PopupCenter('/user/popupInfo','window_name',918,760);">회원별 상세 조회</button>
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
        <col width="15%"/><col width="40%"/><col width="30%"/><col width="15%"/>
    </colgroup>
    <tr>
        <th>계정 생성일</th><th>ETH 계정</th><th>이메일 주소</th><th>회원 상택</th>
    </tr>
    <?php if(count($data->rows) > 0){ foreach($data->rows as  $key=>$val){ ?>
    <tr>
        <td><?php echo(substr($val->create_datetime , 0, 10))?><br/><?php echo(substr($val->create_datetime , 11 ,20))?></td>
        <td><?php echo($val->account)?></td>
        <td class="popupDetail"><a href="javascript:;" user-email="<?php echo($val->email)?>"><?php echo($val->email)?></a></td>
        <td class="<?php echo($val->state == 8 || $val->state == 9 ? "red" :"")?>"><?php
                if($val->state == 8 || $val->state == 9){
                    echo(ENUM_USER_STATE::_print($val->state) . "<br>".date('Y-m-d H:i:s' , $val->update_datetime));
                }else {
                    echo(ENUM_USER_STATE::_print($val->state));
                }
            ?></td>
    </tr>
    <?php }} ?>

</table>
<?php echo($paging);?>
<script>
    $(document).ready(function () {
        $('select[name=pageSize]').on('change' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            $('form[name=search_form]').submit();
        });

        $('.btn_date_list button').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var start_date = $(this).attr('startDate');
            var end_date = $(this).attr('endDate');

            $('input[name=start_date]').val(start_date);
            $('input[name=end_date]').val(end_date);

        });

        $('.popupDetail a').on('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            PopupCenter('/user/popupInfo?email=' + $(this).attr('user-email'),'window_name',918,760);
        });
    });


</script>
<!--<ul class="pagenum">
    <li class="arr"><i class="fas fa-chevron-left"></i></li><li class="on"><a href="#">1</a></li><li><a href="#">2</a></li><li><a href="#">3</a></li><li><a href="#">4</a></li><li><a href="#">5</a></li><li class="arr"><i class="fas fa-chevron-right"></i></li>
</ul>-->