var http_api_url = "http://127.0.0.1:55442";
var https_api_url = "https://127.0.0.1:55443";
var _url = window.location.host
var _url_exep = _url.split('.');
var _dev = _url_exep[0] != 'www' && _url_exep[0] != '' ? '1' : '0';
$.install_check = function(){
	try{

	}catch(e){

	}
}

$.onchain_proc = function(method, params, ajax_fnc_success, loading){
    var _method = method != 'undefined' ? method : "";
    var host_url = http_api_url + "/";
    //console.log(host_url);
    $.ajax({
        url: host_url + _method,
        type: "post",
        dataType: "json",
        timeout: 10000,
        cache: false,
        data: params,
        beforeSend: function( xhr ){
            if(loading != undefined && loading == "on") $('#loading_ajax').show();
        }, complete: function( jqxhr, textStatus ){
            if(loading != undefined && loading == "on") $('#loading_ajax').hide();
        }, success: function(data, textStatus, jqXHR){
            console.log(data);

            if(data.resultCode == "0"){
                ajax_fnc_success(data, textStatus, jqXHR);
            }else{
                alert(data.resultMessage);
            }

            $('#loading_ajax').hide();
        }, error : function(xhr){
            $('#loading_ajax').hide();

            if( xhr.responseText ) {
                alert(xhr.responseText);
            }

            console.log(xhr.responseText);
        }
    });
}

var errorSetObj = null;
var errorSetDelay = 200;


$.runsync = function (url , data ,type , debug) {

    var returnData = null;
    $url = url;
    $postData  = data;

    jQuery.ajax({
        type:'POST',
        url:$url,
        data:$postData,
        dataType:type,
        timeout: 5000,
        cache: false,
        async:false,
        beforeSend: function(xhr) {
            xhr.withCredentials = true;
        },success:function(obj){
            returnData = obj;
        },error:function(xhr,textStatus,errorThrown){
            //alert('An error occurred! '+(errorThrown ? errorThrown : xhr.status));
        },complete: function(jqXHR,textStatus){
            //alert(jqXHR.statusText);
        }
    });
    if(debug) console.log(returnData);

    // adult certify wait
    if( returnData.status === '200' && returnData.code === '999' && returnData.message === '승인대기중입니다.' ) {
        maskLayer($('.certify'));
        $('form.certify_form')[0].reset();
    }

    if( returnData.code == '401' ) {
        clearTimeout( errorSetObj );

        errorSetObj = setTimeout(function() {
            alert( returnData.message );
            location.href = '/';
            return false;
        }, errorSetDelay);
    }

    return returnData;
}


$.ajaxSync = function(url, params, ajax_fnc_success, loading){

    $.ajax({
        url: url,
        type: "post",
        dataType: "json",
        timeout: 1000,
        cache: false,
        data: params,
        beforeSend: function( xhr ){
            if(typeof loading != 'undefined') $('#loader-wrapper').addClass('loader-wrapper').show();
        }, complete: function( jqxhr, textStatus ){
            if(loading != undefined) $('#loader-wrapper').removeClass('loader-wrapper').hide();
        }, success: function(data, textStatus, jqXHR){
            //console.log(data);
            if(ajax_fnc_success) ajax_fnc_success(data, textStatus, jqXHR);

        }, error : function(xhr){
            if(loading != undefined) $('#loader-wrapper').removeClass('loader-wrapper').hide();
            alert(xhr.responseText);
            console.log(xhr.responseText);
        }
    });
}



$.login_page_load = function(url, data){
	$.ajax({
		xhr: function(){
			var xhr = new window.XMLHttpRequest();
			xhr.addEventListener("progress", function(evt){
				if (evt.lengthComputable) {
					var percentComplete = evt.loaded / evt.total;
				}
			}, false);
			return xhr;
		}, 
		type: 'post',
		url: url,
		data: data,
		success: function(data){
			if($("div.wrap_login > aticle").length > 0){
				$("div.wrap_login > aticle").css("position", "absolute").animate({"margin-left": "-300px", 'opacity': 0}, '200', function() { $(this).remove(); });
				$("div.wrap_login").append($(data).animate({"margin-left": "0px"}, '400'));
			}else{
				$("div.wrap_login").append(data);
			}
		}
	});
}


