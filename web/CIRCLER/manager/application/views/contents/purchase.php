<p class="subTit">회원 구매 현황</p>
<form method="get">
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>구매일</th>
        <td colspan="3"><span class="alignment01 btn_calender_list"><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->start_date)?>"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->end_date)?>"/></span><span class="alignment02 btn_calender_list"><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' ))?>" end_date="<?php echo(date('Y-m-d' ))?>">당일</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 1 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">1개월</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 3 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">3개월</button></span></td>
    </tr>
    <tr>
        <th>구매 회원<br/>(이메일 주소)</th>
        <td colspan="3"><input type="text" name="email" placeholder="콘텐츠 번호 입력"  class="inp03" style="width:100%" value="<?php echo($request_params->email)?>"/></td>
    </tr>
    <tr>
        <th>콘텐츠 번호</th>
        <td><input type="text" name="contents_id" placeholder="콘텐츠 번호 입력"  class="inp03" value="<?php echo($request_params->contents_id)?>"></td>
        <th>콘텐츠 제목</th>
        <td><input type="text" name="title" placeholder="콘텐츠 제목 입력"  class="inp03" value="<?php echo($request_params->title)?>"></td>
    </tr>
</table>
<p class="btnbox"><button class="btn_blue01" type="submit">검색</button></p>
    <table class="sty04">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>구매건수</th>
        <td><?php echo(number_format($data->total_rows))?>건</td>
        <th>구매금액</th>
        <td><?php echo(number_format($data->total_point))?> WEI</td>
    </tr>
</table>
<ul class="memSearch" style="margin-top:30px">
    <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span></li>
    <li>
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
        <col width="10%"/><col width="20%"/><col width="10%"/><col width="10%"/><col width="15%"/><col width="*"/>
    </colgroup>
    <tr>
        <th>콘텐츠 번호</th><th>콘텐츠 제목</th><th>용량</th><th>구매금액</th><th>구매일</th><th>구매 회원(이메일 주소)</th>
    </tr>
    <?php if(count($data->rows) > 0 ){ foreach($data->rows as $key=>$val){?>
    <tr>
        <td><?php echo($val->contents_id)?></td><td><?php echo($val->title)?></td><td><?php echo(getFileSizeStr($val->size))?></td><td><?php echo($val->point)?></td><td><?php echo(date('Y-m-d'))?><br/><?php echo(date('H:i:s'))?></td><td><?php echo($val->email)?></td>
    </tr>
    <?php }}?>
</table>
<?php echo($paging);?>
<script>
    $(document).ready(function () {
        $('.btn_calender_list button').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('input[name=start_date]').val($(this).attr('start_date'));
            $('input[name=end_date]').val($(this).attr('end_date'));
        });
        $('select[name=pageSize]').on('change' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('form').submit();
        });
    });
</script>