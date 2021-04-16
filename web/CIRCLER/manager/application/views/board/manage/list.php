<p class="subTit">게시판 목록</p>
<form method="get" name="search_form">
    <table class="sty02">
        <colgroup>
            <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
        </colgroup>
        <tr>
            <th>검색</th>
            <td colspan="3">
                <div class="select">
                    <select name="state" style="width:150px;float:left;">
                        <?php
                        $search_arr = array('bbs_is'=>'게시판아이디' , 'bbs_name'=>'게시판명');
                        foreach($search_arr as $key=>$val){
                            if($key == $request_params->search_key) $sel = "selected";
                            else $sel = "";
                            echo "<option value='".$key."' {$sel}>".$val."</option>";
                        }
                        ?>
                    </select>

                </div>
                <input type="text" name="email" placeholder="검색어 입력"  class="inp03" value="<?php echo($request_params->search_value)?>" style="width:200px;float:left;margin-left:20px;">
            </td>
        </tr>
    </table>
    <p class="btnbox"><button class="btn_blue01">검색</button></p>
    <ul class="memSearch">
        <li>검색건수 <span class="searchNum">총 <?php echo(number_format(count($data->rows)))?>건</span></li>
        <li>&nbsp;<button class="btn_blue02 board_writen_btn" type="button">게시판만들기</button>
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
        <col width="15%"/><col width="40%"/><col width="20%"/><col width="25%"/>
    </colgroup>
    <tr>
        <th>게시판 아이디</th><th>게시판명</th><th> 생성일</th><th>수정/삭제</th>
    </tr>
    <?php if(count($data->rows) > 0){ foreach($data->rows as  $key=>$val){ ?>
        <tr board_info_id="<?php echo($val->board_info_id)?>">
            <td><a href="/board/manage/write?board_info_id=<?php echo($val->board_info_id)?>"><?php echo($val->bbs_id)?></a></td>
            <td><a href="/board/manage/write?board_info_id=<?php echo($val->board_info_id)?>"><?php echo($val->bbs_name)?></a>&nbsp;<button  type="button" class="btn_green01" onclick="location.href='/board/lists/<?php echo($val->bbs_id)?>'">바로가기</button></td>
            <td class="popupDetail"><?php echo($val->regdate)?></td>
            <td class="">
                <button  type="button" class="btn_blue02 btn_modify" >수정</button>
                <button  type="button" class="btn_red01 btn_delete" >삭제</button>
            </td>
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

        $('.btn_modify').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var board_info_id = $(this).parent().parent().attr('board_info_id');

            document.location.href = '/board/manage/write?board_info_id=' + board_info_id;
        });

        $('.board_writen_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            document.location.href = '/board/manage/write';

        })
    });


</script>