$.account_balance_callback =  function(){
   var data =  $.runsync(http_api_url + '/account/balance' , '' , 'json' , true);
    if(data.resultCode != 0){
        $.onchain_proc('account/logout','',logout_call_back, 'on');
        //window.location.replace('/user/signin');
    }else{
        $('li span.eth_balance').text(number_format(data.ethBalance));

        var dappBalance = data.dappBalance;
        if( dappBalance ) {
            var octalNumber = parseInt(dappBalance, 16);
            
            var regexp = /\B(?=(\d{3})+(?!\d))/g;
            octalNumber = octalNumber.toString().replace(regexp, ',');

            $('li span.way_balance').text(octalNumber);
        }
    }
}

function logout_call_back(data){
    if(data.resultCode == 0){
        document.location.replace('/user/logout');
    }else{
        alert(data.resultMessage);
    }
}

$(document).on('click' , '.logout_btn' , function () {
    $.onchain_proc('account/logout','',logout_call_back, 'on');
});

/*******************************************************************************/


$(document).ready(function () {
    //$.account_balance_callback()
    $('.popup_top').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('.layer_contents_pop').animate({ scrollTop: 0 }, 600);
    });

    /*$('.popup_close_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        contents_maskLayer($('.layer_contents_pop'));
    });*/

    function contentPurchaseCheck(obj, contents_id) {
        if( obj && contents_id ) {
            var purchase_params  = {contents_id : contents_id};
            var purchase_info = $.runsync('/contents/purchaseInfo', purchase_params , 'json' , true);

            if( purchase_info.message == 'BUY' ) {
                obj.attr('data-purchase', 'N');
            } else {
                obj.attr('data-purchase', 'Y');
            }
        }
    }

    $(document).on('click' , '.contentsList li  , .contentsList .item' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        var obj = $(this);
        var contents_id = obj.attr('contents_id');
        var user_is_adult  = getCookie('is_adult');
        var is_adult = obj.attr('adult');

        contentPurchaseCheck(obj, contents_id);

        if(is_adult == 'Y' && user_is_adult == 'N'){
            maskLayer($('.adultPop'));
        }else if(is_adult == 'Y'){
            var params = {};
            var data = $.runsync('/user/userVertify' , params , 'json',false);

            if(data.code == 200){
                document.location.href = "#contents_id="+contents_id + '&t='+new Date().getTime();
            }else{
                alert(data.message);
            }
        }else{
            document.location.href = "#contents_id="+contents_id + '&t='+new Date().getTime();
        }
    });


    $('.adult_certify_btn').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        $('.adultPop').animate({
            left: '2000px',
            opacity : 0.1,
        }).fadeOut('1000');

        var win = $(window);
        var _obj = $('.certify');
        var left = win.scrollLeft() + ((win.width() - _obj.width()) / 2);
        var top = (win.height() - _obj.height() ) / 2 ;
        //_obj.css({'left':left + 'px','top':top + 'px'}).show();
        _obj.css({'top':top +'px' , 'left' : '-300px'}).fadeTo('fast' , 0.1);
        _obj.animate({'left':left +'px'}).fadeTo('fast' , 1);


    });

    $(document).on('mouseover' , '.contentsList li' , function (event) {
        $(this).find('img.lazy').addClass('image');
    });

    $(document).on('mouseout' , '.contentsList li' , function (event) {
        $(this).find('img.lazy').removeClass('image');
    });

    $(window).bind('hashchange' , function () {
        var hash = location.hash;
        var urlParams = getUrlParams(hash)
        if(urlParams.contents_id > 0){
            //alert(urlParams.contents_id)
            $.contentsLoadView('/contents/ajax_view/' , urlParams );
            contents_maskLayer($('.layer_contents_pop'));
        }

    });

    if(window.location.href.indexOf("#") != -1){
        var hash = location.hash;
        var urlParams = getUrlParams(hash);
        if(urlParams.contents_id > 0){
            $.contentsLoadView('/contents/ajax_view/' , urlParams );
            contents_maskLayer($('.layer_contents_pop'));
        }
    }

    $('.layer_contents_pop').scroll(function (event) {
        event.stopPropagation();
        event.preventDefault();
        if($(this).scrollTop() > 20){
            $('.popup_top').fadeIn('slow');
        }else{
            $('.popup_top').fadeOut('slow');
        }
        var btnOffset = $( '.down_btn_area' ).offset();
        if ( $( '.layer_contents_pop' ).scrollTop() > btnOffset.top ) {
            var _obj = $('.down_btn_area');
            var win = $(window);
            var left = win.scrollLeft() + ((win.width() - _obj.width()) / 2);
            var agent = navigator.userAgent.toLowerCase();
            if ( (navigator.appName == 'Netscape' && agent.indexOf('trident') != -1) || (agent.indexOf("msie") != -1)) {
                var left_px = left -8;
            }else{
                var left_px = left;
            }
            $( '.down_btn_area' ).css( {'position':'fixed','top':0 , 'width':'800px;' , 'left' : left_px , 'z-index' : '2' , 'border-bottom' : '1px solid #ccc'} );
        }else {

            $( '.down_btn_area' ).css( {'position':'' ,'border-bottom' : '0'});
        }
    });

});

