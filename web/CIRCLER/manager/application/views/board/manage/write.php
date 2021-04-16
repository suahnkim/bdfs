<p class="subTit">게시판관리</p>
<div style="background: #FF0000;color:#fff;margin-bottom:10px;line-height:50px;padding-left: 10px;display:none;border-radius: 5px;" class="errorMsg"></div>
<table class="sty02">
    <form class="borad_form" action="/board/setManage" method="post">
        <input type="hidden" name="act" value="<?php echo(@$data->board_info_id ? 'mod' : 'reg')?>">
        <input type="hidden" name="board_info_id" value="<?php echo(@$data->board_info_id)?>">
    <colgroup>
        <col width="15%"/><col width="35%"/><col width="15%"/><col width="35%"/>
    </colgroup>
    <tr>
        <th>게시판아이디</th>
        <td><input type="text" name="bbs_id" placeholder="게시판 아이디 입력"  class="inp03" required="required" title="게시판 아이디" value="<?php echo(@$data->bbs_id)?>" <?php echo(@$data->board_info_id ? "disabled":"")?>></td>
        <th>게시판명</th>
        <td><input type="text" name="bbs_name" placeholder="게시판명 입력"  class="inp03"  value="<?php echo(@$data->bbs_name)?>"></td>
    </tr>
    <tr>
        <th>스킨선택</th>
        <td>
            <div class="select">
                <select name="skin_name" style="width:200px;">
                    <?php
                    $skin_dir=  dirname(__FILE__) . "/../skin";
                    $handle=opendir($skin_dir);
                    while ($skin_info = readdir($handle))
                    {
                        if($skin_info != '.' && $skin_info != '..')
                        {

                            if(!@$data->skin_name) $data->skin_name = "basic";

                            if($skin_info == $data->skin_name) $select="selected";
                            else $select="";


                            echo"<option value='".$skin_info."' {$select}>$skin_info</option>";

                        }
                    }
                    closedir($handle);
                    ?>
                </select>
            </div>
        </td>
        <th>파일 사용</th>
        <td>
            <input type="radio" name="file_use" value="Y" <?php echo(@$data->file_use == 'Y' || @$data->file_use == '' ? 'checked':'')?>> 사용 <input type="radio" name="file_use" value="N" <?php echo(@$data->file_use == 'N' ? 'checked':'')?>> 사용안함
            <span class="view_file_cnt" style="display: <?php echo(@$data->file_use == 'Y' || @$data->file_use == '' ? '' :'none')?>;padding-left:30px;">
                <input type="text" name="file_cnt" class="inp04" value="1" onKeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo(@$data->file_cnt)?>">
                    <ul class="soltarrow" style="margin-right:10px;">
                         <li><i class="fas fa-sort-up"></i></li>
                         <li><i class="fas fa-sort-down"></i></li>
                     </ul>
            </span>
        </td>
    </tr>
    <tr>
        <th>카테고리명</th>
        <td><input type="text" name="ca_title" placeholder="카테고리명를 입력하세요"  class="inp03" value="<?php echo(@$data->ca_title)?>"></td>
        <th>카테고리 옵션</th>
        <td><input type="text" name="ca_name" placeholder="ex) 임플란드|&|치아|&|보험"  class="inp03" value="<?php echo(@$data->ca_name)?>"></td>
    </tr>
    <tr>
        <th>리스트수</th>
        <td class="view_list_cnt">
            <input type="text" name="list_cnt" class="inp04" value="20" onKeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo(@$data->list_cnt)?>"/>
            <ul class="soltarrow" style="margin-right:10px;">
                <li><i class="fas fa-sort-up"></i></li>
                <li><i class="fas fa-sort-down"></i></li>
            </ul>
        </td>
        <th>에디터사용</th>
        <td><input type="radio" name="editor_use" value="Y" <?php echo(@$data->editor_use == 'Y' || @$data->editor_use == '' ? 'checked':'')?>> 사용 <input type="radio" name="editor_use" value="N" <?php echo(@$data->editor_use == 'N' ? 'checked':'')?>> 사용안함</td>
    </tr>
        <tr>
            <th>답글사용</th>
            <td class="view_list_cnt">
                <input type="radio" name="reply" value="Y" <?php echo(@$data->reply == 'Y')?"checked":""?>> 사용 <input type="radio" name="reply" value="N" <?php echo(@$data->reply == 'N'  || @$data->reply == '' ? 'checked':'' )?>> 사용안함
            </td>
            <th>비밀글사용</th>
            <td><input type="radio" name="secret" value="Y" <?php echo(@$data->secret == 'Y'? 'checked':'')?>> 사용 <input type="radio" name="secret" value="N" <?php echo(@$data->secret == 'N'  || @$data->secret == '' ? 'checked':'')?>> 사용안함</td>
        </tr>
    </form>
