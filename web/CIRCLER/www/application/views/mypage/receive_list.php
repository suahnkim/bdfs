
<div class="contents">
    <p class="mytit"><img src="<?php echo COM_ASSETS_PATH; ?>/img/mycontents_tit.png" alt=""> 내가 받은 컨텐츠</p>
    <div class="table_wrap">
        <div class="btnbox">
            <button class="btn_whitebox delete_btn">선택 삭제<img src="<?php echo COM_ASSETS_PATH; ?>/img/ic_x.png" alt=""></button><!--<button class="btn_violetbox">다시 받기<img src="<?php /*echo COM_ASSETS_PATH; */?>/img/ic_download.png" alt=""></button>-->
        </div>
        <table class="sty01 line2 contentsReceiveList">
            <colgroup><col width="10%"><col width="*"><col width="15%"><col width="12%"><col width="12%"></colgroup>
            <tr>
                <th><form><input type="checkbox" name="all_chk" class="all_chk"/></form></th><th>제목</th><th>용량</th><th>가격</th><th>잔여일</th>
            </tr>
            <tr class="noticeline">
                <td><img src="<?php echo COM_ASSETS_PATH; ?>/img/ic_notice.png" alt=""></td><td colspan="4" class="align_left">다운로드한 콘텐츠는 3일간 무료로 다시받기가 가능합니다</td>
            </tr>
            <?php if($data->total_rows > 0){ $num = $data->num_start; foreach($data->rows as $key=>$val){ ?>
            <tr class="item" contents_id="<?php echo($val->contents_id)?>">
                <td><form><input type="checkbox" name="chk[]" value="<?php echo($val->contents_id)?>"/></form></td><td class="align_left"><a href="javascript:;"><?php echo($val->title)?></a></td><td><?php getFileSizeStr($val->size)?></td><td><?php echo(number_format($val->real_cash))?> WEI</td><td><?php echo(@get_dayhours($val->edate))?></td>

            </tr>
            <?php $num--;}} ?>
        </table>
        <?php echo($paging)?>
        <form method="get">
        <div class="board_search">
            Search <input type="text" name="receive_srch_value" class="textbox" value="<?php echo($request_params->receive_srch_value)?>"><button type="submit" class="btn_bseach">검색</button>
        </div>
    </div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/receive.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>