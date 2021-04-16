$(document).ready(function () {
    //$.packaging_list();
    //$("#ajaxpaging").append(PagingHelper.pagingHtml(1001));
    $('.table tbody tr').each(function () {

       if($(this).attr('package_status') != 3) {

           var params = {
               next_val: $(this).attr('next_val'),
               user_id: $(this).attr('user_id'),
           }
           var json_data = $.runsync('/contents/getContentsPackageState', params, 'json', false);


           if (json_data.data != 'undefined') {
               //console.log(json_data.data);
               if (json_data.code == 200 && json_data.data.status != 'undefined') {
                   $(this).find('.status').html(package_status_arr[json_data.data.status]);
               } else {
                   $(this).find('.status').text('오류');
               }
           }
       }

   });

    $('.product_commit_btn').on('click' , function () {

        var contents_id = $(this).attr('contents_id');
        $('#loading_ajax .loading_msg').text('상품등록중..');
        //$('#loading_msg').textbanner();
        var params = {contents_id : contents_id};
        var data = $.runsync('/contents/getContentsInfoProductData' , params , 'json' , false);

        $('#loading_ajax').fadeIn(function(){

            if(data.code == 200){

                var product_params = {
                    cid : data.data.data.cid,
                    ccid : data.data.data.ccid,
                    version : data.data.data.version,
                    info : data.data.data.info,
                    fee : parseInt(data.data.data.fee),
                    fileHasheLists :data.data.data.fileHashList ,
                    chunkLists : data.data.data.chunkList,
                    UsageRestriction : data.data.data.UsageRestriction,
                }

                $('.json_text').html(product_params);
                var return_data = $.runsync(http_api_url + '/register/data' ,product_params , 'json' , true);

                if(return_data.resultCode == 0 ){
                    var params_data = {contents_id : contents_id , dataid : return_data.dataId , metainfo : data.data.data.metainfo}
                    var  p_data = $.runsync('/contents/setModContentsInfo' , params_data , 'json' , true);
                    if(p_data.code == 200){
                        alert('정상적으로 상품등록되었습니다.');
                        $('#loading_ajax').fadeOut();
                        document.location.reload()
                    }
                }else{
                    commonMessage({
                        title: 'Error',
                        message: return_data.resultMessage,
                        type: 'error',
                        addclass: 'stack-bar-bottom',
                        stack: stack_bar_bottom,
                    });
                    $('#loading_ajax').fadeOut();
                }
            }else{
                commonMessage({
                    title: 'Error',
                    message: data.message,
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
                $('#loading_ajax').fadeOut();
            }
        });

    });

    $('.stop_publish_commit_btn').on('click' , function () {

        if( confirm('한번 배포가 중단되면, 더이상 사용할 수 없습니다.') ) {
            // true
        } else {
            // false            
            return ;
        }
        
        var contents_id = $(this).attr('contents_id');
        $('#loading_ajax .loading_msg').text('배포중지중..');
        //$('#loading_msg').textbanner();
        var params = {contents_id : contents_id};
        
        var data = $.runsync('/contents/getContentsInfoProductData' , params , 'json' , false);

        $('#loading_ajax').fadeIn(function(){

            if(data.code == 200){

                var product_params = {
                    // cid : data.data.data.cid,
                    // ccid : data.data.data.ccid,
                    // version : data.data.data.version,
                    // info : data.data.data.info,
                    // fee : parseInt(data.data.data.fee),
                    // fileHasheLists :data.data.data.fileHashList ,
                    // chunkLists : data.data.data.chunkList,
                    // UsageRestriction : data.data.data.UsageRestriction,
                    dataid : data.data.data.dataid,
                    delete_all_products : true,
                }

                $('.json_text').html(product_params);
                
                var return_data = $.runsync(http_api_url + '/revoke/data' ,product_params , 'json' , true);

                if(return_data.resultCode == 0 ){
                    var params_data = {contents_id : contents_id , stop_publish : 'Y'}
                    //var  p_data = $.runsync('/contents/setModContentsInfo' , params_data , 'json' , true);
                    var  p_data = $.runsync('/contents/setModContentsInfoStopPublish' , params_data , 'json' , true);
                    if(p_data.code == 200){
                        alert('정상적으로 배포 중지가 되었습니다.');
                        $('#loading_ajax').fadeOut();
                        document.location.reload()
                    }
                }else{
                    commonMessage({
                        title: 'Error',
                        message: return_data.resultMessage,
                        type: 'error',
                        addclass: 'stack-bar-bottom',
                        stack: stack_bar_bottom,
                    });
                    $('#loading_ajax').fadeOut();
                }
               
            }else{
                commonMessage({
                    title: 'Error',
                    message: data.message,
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
                $('#loading_ajax').fadeOut();
            }
        });

    });


    $('.packaging_download_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        if(confirm('파일을 다운로드 하시겠습니까?')) {
            var params = {contents_id : $(this).attr('contents_id')};
            var contents_data = $.runsync('/contents/contentsDownInfo' , params , 'json' , true);
            var user_id = $(this).parents('tr').attr('user_id');
            var next_val = $(this).parents('tr').attr('next_val');
            var json_add = '';
            if(contents_data.code == 200){
                $(contents_data.data.rows).each(function (key , val) {
                    var  json_params = {
                        "contents_no": val.contents_id,
                        "contents_file_no": val.contents_file_id,
                        "file_name": val.filename,
                        "file_size": val.realsize,
                        "user_id": user_id,
                        "next_val": next_val,
                        "file_no": String(parseInt(val.sort)),
                        "del_yn": 'N',
                        "total_cnt" : String(contents_data.data.rows.length),
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

                var down_params = {d:json_add.substring(0 , (json_add.length -1))};
                var url = "http://localhost:54777";

                $.runsync(url, down_params, 'html', true);
                setTimeout(function () {
                    document.location.reload();
                },1000);
            }else{
                alert('콘텐츠 정보를 가져오는데 실패하였습니다.');
            }
        }
    });
 });




$.packaging_list =  function () {
    var params = {
        next_val : '',
        all_data : 'C'
    }
    var data = $.runsync('/contents/packagingList' , params , 'json' , true);
    var contents_html = '';
    if(data.result){
        if(data.data.req_data.length > 0) {

            $(data.data.req_data.reverse()).each(function (key , val) {
                var num = data.data.req_data.length - key;
                var _class = key % 2 == 0 ? 'odd' : 'even';

                contents_html += "<tr class=\""+_class+"\" next_val=\"\" user_id=\"\" package_status=\"\">\n" +
                    "                    <td>"+num+"</td>\n" +
                    "                    <td style=\"text-align:left;\" >"+val.content_name+"</td>\n" +
                    "                    <td>12Mb</td>\n" +
                    "                    <td>음악</td>\n" +
                    "                    <td class=\"status\">"+package_status_arr[val.status]+"</td>\n" +
                    "                </tr>";
            });
        }
    }else{
        contents_html = "<tr><td colspan='4'>검색된 데이터가 없습니다.</td></tr>";
    }
    $('.list_area').html(contents_html);
}



