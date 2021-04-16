<div class="contents">
    <p class="mytit"><img src="<?php echo COM_ASSETS_PATH; ?>/img/customer_tit.png" alt=""> 고객센터</p>
    <div class="table_wrap">
        <ul class="cutomer_tab">
            <li onclick="location.href='/board/lists/notice'">공지사항</li>
            <li class="on" >1:1 문의</li>
            <li onclick="location.href='/board/lists/qna'">내 문의 내역</li>
        </ul>
        <table  class="sty02">
            <form name="theForm" method="post" action="/board/form_submit" enctype="multipart/form-data">
                <input type="hidden" name="bbs_id" value="<?php echo($data->board_config->bbs_id)?>">
                <input type="hidden" name="board_id" value="<?php echo(@$data->board_id)?>">
                <input type="hidden" name="act" value="<?php echo($request_params->act)?>">
            <colgroup>
                <col width="25%"/><col width="75%"/>
            </colgroup>
            <tr>
                <th><?php echo($data->board_config->ca_title)?></th>
                <td>
                    <div class="custom-select" style="width:250px; margin:0">
                        <select name="ca_name">
                            <?php
                            $ca_option_exe = explode('|&|' ,$data->board_config->ca_name);
                            foreach($ca_option_exe as $key=>$val){
                            ?>
                                <option value="<?php echo($val)?>"><?php echo($val)?></option>
                            <?php } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <th>제목</th>
                <td><input type="text" name="subject" class="text01" placeholder="제목을 입력해 주세요."/></td>
            </tr>
            <tr>
                <th>문의내용</th>
                <td><textarea name="contents" id="editor" placeholder="내용을 입력해 주세요."></textarea></td>
            </tr>
            <?php if($data->board_config->file_use == 'Y'){ ?>
            <tr>
                <th>파일 첨부</th>
                <td class="file_list">
                    <p class=".filebox">
                        <input type="hidden" name="change_board_file_no[]" class="change_board_file_no" value="">
                        <input type="file" id="" name="filename[]" class="upload-hidden" style="display:none;" accept="image/*">
                        <input type="text" name="" class="text01 upload-name" style="width:300px;" disabled="disabled"/> <button class="btn_graybox file_put_btn" type="button">파일선택</button><button class="btn_whitebox02 cancel_file" style="height:34px;" type="button">- 선택파일 초기화</button> </p>
                    <p class="smalltxt">파일첨부는 jpg, png 파일만 가능하며 10MB까지 등록이 됩니다. </p>
                </td>
            </tr>
            <?php } ?>
            </form>
        </table>
        <div class="inquiry_btnbox">
            <button type="submit" class="btn_inquiry btn_submit">등록하기</button>
        </div>
    </div>
</div>
<!--<script type="text/javascript" src="/assets/cheditor/cheditor.js"></script>-->
<script>
    $(document).ready(function () {
        $('.file_put_btn').on('click' , function () {
            $(this).siblings('.upload-hidden').click();
        });
        $('.upload-hidden').on('change' , function () {
            //var board_file_no = $(this).siblings('.file_put_btn').attr('board_file_no');
            //$(this).siblings('.change_board_file_no').val(board_file_no);
            if(window.FileReader){
                var filename = $(this)[0].files[0].name;
            }else{
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            console.log(filename);
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

        })

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
            console.log(filename);
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
            if(!$('input[name=subject]').val()){
                alert('제목을 입력해 주세요');
                $('input[name=subject]').focus();
                return false;
            }

            if(!$('textarea[name=contents]').val()){
                alert('내용을 입력해 주세요');
                $('textarea[name=contents]').focus();
                return false;
            }
            /*if(!myeditor.outputBodyHTML()){
                alert("내용을 입력해 주세요.");
                return;
            }
            myeditor.outputBodyHTML();*/


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
                        /*case "302":
                           alert(data.message);
                           location.href = "/board/bview/"+$("input[name=board_code]").val()+"/"+$("input[name=board_id]").val();
                           break;*/
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


    });
</script>
<script>

/*
    var myeditor = new cheditor();              // 에디터 개체를 생성합니다.
    myeditor.config.editorHeight = '340px';     // 에디터 세로폭입니다.
    myeditor.config.editorWidth = '100%';       // 에디터 가로폭입니다.
    myeditor.inputForm = 'editor';             // 위에 있는 textarea의 id입니다. 주의: name 속성 이름이 아닙니다.
    myeditor.run();                             // 에디터를 실행합니다.
*/

    var x, i, j, selElmnt, a, b, c;
    /*look for any elements with the class "custom-select":*/
    x = document.getElementsByClassName("custom-select");
    for (i = 0; i < x.length; i++) {
        selElmnt = x[i].getElementsByTagName("select")[0];
        /*for each element, create a new DIV that will act as the selected item:*/
        a = document.createElement("DIV");
        a.setAttribute("class", "select-selected");
        a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
        x[i].appendChild(a);
        /*for each element, create a new DIV that will contain the option list:*/
        b = document.createElement("DIV");
        b.setAttribute("class", "select-items select-hide");
        for (j = 1; j < selElmnt.length; j++) {
            /*for each option in the original select element,
            create a new DIV that will act as an option item:*/
            c = document.createElement("DIV");
            c.innerHTML = selElmnt.options[j].innerHTML;
            c.addEventListener("click", function(e) {
                /*when an item is clicked, update the original select box,
                and the selected item:*/
                var y, i, k, s, h;
                s = this.parentNode.parentNode.getElementsByTagName("select")[0];
                h = this.parentNode.previousSibling;
                for (i = 0; i < s.length; i++) {
                    if (s.options[i].innerHTML == this.innerHTML) {
                        s.selectedIndex = i;
                        h.innerHTML = this.innerHTML;
                        y = this.parentNode.getElementsByClassName("same-as-selected");
                        for (k = 0; k < y.length; k++) {
                            y[k].removeAttribute("class");
                        }
                        this.setAttribute("class", "same-as-selected");
                        break;
                    }
                }
                h.click();
            });
            b.appendChild(c);
        }
        x[i].appendChild(b);
        a.addEventListener("click", function(e) {
            /*when the select box is clicked, close any other select boxes,
            and open/close the current select box:*/
            e.stopPropagation();
            closeAllSelect(this);
            this.nextSibling.classList.toggle("select-hide");
            this.classList.toggle("select-arrow-active");
        });
    }
    function closeAllSelect(elmnt) {
        /*a function that will close all select boxes in the document,
        except the current select box:*/
        var x, y, i, arrNo = [];
        x = document.getElementsByClassName("select-items");
        y = document.getElementsByClassName("select-selected");
        for (i = 0; i < y.length; i++) {
            if (elmnt == y[i]) {
                arrNo.push(i)
            } else {
                y[i].classList.remove("select-arrow-active");
            }
        }
        for (i = 0; i < x.length; i++) {
            if (arrNo.indexOf(i)) {
                x[i].classList.add("select-hide");
            }
        }
    }
    /*if the user clicks anywhere outside the select box,
    then close all select boxes:*/
    document.addEventListener("click", closeAllSelect);
</script>