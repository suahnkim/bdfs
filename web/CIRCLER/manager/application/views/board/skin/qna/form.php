<?php
switch($request_params->act){
    case 'add' : $btn_name = "작성"; break;
    case 'mod' : $btn_name = "수정"; break;
    case 'reply' : $btn_name = "답글"; break;
}
?>
<script type="text/javascript" src="<?php echo MC_ASSETS_PATH; ?>/cheditor/cheditor.js"></script>
<p class="subTit"><?php echo($data->board_config->bbs_name)?> <?php echo($btn_name)?></p>
<?php if($request_params->act == "reply"){ ?>

<table class="sty02">
    <tr>
        <td>
            <p class="viewtit"><?php echo($data->subject)?></p>
            <div class="viewsbox">번호: <?php echo($data->board_id)?></div><div class="viewline"></div><div class="viewsbox">작성일 : <?php echo($data->regdate)?></div><div class="viewline"></div><div class="viewsbox"><a href="#" onclick="window.open('member_search.html','window_name','width=900px,height=750px,location=no,status=no,scrollbars=auto');"><?php echo($data->user_email)?></a></div><div class="viewline"></div><div class="viewsbox"><span class="txt_red">대기중</span><!-- 답변완료--></div>
        </td>
    </tr>
    <?php if(count($data->file_rows) > 0){ ?>
    <tr>
        <td class="down_list">
            <?php foreach($data->file_rows as $key=>$val){ ?>
            <p><i class="fas fa-paperclip"></i> 첨부파일 : <a href="javascript:;" style="color:#333" board_file_no="<?php echo($val->board_file_no)?>"><?php echo($val->file_origin_name)?></a></p>
            <?php }?>
        </td>
    </tr>
    <?php

    }
    ?>
    <tr>
        <td>
            <div class="qna_left txt_blue">
                Q
            </div>
            <div class="qna_right">
                <?php echo($data->contents)?>
            </div>
        </td>
    </tr>
</table>
<?php
    $data->subject = "";
    $data->contents = "";
    $data->file_rows = array();
    }
?>
<table class="sty02">
    <form name="theForm" method="post" action="/board/form_submit" enctype="multipart/form-data">
    <input type="hidden" name="bbs_id" value="<?php echo(@$data->board_config->bbs_id)?>">
    <input type="hidden" name="board_id" value="<?php echo(@$data->board_id)?>">
    <input type="hidden" name="act" value="<?php echo($request_params->act)?>">
      <?php if($request_params->act == 'reply'){ ?>
     <input type="hidden" name="depth" value="<?php echo(@$data->depth)?>">
     <input type="hidden" name="sort" value="<?php echo(@$data->sort)?>">
     <input type="hidden" name="wgroup" value="<?php echo(@$data->wgroup)?>">
      <?php } ?>
     <colgroup><col width="20%"><col width="*"></colgroup>
     <?php
     if($data->board_config->ca_name && $data->board_config->ca_title){

         $ca_opton_exe  = explode("|&|" , $data->board_config->ca_name)
         ?>
    <tr>
        <th><?php echo($data->board_config->ca_title)?></th>
        <td>
            <select name="ca_name" style="width:200px;">
                <option value=""><?php echo($data->board_config->ca_title)?>선택</option>
              <?php if(count($ca_opton_exe) > 0){ foreach($ca_opton_exe as $key=>$val){

                            if($val == @$data->ca_name) $sel = "selected";
                            else $sel = "";
                  ?>
                  <option value="<?php echo($val)?>" <?php echo($sel)?>><?php echo($val)?></option>
              <?php }}?>
            </select>
        </td>
    </tr>
    <?php }?>
    <tr>
        <th>제목</th>
        <td><input type="text"  name="subject" class="inp03" value="<?php echo(@$data->subject)?>"></td>
    </tr>
    <?php if($data->board_config->secret == 'Y'){ ?>
    <tr>
        <th>비밀글</th>
        <td><input type="checkbox"  name="secret" value="1" <?php echo(@$data->secret == 1 ? "checked" :"" )?>></td>
    </tr>
    <?php }?>
    <tr>
        <th>내용</th>
        <td class="editor">
            <textarea name="contents" id="editor" ><?php echo(@$data->contents)?></textarea>
        </td>
    </tr>
        <?php if($data->board_config->file_use == 'Y'){ ?>
    <tr>
        <th>첨부파일</th>
        <td class="file_list">
            <?php for($i=1; $i<=$data->board_config->file_cnt; $i++){?>
            <p class="mt30 filebox" style="margin-bottom:5px;"><input type="hidden" name="change_board_file_no[]" class="change_board_file_no" value=""><input type="file" id="" name="filename[]" class="upload-hidden" style="display:none;" accept="image/*"><input type="text" name="" class="inp03 upload-name" style="width:300px;" disabled value="<?php echo(@$data->file_rows[$i-1]->file_name ? @$data->file_rows[$i-1]->file_name : '선택파일없음')?>"> <button type="button" class="btn_gray01 file_put_btn"  style="width:100px;" board_file_no="<?php echo(@$data->file_rows[$i - 1]->board_file_no)?>"><i class="fas fa-search"></i> 파일찾기</button><button type="button" class="btn_white cancel_file" style="width:120px; height:34px;">선택파일 초기화</button>
                <?php if(@$data->file_rows[$i - 1]->board_file_no){ ?><img src="http://<?php echo($data->file_rows[$i-1]->file_domain)?>/<?php echo($data->file_rows[$i-1]->file_path)?>/<?php echo($data->file_rows[$i-1]->file_name)?>" style="width:80px;height:80px;border-radius: 20px;vertical-align: middle;"><input type="checkbox" value="<?php echo($data->file_rows[$i - 1]->board_file_no)?>" name="del_board_file[]" style="vertical-align: middle;">파일삭제<?php } ?>
            </p>
            <?php } ?>
        </td>
    </tr>
        <?php } ?>
    </form>
