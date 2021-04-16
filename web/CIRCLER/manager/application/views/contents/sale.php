<p class="subTit">콘텐츠 판매 현황</p>
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>판매기간</th>
        <td colspan="3"><span class="alignment01 "><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" /></span><span class="alignment02 btn_calender_list"><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' ))?>" end_date="<?php echo(date('Y-m-d' ))?>">당일</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 1 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">1개월</button><button class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 3 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">3개월</button></span></td>
    </tr>
    <tr>
        <th>콘텐츠 번호</th>
        <td><input type="text" name="contents_id" placeholder="콘텐츠 번호 입력"  class="inp03" value="<?php echo($request_params->contents_id)?>"></td>
        <th>콘텐츠 제목</th>
        <td><input type="text" name="title" placeholder="콘텐츠 제목 입력"  class="inp03" value="<?php echo($request_params->title)?>"></td>
    </tr>
</table>
<p class="btnbox"><button class="btn_blue01">검색</button></p>
<table class="sty04">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>판매건수</th>
        <td><?php echo(number_format($data->total_rows))?>건</td>
        <th>판매금액</th>
        <td><?php echo(number_format($data->total_point))?> WEI</td>
    </tr>
</table>
<ul class="memSearch" style="margin-top:30px">
    <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span></li>
    <li><div class="select">
            <select name="pageSize" style="width:130px;">
                <option value="20" <?php echo($request_params->page_size == 20 ? "selected" : "")?>>20개씩 보기</option>
                <option value="50" <?php echo($request_params->page_size == 50 ? "selected" : "")?>>50개씩 보기</option>
                <option value="100" <?php echo($request_params->page_size == 100 ? "selected" : "")?>>100개씩 보기</option>
            </select>
        </div>
    </li>
</ul>
<table class="sty03">
    <colgroup>
        <col width="10%"/><col width="40%"/><col width="10%"/><col width="10%"/><col width="10%"/><col width="*"/>
    </colgroup>
    <tr>
        <th>콘텐츠 번호</th><th>콘텐츠 제목</th><th>용량</th><th>판매단가</th><th>판매건수</th><th>판매금액</th>
    </tr>
    <?php if(count($data->rows) > 0){ foreach($data->rows as $key=>$val){?>
    <tr>
        <td><?php echo($val->contents_id)?></td><td><?php echo($val->title)?></td><td><?php echo(getFileSizeStr($val->size))?></td><td><?php echo(number_format($val->real_cash))?></td><td><?php echo(number_format($val->cnt))?></td><td><?php echo(number_format($val->sum_point))?></td>
    </tr>
    <?php }} ?>
</table>
<?php echo($paging)?>
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