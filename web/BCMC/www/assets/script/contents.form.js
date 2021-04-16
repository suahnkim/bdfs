$(document).ready(function(){
    var total_size = 0;
    var total_cnt = 0;



    $("form.uploadForm button.btn-submit").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        var inDataParams = {inData : $("input[name=user_id]").val() }

        var inDataJson = $.runsync(http_api_url +'/dsa/sign', inDataParams, 'json', true);

        if(inDataJson.resultCode == 0){
            var id_sign_params = inDataJson.signature.sign +'|&|'+ inDataJson.signature.pubKey;
            $('input[name=id_sign]').val(id_sign_params);
            $('form.uploadForm').submit();
        }
    });

    $("form.modifyUploadForm button.btn-submit").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        var inDataParams = {inData : $("input[name=user_id]").val() }

        var inDataJson = $.runsync(http_api_url +'/dsa/sign', inDataParams, 'json', true);

        if(inDataJson.resultCode == 0){
            var id_sign_params = inDataJson.signature.sign +'|&|'+ inDataJson.signature.pubKey;
            $('input[name=id_sign]').val(id_sign_params);
            $('form.modifyUploadForm').submit();
        }
    });

    /*    var validate_form = new commonFormValidation({
            form:".uploadForm",
        });*/

    $("form.uploadForm").submit(function(event){
        event.stopPropagation();
        event.preventDefault();



        if($('ul.multi_img_list li').length > 0){
            var sub_img_arr = [];
            $('ul.multi_img_list li').each(function () {
                //var tmp_filepath = $(this).attr('tmp-filepath');
                //var tmp_filesize = $(this).attr('tmp-filesize');

                sub_img_arr.push($(this).attr('tmp-filepath') +'|'+$(this).attr('tmp-filesize') + '|' + $(this).attr('tmp-width') + '|' + $(this).attr('tmp-height') + '|' + $(this).attr('tmp-filename'));
            });

            $('input[name=sub_img]').val(sub_img_arr);
        }
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
            data: $('form.uploadForm').serialize(),
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
                            var file_path = '';
                            var json_params = "";
                            $.each(data.data.fileinfo.rows , function (key , val) {

                                if(val.folder)  file_path = val.folder +'\\'+ val.filename;
                                else file_path = val.filename;

                                json_params = {
                                    "contents_no" : val.contents_id,
                                    "contents_file_no" : val.contents_file_id,
                                    "user_id" : val.userid,
                                    "next_val" : data.data.next_val,
                                    "c_name" : $('input[name=cont_name]').val(),
                                    "file_no"  : val.sort,
                                    "file_size" : val.realsize,
                                    "file_path" : file_path,
                                    "c_type" : "v",
                                    "file"    : "",
                                    "dev"   : _dev,
                                }

                                json_add += JSON.stringify(json_params) + "#";
                            });
                            var params = {'u' : json_add.substring(0,(json_add.length -1))}

                            $.runsync(url, params, 'html', true);
                            document.location.href='/contents/lists/P';
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

    $("form.modifyUploadForm").submit(function(event){
        event.stopPropagation();
        event.preventDefault();

        $.ajax({
            url: "/contents/modify_form_submit",
            type: "POST",
            dataType: "json",
            timeout: 30000,
            cache: false,
            data: $('form.modifyUploadForm').serialize(),
            success: function(data, textStatus, jqXHR){
                //console.log(data); return false;

                try{
                    switch(data.code){
                        case "401":
                            alert(data.message);
                            location.href = "/user/signin";
                            break;
                        case "200":
                            var response = '';
                            if( data.data.response ) {
                                response = data.data.response;
                            }

                            if( response ) {
                                var url = "http://localhost:55442/modify/data";
                                //var url = "https://localhost:55443/modify/data";
                                var params = {
                                    'dataid': response.dataid,
                                    'info': response.info
                                };

                                $.runsync(url, params, 'html', true);

                                alert('컨텐츠가 수정되었습니다.');
                                document.location.href='/contents/lists/P';
                            }
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


    $('.btn-upload').unbind('click').bind('click' , function (event) {
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
                console.log(data);
                var replaceData = data.replace('},]}','}]}');
                replaceData = replaceData;//replaceData.replace(/\\/ig,"\\\\");
                jsonData = JSON.parse(replaceData);
                if(jsonData.result == 'S'){
                    $.complete_upload_list(jsonData, textStatus, jqXHR);
                }else{
                    //alert(upload_type == 'C' ? "선택된 파일이 없습니다." : "선택된 폴더에 파일이 없습니다.");
                }
            }, error : function(xhr){
                $('#loading_ajax').hide();
                alert(xhr.responseText);
                //console.log(xhr.responseText);
            }
        });


    });


    var imgBtn_click = false;
    $('.main-img-btn').unbind('click').bind('click' , function (e) {
        var exec = $(this).attr('data-exec');
        if( exec === 'N' ) {
            e.preventDefault();
            return false;
        }

        var full = window.location.host
        var parts = full.split('.');
        var subdomain = parts[0] != 'www' && parts[0] != '' ? '&subdomain=' +parts[0] : '';

        if(!imgBtn_click){
            imgBtn_click= true;

            var in_html = "";
            var sel_type = $(this).attr('sel_img_type');
            $.ajax({
                url: "http://localhost:54777",
                type: "get",
                dataType: "text",
                timeout: 9000000,
                cache: false,
                data: 'i='+ sel_type + subdomain,
                beforeSend: function( xhr ){
                    //if(loading != undefined && loading == "on") $('#loading_ajax').show();
                }, complete: function( jqxhr, textStatus ){
                    //if(loading != undefined && loading == "on") $('#loading_ajax').hide();
                }, success: function(data, textStatus, jqXHR){
                    console.log(data);

                    imgBtn_click= false;
                    if(sel_type == 'S'){

                        replaceData = data.replace('},','}');
                        jsonData = JSON.parse(replaceData);


                        if(typeof jsonData.filepath != 'undefined'){
                            var fileObj = jsonData.filepath;
                            var pathHeader = fileObj.lastIndexOf("\\");
                            var pathMiddle = fileObj.lastIndexOf(".");
                            var pathEnd = fileObj.length;
                            var fileName = fileObj.substring(pathHeader+1, pathMiddle);
                            var extName = fileObj.substring(pathMiddle+1, pathEnd);
                            var allFilename = fileName+"."+extName;
                            var img_info = jsonData.fileinfo.split(',');
                            var wid = img_info[0] || 0;
                            var hei = img_info[1] || 0;

                            $('input[name=main_img]').val(jsonData.filepath+'|'+ jsonData.filesize + '|' + wid +'|'+ hei + '|' + jsonData.filename);
                            $('.main_filename').html(allFilename +"<br>"+ formatBytes(jsonData.filesize));
                            if(typeof jsonData.filename != 'undefined'){
                                $('.main_filename').html("<img src='"+ jsonData.filename+"' style='width:100%;height:100%;overflow:hidden;display:flex;;justify-content:center;align-items:center;'>").fadeIn('slow');
                            }
                        }

                    }else{
                        var replaceData = data.replace('},]}','}]}');
                        //replaceData = replaceData.replace('}{,','},{');
                        replaceData = replaceData.replace(/\//ig,"\\/");
                        jsonData = JSON.parse(replaceData);

                        if(jsonData.result == 'S'){
                            $.each(jsonData.fileinfo , function (key , val) {

                                var fileObj = val.filepath;
                                var pathHeader = fileObj.lastIndexOf("\\");
                                var pathMiddle = fileObj.lastIndexOf(".");
                                var pathEnd = fileObj.length;
                                var fileName = fileObj.substring(pathHeader+1, pathMiddle);
                                var extName = fileObj.substring(pathMiddle+1, pathEnd);
                                var allFilename = fileName+"."+extName;
                                var img_info = val.fileinfo.split(',');
                                var wid = img_info[0] | 0;
                                var hei = img_info[1] | 0;


                                in_html += "<li style='width:120px;height:100px;float:left;margin:10px;border-radius: 5px;background:#ddd;text-align:center;position:relative;overflow:hidden;display:flex;;justify-content:center;align-items:center;'  tmp-filepath='"+val.filepath+"'  tmp-filesize='"+val.filesize+"'  tmp-width='"+ wid+"' tmp-height='"+ hei+"' tmp-filename='"+ val.filename  +"'>" +
                                    "<span style='width:120px;'><img src='"+val.filename+"' style='width:100px;height:auto;'></span>" +
                                    /*"<div style='position:absolute;bottom:0;height:50px;background: #000;'><span style='width:100%;float:left;ext-overflow:ellipsis;white-space:nowrap;overflow:hidden;'>"+allFilename+"</span>" +
                                    "<span style='width:100%;'>"+ formatBytes(val.filesize) +"</span>" +
                                    "<span style='width:100%;;display:none;'>삭제</span></div>" +*/
                                    "</li>";
                            });
                            $('ul.multi_img_list').html(in_html).fadeIn('slow');
                        }
                    }

                    // }else{

                    // }
                }, error : function(xhr){
                    $('#loading_ajax').hide();
                    alert(xhr.responseText);
                    //console.log(xhr.responseText);
                }
            });
        }
    });

    $.complete_upload_list = function(data){

        if(typeof(data) != "undefined"){
            var in_html = "";

            if(data.result =='S'){

                if(data.folderpath){

                    $("input[name=folderpath]").val(data.folderpath);
                    $.each(data.fileinfo ,function (key , val) {
                        var spl_filepath = val.filepath.split('\\');
                        var add_val = val.filepath + "|&|" + val.filesize;

                        in_html += "<li class=\"target filelist_item col-sm-12\" ><div class='col-sm-10' style='width:80%;float:left;text-align:left'><input type=\"hidden\" name=\"fileinfo[]\" value=\""+ add_val +"\">"+ spl_filepath[spl_filepath.length - 1]+"</div><div class='col-sm-2' style='width:20%;float:left;text-align:right;'>"+ formatBytes(val.filesize)+"</div></li>";

                        total_size += parseInt(val.filesize);
                        total_cnt++;
                    });
                }else{
                    //console.log(data.filepath);
                    var spl_filepath = data.filepath.split('\\');
                    var add_val = data.filepath + "|&|" + data.filesize;

                    //console.log(add_val);

                    in_html += "<li class=\"target filelist_item col-sm-12\" ><div class='col-sm-10' style='width:80%;float:left;text-align:left'><input type=\"hidden\" name=\"fileinfo[]\" value=\""+ add_val +"\">"+ spl_filepath[spl_filepath.length - 1]+"</div><div class='col-sm-2' style='width:20%;float:left;text-align:right;'>"+ formatBytes(data.filesize)+"</div></li>";
                        //in_html += "<li class=\"target\"><input type=\"hidden\" name=\"fileinfo[]\" value=\"" + add_val +"\">"+ spl_filepath[spl_filepath.length - 1]+"</li>";

                    total_size += parseInt(data.filesize);
                    total_cnt++;
                }

                $('.file_size').html(formatBytes(parseInt(total_size)));
                $('.file_cnt').html(total_cnt);
                $('.filelist').append(in_html);
            }
        }
    }

    $('.cast_item_add_btn').on('click', function(e) {
        e.preventDefault();

        var cast_items = $('.cast_items');

        var html = ''+
            '<div class="cast_item row col-sm-12 pt-4 pl-0 pr-0">'+
            '   <label class="col-sm-2 control-label text-sm-right pt-2">&nbsp;</label>'+
            '   <div class="col-sm-10 pr-0">'+
            '       <div class="col-sm-12 pr-0"><input type="text" name="cast_name[]" class="form-control" placeholder="배우 이름"></div>'+
            '       <div class="col-sm-12 pt-2 pr-0"><input type="text" name="cast_cast_name[]" class="form-control" placeholder="배역 이름"></div>'+
            '   </div>'+
            '</div>'+
        '';

        cast_items.append(html);
    });

    $('.crew_item_add_btn').on('click', function(e) {
        e.preventDefault();

        var cast_items = $('.crew_items');

        var html = ''+
            '<div class="crew_item row col-sm-12 pt-4 pl-0 pr-0">'+
            '   <label class="col-sm-2 control-label text-sm-right pt-2">&nbsp;</label>'+
            '   <div class="col-sm-10 pr-0">'+
            '       <div class="col-sm-12 pr-0"><input type="text" name="crew_name[]" class="form-control" placeholder="제작자 이름"></div>'+
            '       <div class="col-sm-12 pt-2 pr-0"><input type="text" name="crew_role[]" class="form-control" placeholder="제작자 역할"></div>'+
            '   </div>'+
            '</div>'+
        '';

        cast_items.append(html);
    });
});




