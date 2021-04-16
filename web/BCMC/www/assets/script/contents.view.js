$(document).ready(function () {
    /*********************************************** 다운로드 ************************************/
    $.initDownStatus();

    $("button.btn-download").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        $('form.down-form').submit();
    });

    /*    var validate_form = new commonFormValidation({
            form:".uploadForm",
        });*/

    $("form.down-form").submit(function(event){
        event.stopPropagation();
        event.preventDefault();
        var status = $('form.down-form input[name=status]').val();
        if(status != 3){
            alert('패키징 진행중입니다.\n\n퍄키징 완료후 다운로드 할 수 있습니다.');
            return false;
        }

        if(confirm('파일을 다운로드 하시겠습니까?')) {

            var contents_no = $("form.down-form input[name=contents_no]").val();
            var next_val = $("form.down-form input[name=next_val]").val();
            var user_id = $('form.down-form input[name=user_id]').val();
            var contents_ccid = $('form.down-form input[name=contents_ccid]').val();
            var contents_version = $('form.down-form input[name=contents_version]').val();
            var sub_path = "";
            var payment_id = $('form.down-form input[name=payment_id]').val();
            var file_path = $('form.down-form input[name=file_path]').val();
            var file_type = $('form.down-form input[name=file_type]').val();
            var file_size = $('form.down-form input[name=file_size]').val();

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
                    "file_no": String(parseInt($(this).attr("file_no"))),
                    "del_yn": $(this).attr("del_yn"),
                    "total_cnt" : String($("ul.file_list  li").length),
                    "dev" : _dev,
                 /*   "contents_ccid" : contents_ccid,
                    "contents_version"  : contents_version,
                    "sub_path" : sub_path,
                    "payment_id" : payment_id,
                    "file_path" : $(this).attr('file_path') ,
                    "file_size" : String(file_size),
                    "file_type" : file_type == "Y" ? '1' : '0' ,*/

                }
                json_add += JSON.stringify(json_params) + "#";
                //console.log(json_add);
            });

            var params = {d:json_add.substring(0 , (json_add.length -1))}

            $.runsync(url, params, 'html', true);
            //document.location.reload();
        }
    });


    $(document).on('click' , '.filelist li', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('.filelist li').removeClass('target_click');
        $(this).addClass('target_click');
    });

});

$.initDownStatus = function(){
    var params = {
        next_val : $('form.down-form input[name=next_val]').val(),
        user_id : $('form.down-form input[name=user_id]').val(),
    }
    var json_data = $.runsync('/contents/getContentsPackageState' , params, 'json' , true);

    if(json_data.data != 'undefined') {
        console.log(json_data.data);
        if (json_data.code == 200 && json_data.data.status != 'undefined') {
            $('form.down-form input[name=status]').val(json_data.data.status);
        } else {
            $('form.down-form input[name=status]').val(2);
        }
    }
}