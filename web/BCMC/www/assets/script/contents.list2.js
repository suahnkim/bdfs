$(document).ready(function () {
    $.packaging_list();
});

$(document).on('click' , '.contents_model_view a' , function (event) {
    event.stopPropagation();
    event.preventDefault();
    $('.contents_title').html('콘텐츠상세정보');
    $('.contentDownForm').empty();
    $('.data-loading').removeClass('loading-overlay').addClass('loading-overlay-showing');


    var params = {next_val : $(this).parents('tr').attr('next_val') , all_data : ''};
    var data = $.runsync('/contents/getAjaxContentsDown', params, 'json', true);
    if(data.result){

        setTimeout(function () {
            $('.data-loading').removeClass('loading-overlay-showing').addClass('loading-overlay');
            $('.contents_title').html(data.title);
            $('.contentDownForm').html(data.contents_html);
        } , 800);
    }else{
        $('.modal-dismiss').click();
        commonMessage({
            title: 'Error',
            message: data.contents_html,
            type: 'error',
            addclass: 'stack-bar-bottom',
            stack: stack_bar_bottom,
        });
    }
});


$(document).on('click' , '#ajaxpaging li.page-item' , function (event) {
    event.stopPropagation();
    event.preventDefault();
    var pageNum = $(this).attr('pagenum');
    console.log(pageNum);
    if(typeof pageNum != 'undefined'){
        PagingHelper.gotoPage(pageNum);
        $('.tr_list').fadeOut('fast');
        $('tr.page_num' + pageNum).fadeIn();
    }
});


var curPage = 31;
var list_page = 20;
$.packaging_list =  function (page) {
    var params = {
        next_val : '',
        all_data : 'A',
        cur_page : curPage,
        ist_cnt : list_page,
        from_date : '',
        to_date : '',

    }
    var data = $.runsync('/contents/packagingList' , params , 'json' , true);


    var contents_html = '';
    var display = '';
    if(data.result){
         if(data.data.req_data.length > 0) {
            $("#ajaxpaging").append(PagingHelper.pagingHtml(data.data.result_cnt));
            $(data.data.req_data).each(function (key , val) {
                var num = data.data.req_data.length - key;
                var _class = key % 2 == 0 ? 'odd' : 'even';

                var page_num = Math.floor(key / 20) + 1;

                if(curPage == page_num)  display = 'block';
                else display = 'none';

                display = '';

                filesize = 0;
                if(val.info_data.length > 0 ) {
                    $(val.info_data).each(function (key , val) {
                        if(val.down_file_size){
                            filesize += val.down_file_size;
                        }
                    });
                }else{
                    filesize = 0;
                }

                contents_html += "<tr class=\""+_class+" tr_list page_num"+ page_num +"\" next_val=\"59bfa4dd-0760-4697-bac6-3d368a642f04\" user_id=\""+$('.profile-info .name').text()+"\"  style='display: "+display+"'>\n" +
                    "                       <td>"+num+"</td>\n" +
                    "                       <td style=\"text-align:left;\" class='contents_model_view'><a href='#modalAnim' class=\"mb-1 mt-1 mr-1 modal-with-zoom-anim ws-normal model-btn\">"+val.content_name+"</a></td>\n" +
                    "                       <td>"+ formatBytes(filesize) +"</td>\n" +
                    "                       <td>음악</td>\n" +
                    "                       <td class=\"status\">"+package_status_arr[val.status]+"</td>\n" +
                    "                   </tr>";
            });
        }
    }else{
        contents_html = "<tr><td colspan='4'>검색된 데이터가 없습니다.</td></tr>";
    }

    console.log(contents_html);

    $('tbody.list_area').html(contents_html);
    $('.modal-with-zoom-anim').magnificPopup({
        type: 'inline',

        fixedContentPos: true,
        fixedBgPos: true,

        overflowY: 'auto',

        closeBtnInside: true,
        preloader: false,

        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        modal: true
    });

    //var aa = $.runsync(http_api_url + '/list' , '' , 'json' , true);

}