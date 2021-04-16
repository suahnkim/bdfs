<p class="subTit"><?php echo($data->board_config->bbs_name)?></p>
<form method="get">
<table class="sty02">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>등록일</th>
        <td colspan="3"><span class="alignment01 btn_calender_list"><input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->start_date)?>"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo($request_params->end_date)?>"/></span><span class="alignment02 btn_calender_list"><button type='button' class="btn_gray01" start_date="<?php echo(date('Y-m-d' ))?>" end_date="<?php echo(date('Y-m-d' ))?>">당일</button><button type='button' class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 1 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">1개월</button><button type='button' class="btn_gray01" start_date="<?php echo(date('Y-m-d' , strtotime(' - 3 month' , strtotime(date('Y-m-d')))))?>" end_date="<?php echo(date('Y-m-d' ))?>">3개월</button></span></td>
    </tr>
    <tr>
        <th>제목</th>
        <td colspan="3"><input type="hidden" name="search_key" value="t1.subject"><input type="text" name="search_value" placeholder="검색할 제목을 입력해 주세요."  class="inp03" style="width:100%"/></td>
    </tr>
</table>
<p class="btnbox"><button class="btn_blue01" type="submit">검색</button></p>

<ul class="memSearch" style="margin-top:30px">
    <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span></li>
    <li><button type="button" class="btn_green01" onclick="location.href='/board/form/<?php echo($data->board_config->bbs_id)?>/add' "><i class="fas fa-edit"></i> 작성하기</button><div class="select">
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
        <col width="10%"/><col width="*"/><col width="20%"/>
    </colgroup>
    <tr>
        <th>번호</th><th>제목</th><th>등록일</th>
    </tr>
    <?php
        if(count($data->rows) > 0){ $num = $data->num_start; foreach($data->rows as $key=>$val){
            $reply_icon = "";
            if($val->depth > 0){
                for($i=1; $i<$val->depth; $i++){
                    $reply_icon .= "&nbsp;&nbsp;";
                }
                $reply_icon .= "<img src='". COM_ASSETS_PATH."/img/ico_reply.png' style='vertical-align:top'>";
            }
      ?>
    <tr>
        <td><?php echo($num)?></td><td class="left"><?php echo($reply_icon)?><a href="/board/view/<?php echo($val->bbs_id)?>?board_id=<?php echo($val->board_id)?>"><?php echo($val->subject)?></a></td><td><?php echo(substr($val->regdate , 0 , 10))?><br/><?php echo(substr($val->regdate , -8))?></td>
    </tr>
    <?php $num--;}}else{ ?>
        <tr><td style="line-heigh:50px;text-align:center;" colspan="3">등록된 게시물이 없습니다.</td></tr>
    <?php } ?>
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