
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php include_once(dirname(__FILE__) . "/../common/inc/top.php"); ?>
    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 24px;
            vertical-align:middle;
        }

        /* Hide default HTML checkbox */
        .switch input {display:none;}

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 24px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        p {
            margin:0px;
            display:inline-block;
            font-size:15px;
            font-weight:bold;
        }

    </style>
</head>
<body>
<form method="get">
<div class="mem_search">
    <div class="emailSearch">
        <input type="text" name="email" class="inp02" value="<?php echo(@$request_params->email)?>"/><button class="btn_blue01">조회</button>
    </div>
    <div class="memline"></div>
    <div class="memsrcBox">
        <table class="sty01">
            <colgroup>
                <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
            </colgroup>
            <tr>
                <th>이메일 주소</th>
                <td colspan="3"><?php echo(@$user->email)?></td>
            </tr>
            <tr>
                <th>성인인증</th>
                <td colspan="3">
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider round"></span>
                    </label>
                    <p>인증대기</p>
                    <p style="display:none;">인증완료</p>
                </td>
            </tr>


            <tr>
                <th>ETH 계정</th>
                <td colspan="3"><?php echo(@$user->account)?></td>
            </tr>
            <tr>
                <th>계정 생성일</th>
                <td><?php echo(@$user->create_datetime)?></td>
                <th>회원상태</th>
                <td><?php echo(ENUM_USER_STATE::_print(@$user->state))?><?php if(@$user->state == 8 || @$user->state == 9){?> <span class="txt_red">(<?php echo(date('Y-m-d H:i:s' ,$val->update_datetime))?>)</span><?php } ?></td>
            </tr>
            <tr>
                <th>보유 ETH</th>
                <td>2012 ETH</td>
                <th>보유 WEI</th>
                <td>5,590 WEI</td>
            </tr>
        </table>
    </div>
    <div class="memline"></div>
    <div class="memsrcBox">
        <p class="memsrcTit">WEI 이용 안내</p>

        <div class="srcDetail">
				<span class="alignment01">기간 <input type="text" name="start_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo(@$request_params->start_date)?>"/> ~ <input type="text" name="end_date" placeholder="전체" class="inp01 jca-date-picker" value="<?php echo(@$request_params->end_date)?>" />
				<div class="select">
					<select name="point_type" style="width:90px">
                        <option value="" <?php echo(@$request_params->point_type == '' ? 'selected' : '')?>>전체</option>
                        <option value="2" <?php echo(@$request_params->point_type == '2' ? 'selected' : '')?>>적립</option>
                        <option value="1" <?php echo(@$request_params->point_type == '1' ? 'selected' : '')?>>사용</option>
					</select>
				</div>
				</span><span class="alignment02"><button class="btn_gray01" type="submit">검색</button></span>
        </div>
        <div class="usage">
            조회 기간 합계 : <span class="txt_blue">적립 <?php echo(number_format(@$total['min_total']->data->total_point))?> WEI</span> ㅣ <span class="txt_red">사용 <?php echo(number_format(@$total['add_total']->data->total_point))?> WEI</span>
        </div>
        <table class="sty01">
            <colgroup>
                <col width="18%"/><col width="18%"/><col width="27%"/><col width="27%"/><col width="10%"/>
            </colgroup>
            <tr>
                <th>날짜</th><th>구분</th><th>상세구분</th><th>내용</th><th>금액</th>
            </tr>
            <?php if(count(@$data->rows) > 0){ $num = $data->num_start;  foreach($data->rows as $key=>$val){ ?>
            <tr>
                <td class="c"><?php echo(date("Y-m-d",$val->wdate))?><br/><?php echo(date("H:i:s",$val->wdate))?></td>
                <td class="c"><?php echo($val->point_type == 1 ? '사용' : '적립')?></td>
                <td class="c"><?PHP echo(ENUM_POINT_TYPE::_print($val->code))?></td>
                <td class="c"><?php echo($val->info)?></td>
                <td class="c"><?php echo($val->point_type == 1 ? '-' : '+')?><?php echo(number_format($val->point))?></td>
            </tr>
            <?php $num--; }}else{ ?>
              <tr>
                <td colspan="5" style="text-align: center;">이용내역이 없습니다.</td>
              </tr>
            <?php } ?>
        </table>
       <?php echo($paging)?>
    </div>
</div>
</form>
<script>
    $(function(){
        var check = $("input[type='checkbox']");
        check.click(function(){
            $("p").toggle();
        });
    });

</script>
</body>
</html>