$(document).on('click' , '.zzim_btn' ,function (event) {
    event.stopPropagation();
    event.preventDefault();
    var contents_id = $('form.down-form input[name=contents_id]').val();
    var params = {contents_id : contents_id};
    var zzim_status = $('form.down-form input[name=zzim_status]').val();

    if(zzim_status != 1){
        var zzim_data = $.runsync('/contents/contentsZzim' , params , 'json' , false);
        if(zzim_data.code == 200) {
            var img_src = $(this).find('img').attr('src');
            $(this).find('img').attr({'src': img_src.replace('icon_zzim.png', 'icon_zzim_on.png')});
            alert(zzim_data.message);
        }else{
            alert(zzim_data.message);
        }
    }else{
        alert('이미찜한 콘텐츠입니다.');
    }
});

$(document).on('click' , '.recommand_btn' , function (event) {
    event.stopPropagation();
    event.preventDefault();

    var contents_id = $('form.down-form input[name=contents_id]').val();

    var params = {contents_id : contents_id};
    var recommand_data = $.runsync('/contents/contentsRecommand', params , 'json' , false);

    if(recommand_data.code == 200){
        $(this).find('.recommand_cnt').text(recommand_data.data.recommand_cnt);
    }else{
        alert(recommand_data.message);
    }

});

$(document).on('click' , '.contents_layer_close_btn , .popup_close_btn' , function (event) {
    event.stopPropagation();
    event.preventDefault();
    //console.log(location.hash);
    location.hash = '';
    //history.replaceState(null, null, ' ');
    $('html,body').css({'overflow':'auto'});
    contents_maskLayer($('.layer_contents_pop'));
});

$.contentsLoadView = function (url  , params) {
    var jsonData  = $.runsync(url , params , 'json' , true);
    $('.layer_contents_pop').empty();
    $('html,body').css({'overflow':'hidden'});
    $('.layer_contents_pop').html(jsonData.contents_html);
    $('.layer_contents_pop').animate( { scrollTop : 0 }, 400 );
    var ll = new LazyLoad({
        threshold: 0
    });

}

var list_mode = false;
$(document).on('click' , '.btn_listmore' , function (event) {
    event.stopPropagation();
    event.preventDefault();

    $('.conPop .conbom .containBox01 ul.hide_list').slideToggle();
    $(this).text(list_mode ? '더 보 기' : '접 기');
    list_mode = list_mode ? false : true;
});


