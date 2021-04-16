<div class="contents">
    <p class="mytit"><img src="<?php echo COM_ASSETS_PATH; ?>/img/usein_tit.png" alt=""> WEI 이용내역</p>
    <div class="table_wrap">
        <ul class="mywaylist">
            <li>적립 : <?php echo(number_format($total['min_total']->data->total_point))?>  WEI</li><li>사용 : <?php echo(number_format($total['add_total']->data->total_point))?>  WEI</li>
        </ul>
        <form class="use_form" method="POST">
        <div class="myconSearch">
            <div class="calendarBox">
                <input type="text" name="start_date" class="jca-date-picker"  value="<?php echo($request_params->start_date)?>" placeholder="전체">
            </div>
            <span class="period"></span>
            <div class="calendarBox">
                <input type="text" name="end_date" class="jca-date-picker" value="<?php echo($request_params->end_date)?>" placeholder="전체">
            </div>
            <div class="custom-select" style="width:120px;">
                <select name="point_type">
                    <option value="" <?php echo($request_params->point_type == '' ? 'selected' : '')?>>전체</option>
                    <option value="2" <?php echo($request_params->point_type == '2' ? 'selected' : '')?>>적립</option>
                    <option value="1" <?php echo($request_params->point_type == '1' ? 'selected' : '')?>>사용</option>
                </select>
            </div>
            <button class="btn_graybox" type="submit">조회</button>
        </div>
        </form>
        <table class="sty01 usagelist">
            <colgroup><col width="20%"><col width="35%"><col width="15%"><col width="15%"><col width="15%"></colgroup>
            <tr>
                <th></th><th>내용</th><th>구분</th><th>날짜</th><th>금액</th>
            </tr>
            <?php if(count($data->rows) > 0){ $num = $data->num_start;  foreach($data->rows as $key=>$val){ ?>
            <tr>
                <td>
                    <div class="<?php echo($val->point_type == 1 ? 'useway' : 'saveway')?>">
                        <?php echo($val->point_type == 1 ? '사용' : '적립')?>
                    </div>
                </td>
                <td class="align_left"><a href="javascript:;"><?php echo($val->info)?></a></td><td><?PHP echo(ENUM_POINT_TYPE::_print($val->code))?></td><td><?php echo(date("Y.m.d",$val->wdate))?></td><td><span class="<?php echo($val->point_type == 1 ? 'usetxt' : 'savetxt')?>"><?php echo($val->point_type == 1 ? '-' : '+')?><?php echo(number_format($val->point))?></span></td>
            </tr>
            <?php $num--; }}else{ ?>
                <tr>
                    <td colspan="5">이용내역이 없습니다.</td>
                </tr>
            <?php }?>
        </table>
    </div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/use.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
