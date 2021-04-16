$(document).ready(function(){

    $("form.validate-form button.btn-submit").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        $('form.validate-form').submit();
    });

/*    var validate_form = new commonFormValidation({
        form:".validate-form",
    });*/

    $("form.validate-form").submit(function(event){
        event.stopPropagation();
        event.preventDefault();

       /* var form_obj = $(this);
        if(!validate_form.formValidate(true)){
            return;
        }*/
        $.ajax({
            url: "/contents/form_submit",
            type: "POST",
            dataType: "json",
            timeout: 30000,
            cache: false,
            data: $('form.validate-form').serialize(),
            success: function(data, textStatus, jqXHR){
                try{
                    switch(data.code){
                        case "401":
                            alert(data.message);
                            location.href = "/user/signin";
                            break;
                        case "200":
                            var url = "http://localhost:54777";
                            var json_add = "";
                            $.each(data.data.fileinfo.rows , function (key , val) {

                                var json_params = {
                                    "contents_no" : val.contents_id,
                                    "contents_file_no" : val.contents_file_id,
                                    "user_id" : val.userid,
                                    "next_val" : data.data.next_val,
                                    "c_name" : $('input[name=cont_name]').val(),
                                    "file_no"  : val.sort,
                                    "file_size" : val.realsize,
                                    "file_path" : val.folder +'\\'+ val.filename,
                                    "c_type" : "v",
                                    "file"    : ""
                                }

                                json_add += JSON.stringify(json_params) + "#";
                            });
                            var params = {u:json_add.substring(0 , (json_add.length -1))}
                            //console.log(params);
                            $.runsync(url, params, 'html', true);
                            //document.location.reload();
                            break;
                        default:
                            alert(data.message);
                            break;
                    }
                }catch(e){
                    alert("알 수 없는 에러 입니다.");
                }
                return;
            }
        });
    });


    $('.btn-upload').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        var upload_type = $(this).attr('upload-type');


        $.ajax({
            url: "http://localhost:54777",
            type: "get",
            dataType: "text",
            timeout: 30000,
            cache: false,
            data: 'f='+upload_type,
            beforeSend: function( xhr ){
                //if(loading != undefined && loading == "on") $('#loading_ajax').show();
            }, complete: function( jqxhr, textStatus ){
                //if(loading != undefined && loading == "on") $('#loading_ajax').hide();
            }, success: function(data, textStatus, jqXHR){

                var replaceData = data.replace('},]}','}]}');
                replaceData = replaceData;//replaceData.replace(/\\/ig,"\\\\");
                jsonData = JSON.parse(replaceData);
                if(jsonData.result == 'S'){
                    $.complete_upload_list(jsonData, textStatus, jqXHR);
                }else{
                    alert(upload_type == 'C' ? "선택된 파일이 없습니다." : "선택된 폴더에 파일이 없습니다.");
                }
            }, error : function(xhr){
                $('#loading_ajax').hide();
                alert(xhr.responseText);
                //console.log(xhr.responseText);
            }
        });
    });

    $("form.down-form button.btn-submit").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        $('form.down-form').submit();
    });

    /*    var validate_form = new commonFormValidation({
            form:".validate-form",
        });*/

    $("form.down-form").submit(function(event){
        event.stopPropagation();
        event.preventDefault();

        if(confirm('파일을 다운로드 하시겠습니까?')) {

            var contents_no = $("form.down-form input[name=contents_no]").val();
            var next_val = $("form.down-form input[name=next_val]").val();
            var user_id = $('form.down-form input[name=user_id]').val();

            var json_add = "";
            var url = "http://localhost:54777";
            $("ul.file_list  li").each(function (i) {

                var  json_params = {
                    "contents_no": contents_no,
                    "contents_file_no": $(this).attr("contents_file_no"),
                    "file_name": $(this).attr("file_name"),
                    "file_size": $(this).attr("file_size"),
                    "user_id": user_id,
                    "next_val": next_val,
                    "file_no": String(parseInt($(this).attr("file_no")) + 1),
                    "del_yn": $(this).attr("del_yn"),
					"file_cnt" :$("ul.file_list  li").length
                }
                json_add += JSON.stringify(json_params) + "#";
            });
            var params = {d:json_add.substring(0 , (json_add.length -1))}
            //console.log(params);
            $.runsync(url, params, 'html', true);
            //document.location.reload();
        }
    });

    $.complete_upload_list = function(data){
        if(typeof(data) != "undefined"){
            var in_html = "";
            //console.log(data);
            if(data.result =='S'){
                if(data.folderpath){
                    $("input[name=folderpath]").val(data.folderpath)
                    $.each(data.fileinfo ,function (key , val) {

                        var spl_filepath = val.filepath.split('\\');
                        var add_val = val.filepath + "|&|" + val.filesize;
                        in_html += "<li class=\"target\"><input type=\"hidden\" name=\"fileinfo[]\" value=\""+ add_val +"\">"+ spl_filepath[spl_filepath.length - 1]+"</li>";
                    });
                }else{
                    //console.log(data.filepath);
                    var spl_filepath = data.filepath.split('\\');
                    var  add_val = data.filepath + "|&|" + data.filesize;

                    //console.log(add_val);
                    in_html += "<li class=\"target\"><input type=\"hidden\" name=\"fileinfo[]\" value=\"" + add_val +"\">"+ spl_filepath[spl_filepath.length - 1]+"</li>";
                }

                $('.filelist').append(in_html);
            }
        }
    }



    $(document).on('click' , '.filelist li', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('.filelist li').removeClass('target_click');
        $(this).addClass('target_click');
    });

    $.runsync = function (url , data ,type , debug) {

        var returnData = null;
        $url = url;
        $postData  = data;  // form의 데이터를 ajax데이터 호출에 사용할수 있도록 생성
        // Ajax를 통한 데이터 요청
        console.log(data);
        jQuery.ajax({
            type:'POST',
            url:$url,
            data:$postData,
            dataType:type,
            timeout: 1000,
            cache: false,
            async:false,
            beforeSend: function(xhr) {
                xhr.withCredentials = true;
            },success:function(obj){
                returnData = obj;
            },error:function(xhr,textStatus,errorThrown){
                // alert('An error occurred! '+(errorThrown ? errorThrown : xhr.status));
            },complete: function(jqXHR,textStatus){
                //alert(jqXHR.statusText);
            }
        });
        if(debug) console.log(returnData)
        return returnData;
    }

});

function sleep (delay) {
    var start = new Date().getTime();
    while (new Date().getTime() < start + delay);
}








