$(document).ready(function(){
    //$.onchain_proc('msp/getRequests' , '' , request_list_callback , 'on');

    var auth_list = $.runsync(http_api_url + '/msp/getRequests' , '','json' ,false);
    request_list_callback(auth_list);

    $('.request_list input[name=all_chk]').on('change' , function () {
        if($(this).is(':checked')){
            $('.request_list input[name="request_chk[]"]').prop('checked' , true);
        }else{
            $('.request_list input[name="request_chk[]"]').prop('checked' , false);
        }
    });
    /* c8da2a3d678a02b7d9a44867846d7dae4c0cdc4a
    * */
    $('button.btn-approvals').on('click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var chk_len = $('.request_list input[name="request_chk[]"]:checked').length;
        if(chk_len < 1) alert('선택된 계정이 없습니다.');
        else {
            var chk_str = '';
            $('.request_list input[name="request_chk[]"]').each(function () {
                chk_str += $(this).is(':checked') ? true : false
                chk_str += ",";
            });
           // var params = {approvals : [chk_str.substring(0 , (chk_str.length -1))]}
           // console.log(params);
            $.onchain_proc('msp/approve' , 'approvals=['+chk_str.substring(0 , (chk_str.length -1))+']' , request_approve_callback , 'on');

        }
    });


});

function request_approve_callback(data){
    if(typeof(data) != "undefined"){
        if(data.resultCode == 0){
            var auth_commit_user = [];
            $('.request_list input[name="request_chk[]"]:checked').each(function () {
                auth_commit_user.push($(this).attr('accountId'));
            });
            //console.log(auth_commit_user);
            var commit_data = $.runsync('/user/userAuthCommit' , 'userAccounts=' + auth_commit_user , 'json' , true);
            alert('정상적으로 승인되었습니다.');
            document.location.reload();
        }else{
          alert(data.resultMessage);
          document.location.reload();
        }
    }
}


function request_list_callback(data){
    if(typeof(data) != "undefined"){
        var in_html = "";
        if(data.resultCode == 0){

            if(data.list.length > 0) {
                var num = data.list.length;
                $.each(data.list, function (key, val) {
                    in_html += "<tr>";
                    in_html += "<td><input type='checkbox' name='request_chk[]' value='1' accountId='"+ val.requester.substr(2) +"'></td>";
                    in_html += "<td>"+ num +"</td>";
                    in_html += "<td>"+val.requester.substr(2)+"</td>";
                    in_html += "<td>"+authoriryRequestArr[val.role]+"</td>";
                    in_html += "<tr>";
                    num--;
                });
            }else{

                in_html += "<tr>";
                in_html += "<td colspan='4' style='text-align:center;'>사용권한 요청한 내역이 없습니다.</td>";
                in_html += "</tr>";
            }


        }else{
            in_html += "<tr>";
            in_html += "<td cospan='4'>데이터를  가져오는데 실패하였습니다..</td>";
            in_html += "</tr>";
            alert('resultCode : ' + data.resultCode + "\n\nMessage : " + data.resultMessage);
        }
        $('request_list tbody .loading').fadeOut();
        $('.request_list  tbody').html(in_html);
    }
}