<p class="subTit">성인인증요청 목록</p>
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
            <th>인증 상태</th>
            <td>
                <div class="select">
                    <select name="adult_yn" style="width:150px;">
                        <option value="" >전체</option>
                        <option value="N" <?php echo($request_params->adult_yn == 'N' ? "selected" : "")?> >인증대기</option>
                        <option value="Y" <?php echo($request_params->adult_yn == 'Y' ? "selected" : "")?> >인증완료</option>
                    </select>
                </div>
            </td>
            <th>이메일 주소</th>
            <td><input type="text" name="email" placeholder="이메일 주소 입력"  class="inp02" value="<?php echo($request_params->email)?>"></td>
        </tr>
    </table>
    <p class="btnbox"><button class="btn_blue01">검색</button></p>
    <ul class="memSearch">
        <li>검색건수 <span class="searchNum">총 <?php echo(number_format($data->total_rows))?>건</span> <!--<button class="btn_blue02 btn_commit" style="margin-left:10px;">인증승인</button> <button class="btn_red01 btn_cancel">인증취소</button>--></li>
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
        <col width="15%"/><col width="30%"/><col width="30%"/><col width="15%"/><col width="15%"/>
    </colgroup>
    <tr>
        <!--<th><input type="checkbox" class="all_chk"></th>--><th>신청일</th><th>ETH 계정</th><th>이메일 주소</th><th>인증상태</th><th></th>
    </tr>
    <?php if(count($data->rows) > 0){ foreach($data->rows as  $key=>$val){ ?>
        <tr certify_id="<?php echo($val->certify_id)?>" user_info_id="<?php echo($val->user_info_id)?>" identify_id="<?php echo($val->identify_id)?>" attributeId="<?php echo($val->attributeId)?>">
            <!--<td><input type="checkbox" name="chk[]" class='chk' value="<?php /*echo($val->certify_id)*/?>" user_info_id="<?php /*echo($val->user_info_id)*/?>"></td>-->
            <td><?php echo(substr($val->regdate , 0, 10))?><br/><?php echo(substr($val->regdate , 11 ,20))?></td>
            <td><?php echo($val->account)?></td>
            <td class="popupDetail"><a href="javascript:;" user-email="<?php echo($val->email)?>"><?php echo($val->email)?></a></td>
            <td class="<?php echo($val->adult_yn == 'N' ? "red" :"")?>"><?php
                if($val->adult_yn == 'N'){
                    echo "승인대기";
                }else {
                    echo "인증완료";
                }
                ?></td>
            <td>
                <?php if($val->adult_yn == 'N'){ ?>
                    <button class="btn_blue02 btn_commit">인증승인</button>
                <?php }else{ ?>
                    <buttnon class="btn_red01 btn_cancel">인증취소</buttnon>
                <?php } ?>
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

        $('.all_chk').on('click' , function (event) {
            $('.chk').prop('checked' , $(this).is(':checked') ? true : false);
        });

        /*$('.btn_commit').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var len = $('.chk:checked').length;
            if(len >0){

                if(confirm('선택된 '+len+'건을 성인인증을 승인하시겠습니까?')){
                    var user_info_ids = [];
                    var certify_ids = [];
                    $('input[name="chk[]"]:checked').each(function () {
                        user_info_ids.push($(this).attr('user_info_id'));
                        certify_ids.push($(this).val());
                    });

                    var params = {user_info_ids : user_info_ids , certify_ids : certify_ids , 'adult_yn' : 'Y'};
                    var data = $.runsync('/user/setCertify' , params , 'json' , false);
                    if(data.code == 200){
                        alert(len + '건이 승인되었습니다.');
                        document.location.reload();
                    }else{
                        alert(data.message);
                    }
                }
            }else{
                alert('선택된 회원이 없습니다.');
            }
        });*/
        $(document).on('click' , '.btn_commit' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var certify_id = $(this).parents('tr').attr('certify_id');
            var user_info_id = $(this).parents('tr').attr('user_info_id');

            if(certify_id){
                if(confirm('성인인증을 승인하시겠습니까?')){
                    var params = {user_info_id : user_info_id , certify_id : certify_id , 'adult_yn' : 'Y' , 'identify_id' : $(this).parents('tr').attr('identify_id') , 'attributeId' : $(this).parents('tr').attr('attributeId')};
                    var data = $.runsync('/user/setSingleCertify' , params , 'json' , true);
                    if(data.code == 200){
                        alert('승인되었습니다.');
                        document.location.reload();
                    }else{
                        alert(data.message);
                    }
                }
            }
        });

        $(document).on('click' , '.btn_cancel' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var certify_id = $(this).parents('tr').attr('certify_id');
            var user_info_id = $(this).parents('tr').attr('user_info_id');

            if(certify_id){
                if(confirm('성인인증을 취소하시겠습니까?')){
                    var params = {user_info_id : user_info_id , certify_id : certify_id , 'adult_yn' : 'N' , 'identify_id' : $(this).parents('tr').attr('identify_id') , 'attributeId' : $(this).parents('tr').attr('attributeId')};
                    var data = $.runsync('/user/setSingleCertify' , params , 'json' , true);
                    if(data.code == 200){
                        alert('인증이 취소되었습니다.');
                        document.location.reload();
                    }else{
                        alert(data.message);
                    }
                }
            }
        });

       /* $('.btn_cancel').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var len = $('.chk:checked').length;
            if(len >0){
                if(confirm('선택된 '+len+'건을 성인인증을 취소하시겠습니까?')){
                    var user_info_ids = [];
                    var certify_ids = [];
                    $('input[name="chk[]"]:checked').each(function () {
                        user_info_ids.push($(this).attr('user_info_id'));
                        certify_ids.push($(this).val());
                    });

                    var params = {user_info_ids : user_info_ids , certify_ids : certify_ids , 'adult_yn' : 'N'};
                    var data = $.runsync('/user/setCertify' , params , 'json' , false);
                    if(data.code == 200){
                        alert(len + '건이 취소되었습니다.');
                        document.location.reload();
                    }else{
                        alert(data.message);
                    }
                }

            }else{
                alert('선택된 회원이 없습니다.');
            }
        });*/
    });


</script>
<!--<ul class="pagenum">
    <li class="arr"><i class="fas fa-chevron-left"></i></li><li class="on"><a href="#">1</a></li><li><a href="#">2</a></li><li><a href="#">3</a></li><li><a href="#">4</a></li><li><a href="#">5</a></li><li class="arr"><i class="fas fa-chevron-right"></i></li>
</ul>-->