function getUrlParams(str) {
    var params = {};
    str.replace(/[#&]+([^=&]+)=([^&]*)/gi, function(str, key, value) { params[key] = value; });
    return params;
}

$(document).on('click' , '.content_view .btn_purchase' , function(event){
    event.stopPropagation();
    event.preventDefault();

    if( confirm('컨텐츠를 구매겠습니까?') ) {
        var contents_id = $("form.down-form input[name=contents_id]").val();
        var productId = $('form.down-form input[name=productId]').val();
        var contents_ccid = $('form.down-form input[name=contents_ccid]').val();
        var contents_version = $('form.down-form input[name=contents_version]').val();
        var file_path = $('form.down-form input[name=file_path]').val();
        var file_type = $('form.down-form input[name=file_type]').val();
        var file_size = $('form.down-form input[name=file_size]').val();
        var title = $('form.down-form input[name=title]').val();

        var json_add = "";
        var url = "http://localhost:54777";

        var product_params = {productId : productId}
        var purchase_data = $.runsync(http_api_url + '/register/buy' , product_params , 'json' , true);

        if(purchase_data.resultCode == 0 && purchase_data.purchaseId){
            var buy_params = {contents_id : contents_id , blockchain_purchaseId : purchase_data.purchaseId};
            var buy_data = $.runsync('/contents/contentsPurchase' , buy_params ,'json' , true);

            if(buy_data.code == 200){
                alert('콘텐츠 구매가 완료 되었습니다.');
                document.location.reload();
            }else{
                alert('콘텐츠 구매중 에러가 발생했습니다.');
                document.location.reload();
            }
        }else{
            alert(purchase_data.resultMessage);
            document.location.reload();
        }
    }
});

$(document).on('click' , '.content_view .btn_download , .content_view .btn_redownload' , function(event){
    event.stopPropagation();
    event.preventDefault();

    if(confirm('파일을 다운로드 하시겠습니까?')) {
        $('#popup_loading_ajax').slideDown(function () {
            $('#loading_ajax').fadeIn();

            var contents_id = $("form.down-form input[name=contents_id]").val();
            var next_val = $("form.down-form input[name=next_val]").val();
            var user_id = $('form.down-form input[name=user_id]').val();
            var contents_ccid = $('form.down-form input[name=contents_ccid]').val();
            var contents_version = $('form.down-form input[name=contents_version]').val();
            var sub_path = "";
            //var payment_id = $('form.down-form input[name=payment_id]').val();
            var file_path = $('form.down-form input[name=file_path]').val();
            var file_type = $('form.down-form input[name=file_type]').val();
            var file_size = $('form.down-form input[name=file_size]').val();
            var title = $('form.down-form input[name=title]').val();
            var productId = $('form.down-form input[name=productId]').val();

            //var product_id = productId  ?  productId : '20275355766890061341786867679785024396367831306368464681283286383045859240537';
            var json_add = "";
            var url = "http://localhost:54777";

            var purchase_params  = {contents_id : contents_id};
            var purchase_info = $.runsync('/contents/purchaseInfo', purchase_params , 'json' , true);
            console.log(purchase_info);

            if(purchase_info.code == 200){
                // log down count add
                var down_count_add_params = {
                    log_purchase_id : purchase_info.data.log_purchase_id
                };
                var down_count_add_data = $.runsync('/contents/purchaseDownCountAdd' , down_count_add_params ,'json' , true);

                $("ul.sty02").each(function (i) {
                    var  json_params = {
                        /* "contents_no": contents_no,
                         "contents_file_no": $(this).attr("contents_file_no"),
                         "file_name": $(this).attr("file_name"),
                         "file_size": $(this).attr("file_size"),
                         "user_id": user_id,
                         "next_val": next_val,
                         "file_no": String(parseInt($(this).attr("file_no"))),
                         "del_yn": $(this).attr("del_yn"),
                         "total_cnt" : String($("ul.file_list  li").length),*/
                        "contents_ccid" : contents_ccid,
                        "contents_version"  : contents_version,
                        "account_id" : $('.user_accountId').text(),
                        "product_id"    : productId,
                        "sub_path" : '',//sub_path,
                        "payment_id" : purchase_info.data.blockchain_purchaseId,
                        "file_path" : title ,
                        "file_size" : String($(this).attr('file_size')),
                        "file_type" : file_type  ? 'F' : 'T' ,
                    }
                    json_add += JSON.stringify(json_params) + "#";
                    //console.log(json_add);
                });
                var params = {c:json_add.substring(0 , (json_add.length -1))}
                //console.log(params);
                $.runsync(url, params, 'json', false);
                $('html,body').css({'overflow':'auto'});
                contents_maskLayer($('.layer_contents_pop'));
                document.location.href = "#contents_id="+contents_id + '&t='+new Date().getTime();;

            }else{
                alert(purchase_info.message);
            }

            $('#loading_ajax').fadeOut();
        });
    }
});

function maskLayer(_obj,_is_parent ){
    if(!_is_parent)	_is_parent = false;
    var win = $(window);
    var mask = _is_parent ? $(parent.document).find('#mask') : $('#mask');
    if(_obj.css('display') == 'block') {
        //console.log('block');
        //$('html, body').css({'overflow': 'auto','height':''});
        //$('body').off('scroll touchmove mousewheel');
        $('html,body').css({'overflow':'auto'});
        mask.hide();
        _obj.hide();
    } else {
        var width = '100%';
        var height = (typeof(is_mobile) != 'undefined' && is_mobile) ? '100%'  :  $(document).height();
        var left = win.scrollLeft() + ((win.width() - _obj.width()) / 2);
        var top = (win.height() - _obj.height() ) / 2 ;
        _obj.css({'left':left + 'px','top':top + 'px','z-index':9997,'opacity':1}).show();
        $('html,body').css({'overflow':'hidden'});
        mask.css({'width':width,'height':height ,'overflow':"hidden"}).fadeTo('fast',0.8);
        mask.fadeIn();
    }
}

function contents_maskLayer(_obj,_is_parent ) {
    if(!_is_parent)	_is_parent = false;
    var win = $(window);
    var mask = _is_parent ? $(parent.document).find('#mask') : $('#mask');

    /*$('.popup_close_btn').css({'left' : _close_left ,'z-index':'9999' , 'position': 'fiexed','width' : '36px' , 'height' : '35px' , 'display':'block' , 'top' : '0'});*/
    if(_obj.css('display') == 'block') {
        //console.log('block');
        //$('html, body').css({'overflow': 'auto','height':''});
        //$('body').off('scroll touchmove mousewheel');
        $('html,body').css({'overflow':'auto'});
        mask.hide();
        $('.popup_close_btn , .popup_top').fadeOut();
        _obj.hide();
    } else {
        var width = '100%';
        var height = (typeof(is_mobile) != 'undefined' && is_mobile) ? '100%'  :  $(document).height();

        var left = win.scrollLeft() + ((win.width() - _obj.width()) / 2);
        var top = (win.height() - _obj.height() ) / 2 ;
        var _close = $('.popup_close_btn');
        var _close_left = win.scrollLeft() + ((win.width() + _obj.width()) / 2);


        mask.css({'width':width,'height':height}).fadeTo('fast',0.8);
        mask.fadeIn();
        _obj.css({'left':left,'top':0 }).show();
        $('.popup_close_btn').css({'left' : _close_left + 20,'z-index':'9999' , 'position': 'absolute','width' : '36px' , 'height' : '35px' , 'display':'block' , 'top' : '10px',});
        $('.popup_top').css({'left' : _close_left - 100 , 'z-index':'9999'  , 'position': 'fixed','bottom' : '50px','display':'none'});
    }
}

/*******************************************************************************/
function number_format(data)
{
    var tmp = '';
    var number = '';
    var cutlen = 3;
    var comma = ',';
    var i;
    var sign = data.match(/^[\+\-]/);
    if(sign) {
        data = data.replace(/^[\+\-]/, "");
    }
    len = data.length;
    mod = (len % cutlen);
    k = cutlen - mod;
    for (i=0; i<data.length; i++)
    {
        number = number + data.charAt(i);
        if (i < data.length - 1)
        {
            k++;
            if ((k % cutlen) == 0)
            {
                number = number + comma;
                k = 0;
            }
        }
    }
    if(sign != null)
        number = sign+number;
    return number;
}


/**
 * http://xdsoft.net/jqplugins/datetimepicker/ 의존
 * @param {[type]} configs [description]
 */
function ComponentDatePicker(configs)
{
    configs = configs || {};
    configs.id = configs.id || ".jca-date-picker";
    configs.timepicker = configs.timepicker || false;
    configs.format = configs.format || "Y-m-d";
    configs.step = configs.step || 60;
    this.pickers = [];

    this.init = function()
    {
        var self = this;
        self.eles = document.querySelectorAll(configs.id);

        for(var i = 0; i < self.eles.length; i++)
        {
            var ele = self.eles[i];
            jQuery(ele).datetimepicker({
                timepicker:configs.timepicker,
                format:configs.format,
                step:configs.step,
                lang:'kr'
            });
        }
    }

    this.construct = this.init();
}

function getCookie(cookieName) {
    cookieName = cookieName + '=';
    var cookieData = document.cookie;
    var start = cookieData.indexOf(cookieName);
    var cookieValue = '';
    if(start != -1){
        start += cookieName.length;
        var end = cookieData.indexOf(';', start);
        if(end == -1)end = cookieData.length;
        cookieValue = cookieData.substring(start, end);
    }
    return unescape(cookieValue);
}


window.comDatePickers = new ComponentDatePicker({});

$(document).on('click' , '.btn_ceritfy_submit' , function (event) {
    event.stopPropagation();
    event.preventDefault();

    var obj = $('form.certify_form');
    if(!obj.find('input[name=name]').val()){
        alert('이름을 입력해 주세요.');
        obj.find('input[name=name]').focus();
        return false;
    }

    if(!obj.find('input[name=hp]').val()){
        alert('휴대폰 정보을 입력해 주세요.');
        obj.find('input[name=hp]').focus();
        return false;
    }

    if(!obj.find('input[name=captcha_key]').val()){
        alert('보안숫자를 입력해 주세요.');
        obj.find('input[name=captcha_key]').focus();
        return false;
    }else{
        if(!chk_captcha()) return false;
    }
    //$('form.certify_form')[0].reset();
    //maskLayer($('.certify'));

    $('form.certify_form').submit();
});

$(document).on('submit' , 'form.certify_form' , function (event) {
    event.stopPropagation();
    event.preventDefault();
    var params = $(this).serialize();
    $('#certify_loading').slideDown(function () {
        var data = $.runsync('/user/certify' , params , 'json' , true);
        if(data.code == 200) {
            alert('휴대폰인증이 완료되었습니다.\n관리자 승인 후 성인콘텐츠를 이용하실수 있습니다.');
            $('form.certify_form')[0].reset();
            maskLayer($('.certify'));
            $('#certify_loading').slideUp();
        }else{
            alert(data.message);
            //$('form.certify_form')[0].reset();
            //maskLayer($('.certify'));
            $('#certify_loading').slideUp();
        }
    });

});

$(document).ready(function() {
    if( $('.wei_change_btn').length ) {
        $(document).on('click', '.wei_change_btn', function(e) {
            e.preventDefault();

            var eth_value = prompt('WEI 로 전환할 이더리움을 입력하세요.', "0.1");

            if( !eth_value ) {
                return false;
            }

            while (isNaN(eth_value)) {
                alert('숫자만 입력하세요.');
                eth_value = prompt('WEI 로 전환할 이더리움을 입력하세요.');

                if( !eth_value ) {
                    return false;
                }
            }

            var params = {
                'unit': 'ether',
                'amount': eth_value
            };

            var wei_change_data = $.runsync(http_api_url + '/send/ethereum' , params , 'json' , true);

            //balanceAfter: "8696349600000000000"
            //balanceBefore: "8796908000000000000"
            //ethAddress: "0x1ee77618b9e4f7651381e2ede71b0d389f27a5c6"
            //resultCode: 0

            if( wei_change_data.resultCode === 0 ) {
                alert('전환이 완료 되었습니다.');
                location.reload();
            } else {
                if( wei_change_data.resultMessage ) {
                    alert( wei_change_data.resultMessage );
                } else {
                    alert('WEI 전환 중 오류발생! 다시 시도해 주세요.');
                }
            }
        });
    }
});