</table>
<p class="btnbox"><button type='button' class="btn_blue03" style="margin-right:10px;" onclick="location.href='/board/lists/<?php echo($request_params->bbs_id)?>'">돌아가기</button><button type='button' class="btn_blue01 btn_submit" >작성하기</button></p>

<script type="text/javascript">
    $(document).ready(function () {
        var myeditor = new cheditor();              // 에디터 개체를 생성합니다.
        myeditor.config.editorHeight = '340px';     // 에디터 세로폭입니다.
        myeditor.config.editorWidth = '100%';       // 에디터 가로폭입니다.
        myeditor.inputForm = 'editor';             // 위에 있는 textarea의 id입니다. 주의: name 속성 이름이 아닙니다.
        myeditor.run();                             // 에디터를 실행합니다.

        $('.file_put_btn').on('click' , function () {
            $(this).siblings('.upload-hidden').click();
        });
        $('.upload-hidden').on('change' , function () {
            var board_file_no = $(this).siblings('.file_put_btn').attr('board_file_no');
            $(this).siblings('.change_board_file_no').val(board_file_no);
        });

        $('.cancel_file').on('click' , function () {
            $(this).siblings('.upload-hidden').val('');
            $(this).siblings('.upload-name').val('선택된 파일없음');
        });
        var fileTarget = $('.filebox .upload-hidden');
        fileTarget.on('change', function(){
            if(window.FileReader){
                var filename = $(this)[0].files[0].name;
            }else{
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }

            var point = filename.lastIndexOf('.');
            var temp_filetype = filename.substring(point+1,filename.length);
            var filetype = temp_filetype.toUpperCase()

            if (filetype == 'JPG' || filetype == 'GIF' || filetype == 'BMP'  || filetype == 'PNG')
            {

                $(this).siblings('.upload-name').val(filename);
            }
            else
            {
                alert("이미지 파일(JPG, GIF, BMP , PNG)만 올릴 수 있습니다.");
                //파일폼 초기화
                //obj.outerHTML = obj.outerHTML;   //file 개체를 초기화하는 부분
                return false;
            }

            //$(this).siblings('.upload-name').val(filename);
            //$(this).siblings('.delete-file').attr("checked", true);
            //$(this).siblings('.delete-file').css("display", "none" );
        });

        $('.btn_submit').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

          if(!myeditor.outputBodyHTML()){
                alert("내용을 입력해 주세요.");
                return;
            }
            myeditor.outputBodyHTML();


            $('form[name=theForm]').submit();
        });

        $('form[name=theForm]').ajaxForm({
            dataType: 'json',
            beforeSubmit: function(xhr){
                $(".btn_submit").prop("disabled", true).css('cursor', 'not-allowed');
                $('#loading_ajax').show();
            }, success: function(data, textStatus, jqXHR){
                $('#loading_ajax').hide();
                $(".btn_submit").prop("disabled", false).css('cursor', 'pointer');
                var bbs_id = $('input[name=bbs_id]').val();

                console.log(data);

                try{
                    switch(data.code){
                        case "401":
                            alert(data.message);
                            location.href = "/user/signin";
                            break;
                        case "402":
                            alert(data.message);

                            break;
                        case "200":
                            document.location.replace("/board/lists/"+bbs_id);
                            break;
                        default:
                            alert(data.message);
                            break;
                    }
                }catch(e){
                    //alert("알 수 없는 에러 입니다.1");
                }

                return;
            }, error: function(jqXHR, textStatus, errorThrown){
                $('#loading_ajax').hide();
                $(".btn_submit").prop("disabled", false).css('cursor', 'pointer')

                try{
                    var data = $.parseJSON(jqXHR.responseText);
                    if(typeof data.message != undefined){
                        alert(data.message);
                    }else{
                        alert("알 수 없는 에러 입니다.2");
                    }
                }catch(e){
                    alert("통신 중 에러가 발생하였습니다.");
                }
            },
        });

        $('.down_list a').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var board_file_no = $(this).attr('board_file_no');

           document.location.href = '/board/download/?board_file_no='+board_file_no;

        });

    });
</script>