</table>
<p class="btnbox"><button class="btn_blue03 btn_cancel" type="submit">취소하기</button>&nbsp;&nbsp;&nbsp;<button class="btn_blue01 btn_submit" type="submit"><?php echo(@$data->board_info_id ? '수정' : '확인')?></button></p>
<script>
    var max_file_cnt = 3;
    var min_file_cnt = 1;
    var max_list_cnt = 100;
    var min_list_cnt = 1;
    $(document).ready(function () {

        $('input[name=file_cnt]').on('keyup , mousedown' , function (event) {

            var n = $(this).val();
            console.log(n);
        });

        $('input[name=file_use]').on('change' , function () {
            var chk = $('input[name=file_use]:checked').val();

            $('span.view_file_cnt').css({'display' : chk == 'Y' ? '' :'none'});
        });

        $('.view_file_cnt input[name=file_cnt]').on('keyup' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var file_cnt_val = $(this).val();
            $(this).val(file_cnt_val > max_file_cnt  ? max_file_cnt : file_cnt_val);
        })

        $('.view_file_cnt i.fa-sort-up').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var cnt = parseInt($('input[name=file_cnt]').val());
            if(cnt <1 ){
                $('input[name=file_cnt]').val(1);
            }
            if(cnt >= max_file_cnt){
                alert('첨부파일 개수는 ' + max_file_cnt + '개이상 추가 할 수 없습니다.');
                return false;
            }
            $('input[name=file_cnt]').val(++cnt);

        });
        $('.view_file_cnt i.fa-sort-down').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var cnt = parseInt($('input[name=file_cnt]').val());

            if(cnt <= min_file_cnt){
                alert('첨부파일 개수는 ' + min_file_cnt + '개이상 가능합니다.');
                return false;
            }
            $('input[name=file_cnt]').val(--cnt);

        });

        $('.view_list_cnt input[name=list_cnt]').on('keyup' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var list_cnt_val = $(this).val();
            $(this).val(list_cnt_val > max_list_cnt  ? max_list_cnt : list_cnt_val);
        })


        $('.view_list_cnt i.fa-sort-up').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var cnt = parseInt($('input[name=list_cnt]').val());
            if(cnt >= max_list_cnt){
                alert('리스트수는 ' + max_list_cnt + '이상 설정 할 수 없습니다.');
                return false;
            }
            $('input[name=list_cnt]').val(++cnt);

        });
        $('.view_list_cnt i.fa-sort-down').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var cnt = parseInt($('input[name=list_cnt]').val());

            if(cnt <= min_list_cnt){
                alert('리스트수는 ' + min_list_cnt + '이상 가능합니다.');
                return false;
            }
            $('input[name=list_cnt]').val(--cnt);
        });

        $('.btn_submit').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('form.borad_form').submit();
        });

        var formName = new commonFormValidation({
            form:".borad_form",
        });

        $("form.borad_form").submit(function(event){
            event.stopPropagation();
            event.preventDefault();

            var form_obj = $(this);
            if(!formName.formValidate(true)){
                return;
            }
            var params = form_obj.serialize();
            //console.log(params);
            var data = $.runsync('/board/setBoardManage' , params , 'json'  , true);

            if(data.code == 200){
                alert('정상적으로 설정되었습니다.');
                document.location.href = '/board/manage/list';
            }else{
                alert(data.message);
            }
        });

        $('input[name=bbs_id]').on('keyup', function (event) {
            event.stopPropagation();
            event.preventDefault();

            var params= {search_key : 'bbs_id' , search_value : $(this).val()};
            var data = $.runsync('/board/checkField' , params , 'json' , true);

            if(data.result){
                $('div.errorMsg').fadeOut();
            }else{
                $('div.errorMsg').fadeIn(function () {
                    $(this).text('!! 게시판아이디는 ' + data.msg);
                });
            }
        });

        $('button.btn_cancel').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            document.location.href = '/board/manage/list';
        });

    });
</script>