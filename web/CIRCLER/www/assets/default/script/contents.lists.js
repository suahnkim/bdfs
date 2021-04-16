$(document).ready(function () {
    var ll = new LazyLoad({
        threshold: 0
    });
});
var page = 2;
$(window).scroll(function () {
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {

        var contents_params = {pageNum : page , pageSize : 18  , srch_value : $('input[name=srch_value]').val() , srch_key : 'title' , segment : segment};

        var contents_data = $.runsync('/contents/ajaxContentsLists' ,contents_params , 'json' , false);
        if(contents_data.data.rows.length > 0){
            var contents_html = "";
            $(contents_data.data.rows).each(function (key ,val) {

                if(val.main_img && val.ccid_ver){
                    var exp_main_img = val.main_img.split('|');
                    //$exp_main_img = explode('\\' , $main_img);
                    if(val.main_img.indexOf('coimg.circler.co.kr') != -1){
                        var data_src  = val.main_img;
                    }else{
                        var data_src ="http://www.mediablockchain.co.kr" + exp_main_img[exp_main_img.length - 1];
                    }

                }else{
                    var data_src = "/assets/common/img/common/no-img.jpg";
                }
                var drm_html = '';
                var adult_html = '';

                var user_is_adult  = getCookie('is_adult');
                if(val.is_adult == 'Y' && user_is_adult == 'N') adult_html = '<div class="adult"></div>';
                if(val.is_adult == 'Y' && user_is_adult == 'Y') adult_html = '<div class="adultlogin"></div>';

                if(val.drm == 'Y') drm_html = '<div class="drm">DRM</div>';

                contents_html += '<li class="pic" contents_id="'+val.contents_id+'">\n'+ adult_html +
                    '                <div class="con_iconbox">\n'+
                    '                  '+drm_html+'  \n'+
                    '                </div>\n'+
                    '                <div class="imgbox" style="text-align:center;position:relative;overflow:hidden;display:flex;;justify-content:center;align-items:center;">\n'+
                    '                    <a href="javascript:;" ><img class="lazy "  data-src="'+data_src+'" alt="best_item" style="width;230px;height:auto;" onerror="this.src=\'/assets/common/img/common/no-img.jpg\'"></a>\n'+
                    '                </div>\n'+
                    '                <div class="titbox">\n'+
                    '                    <a href="javascript:;">'+val.title+'</a>\n'+
                    '                </div>\n'+
                    '                <span class="way">'+val.number_real_cash+'WEI</span><span class="date">'+val.datetime+'</span>\n'+
                    '            </li>';

            });
            if(contents_html){
                $('.contentsList').append(contents_html);
                var ll = new LazyLoad({
                    threshold: 0
                });
                ++page;
                console.log(page);
            }

        }
        //$("ul.contentsList").append('<div class="big-box"><h1>Page ' + page + '</h1></div>');

    }
});