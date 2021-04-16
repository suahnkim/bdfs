<?php
$btn_html = "";
$btn_html .= "<button type=\"button\" class=\"listarrow prev_btn\" board_id='".$data->prev_board_id."'><i class=\"fas fa-sort-up\" style=\"padding-top:10px;\"></i></button>";
$btn_html .= "<button type=\"button\" class=\"listarrow next_btn\" board_id='".$data->next_board_id."'><i class=\"fas fa-sort-down\" style=\"padding-bottom:5px;\"></i></button>";
$btn_html .= "<button class=\"btn_white btn_list\">목록</button>";

?>
<p class="subTit"><?php echo($data->board_config->bbs_name)?> 상세보기</p>
<div class="btnbox_right">
    <?php echo($btn_html);?>
</div>
<table class="sty02">
    <tr>
        <td>
            <p class="viewtit"><?php echo($data->subject)?></p>
            <div class="viewsbox">번호: <?php echo($data->board_id)?></div><div class="viewline"></div><div class="viewsbox">등록일 : <?php echo($data->regdate)?></div>
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
    <?php } ?>
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
    <?php if(@$data->reply_data->board_id){ ?>
    <tr>
        <td reply_board_id="<?php echo($data->reply_data->board_id)?>" class="down_list">
            <div class="viewsbox"><span class="txt_black">답변</span></div><div class="viewline"></div><span class="viewsbox">작성일 : <?php echo($data->reply_data->regdate)?></span><span><button type="button" class="btn_red01 reply_delete_btn">삭제</button><button  type="button" class="btn_blue02 reply_modify_btn">수정</button></span><div class="float_right">
                <?php if(@count($data->reply_data->file_rows) > 0){ foreach($data->reply_data->file_rows as $key=>$val){ ?>
                    <p><i class="fas fa-paperclip"></i> 첨부파일 : <a href="javascript:;" style="color:#333" board_file_no="<?php echo($val->board_file_no)?>"><?php echo($val->file_origin_name)?></a></p>
                <?php }} ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="qna_left txt_red">
                A
            </div>
            <div class="qna_right">
                <?php echo($data->reply_data->contents)?>
            </div>
        </td>
    </tr>
    <?php } ?>
</table>
<div class="btnbox_right" style="margin-top:10px">
    <?php echo($btn_html);?>
</div>
<p class="btnbox" ><button type="button" class="btn_blue03 delete_btn" style="margin-right:10px;" >삭제</button><button type="button"  class="btn_blue01 btn_modify">수정</button><?php if($data->board_config->reply == 'Y'){ ?><button type="button"  class="btn_green02 btn_reply">답글</button><?php } ?></p>

<script>
    var bbs_id = '<?php echo($request_params->bbs_id)?>';
    var board_id = '<?php echo($request_params->board_id)?>';
    var prev_board_id = '<?php echo($data->prev_board_id)?>';
    var next_board_id = '<?php echo($data->next_board_id)?>';
    $(document).ready(function () {
        $('.prev_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            if(prev_board_id) document.location.href = '/board/view/'+bbs_id+'/?board_id='+prev_board_id;
            else alert('이전게시글이 없습니다.');
        });
        $('.next_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            if(next_board_id) document.location.href = '/board/view/'+bbs_id+'/?board_id='+next_board_id;
            else alert('다음글이 없습니다.');
        });

        $('.btn_list').on('click' ,function (event) {
            event.stopPropagation();
            event.preventDefault();

            document.location.href = '/board/lists/'+bbs_id;
        });

        $('.delete_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var params = {board_id : board_id};
            var data = $.runsync('/board/delBoard' , params ,'json' , true);

        });

        $('.file_area img').each(function () {
            if($('.file_area').width() <= $(this).width() ) $(this).css({'width' : '100%'})
        });

        $('.delete_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            if(confirm('정말 삭제하시겠습니까?\n삭제된 자료는 복구 할 수 없습니다.')){
                var params = {board_id : board_id};
                var data = $.runsync('/board/delBoard' , params , 'json' , true);

                if(data.code == 200){
                    document.location.replace('/board/lists/'+bbs_id);
                }else{
                    alert(data.message);
                }
            }
        });

        $('.btn_modify').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            document.location.href = '/board/form/'+bbs_id+'/mod?board_id='+board_id;
        });

        $('.btn_reply').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            document.location.href = '/board/form/'+bbs_id+'/reply?board_id='+board_id;
        });
        $('.down_list a').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            var board_file_no = $(this).attr('board_file_no');

            document.location.href = '/board/download/?board_file_no='+board_file_no;

        });

        $('.reply_delete_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            if(confirm('정말 삭제하시겠습니까?\n삭제된 자료는 복구 할 수 없습니다.')){
                var params = {board_id : $(this).parents('td').attr('reply_board_id')};
                var data = $.runsync('/board/delBoard' , params , 'json' , true);
                if(data.code == 200){
                    document.location.reload();
                }else{
                    alert(data.message);
                }
            }
        });

        $('.reply_modify_btn').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            document.location.href = '/board/form/'+bbs_id+'/mod?board_id='+$(this).parents('td').attr('reply_board_id');
        });

    });
</script>