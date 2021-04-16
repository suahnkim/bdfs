var $el = $('#LoadingOverlayApi');
var http_api_url = "http://127.0.0.1:55442";
var https_api_url = "https://127.0.0.1:55443";
var master_eth_account = '1ee77618b9e4f7651381e2ede71b0d389f27a5c6';
var authoriryRequestArr = {
    'Packager' : '콘텐츠 제공자',
    'ContentsProvider' : '콘텐츠 생성자',
    'StorageProvider' : '스토리지 제공자',
    'Distributor' : '유통업자'
};

var package_status_arr =  {
    '0'   : '<button id="shadow-default" class="btn btn-warning">준비중</button>',
    '1'   :  '<button id="shadow-info" class="btn btn-info">진행중</button>',
    '2'   : '<button id="shadow-error" class="btn btn-danger">오류</button>',
    '3'   : '<button id="shadow-success" class="btn btn-success">완료</button>',
};

var _url = window.location.host
var _url_exep = _url.split('.');
var _dev = _url_exep[0] != 'www' && _url_exep[0] != '' ? '1' : '0';

(function($){
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
})(jQuery);

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e){
    $(this).parents('.nav-tabs').find('.active').removeClass('active');
    $(this).parents('.nav-pills').find('.active').removeClass('active');
    $(this).addClass('active').parent().addClass('active');
});

if(typeof($.fn.datepicker) != 'undefined'){
    $.fn.bootstrapDP = $.fn.datepicker.noConflict();
}


$(document).ready(function () {
    $('.btn-logout').on('click' , function (event) {
        event.preventDefault();

        var confirmMessage = '로그아웃 하시겠습니까?';

        var params = {};

        var data = $.runsync('/contents/getAjaxLastContentInfo', params, 'json', false);
        if(data.result){
            var params = {
                'ccid': data.content.ccid,
                'version': data.content.ccid_ver
            };
            var isreceiveResult = $.runsync(http_api_url + '/product/isreceive', params, 'json', false);

            //console.log( isreceiveResult );

            if( isreceiveResult.resultCode === 0 ) {
                if( isreceiveResult.search === false) {
                    confirmMessage = '다운로드가 진행중입니다. 그래도 로그아웃 하시겠습니까?';
                }else if (isreceiveResult.storage === false)
                {
                    confirmMessage = '다운로드가 진행중입니다! 그래도 로그아웃 하시겠습니까?';
                }
            }
        }

        if( confirm(confirmMessage) ) {
            $.onchain_proc('account/logout','',logout_call_back, 'on');
        }
    });
});

function logout_call_back(data){
    if(data.resultCode == 0){
        document.location.replace('/user/logout');
    }else{
        alert(data.resultMessage);
    }
}

$.onchain_proc = function(method, params, ajax_fnc_success, loading){

    var _method = method != 'undefined' ? method : "";
    var host_url = http_api_url + "/";
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
            ajax_fnc_success(data, textStatus, jqXHR);
        }, error : function(xhr){
            $('#loading_ajax').hide();
            alert(xhr.responseText);
            console.log(xhr.responseText);
        }
    });
}


$.runsync = function (url , data ,type , debug , loading) {

    var _data = null
    $url = url;
    $postData  = data;

    jQuery.ajax({
        type:'POST',
        url:$url,
        data:$postData,
        dataType:type,
        timeout: 10000,
        cache: false,
        async:false,
        beforeSend: function(xhr) {
            //xhr.withCredentials = true;
            if(loading != undefined) $('#loading_ajax').fadeIn();
        },success:function(obj){
            if(debug) console.log(obj);
            _data = obj;

            if( _data.resultCode == 0 ) {
                var ethValueObj = $('.balance_item_value.eth');
                if( ethValueObj.length ) {
                    ethValueObj.html( _data.ethBalance );
                }
                ethValueObj=null;

                var dappValueObj = $('.balance_item_value.dapp');
                if( dappValueObj.length ) {
                    dappValueObj.html( _data.dappBalance );
                }
                dappValueObj=null;
            }

            return obj;
        },error:function(xhr,textStatus,errorThrown){
            if(loading != undefined) $('#loading_ajax').fadeOut();
            //alert('An error occurred! '+(errorThrown ? errorThrown : xhr.status));
        },complete: function(jqXHR,textStatus){
            //alert(jqXHR.statusText);
            if(loading != undefined) $('#loading_ajax').fadeOut();
        }
    });
    return _data;
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
            if(loading != undefined && loading == "on") $('#loading_ajax').show();
        }, complete: function( jqxhr, textStatus ){
            if(loading != undefined && loading == "on") $('#loading_ajax').hide();
        }, success: function(data, textStatus, jqXHR){
            console.log(data);
            if(ajax_fnc_success) ajax_fnc_success(data, textStatus, jqXHR);

        }, error : function(xhr){
            $('#loading_ajax').hide();
            alert(xhr.responseText);
            console.log(xhr.responseText);
        }
    });
}

function formatBytes(bytes,decimals) {
    if(bytes == 0) return '0 Bytes';
    var k = 1000,
        dm = decimals + 1 || 2,
        sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
var stack_bar_bottom = {"dir1": "up", "dir2": "right", "spacing1": 0, "spacing2": 0};

window.commonMessage = function(param){
    var notice = new PNotify({
        title: param.title,
        text: param.message,
        type: param.type,
        addclass: param.addclass,
        stack: param.stack,
        width: "70%",
        delay: 2000,
    });
}

$.account_balance_callback =  function(){
    var data =  $.runsync(http_api_url + '/account/balance' , '' , 'json' , true);

    if(data.resultCode != 0){
        //$('.btn-logout').click();
        window.location.replace('/user/signin');
    }
}


var PagingHelper = {
    'data' : {
        currentPage :1     // 현재페이지
        //,startPage : 1    // 시작페이지
        ,pageSize : 10       // 페이지 사이즈 (화면 출력 페이지 수)
        ,maxListCount : 20  // (보여질)최대 리스트 수 (한페이지 출력될 항목 갯수)
        ,startnum : 1       // 시작 글번호
        ,lastnum : 0       // 마지막 글번호
        ,totalCnt : 0       // 전체 글의 갯수.
        ,totalPageCnt : 0   // 전체 페이지 수
    },
    'setOption' : function(opt){
        if( typeof opt != 'object' ) return;
        for (key in opt ) {
            if(key in this.data) {
                this.data[key] = opt[key]; //data에 입력받은 설정값 할당.
            }
        }
    },
    'pagingHtml' : function(pTotalCnt){

        var _ = this;

        _.data['totalCnt'] = pTotalCnt?pTotalCnt:_.data['totalCnt'];

        if (_.data['totalCnt'] == 0) {
            return "";
        }
        //총페이지수 구하기 : 페이지 출력 범위 (1|2|3|4|5)
        _.data.totalPageCnt = Math.ceil(_.data.totalCnt / _.data.maxListCount);

        //현재 블럭 구하기
        var n_block = Math.ceil(_.data.currentPage / _.data.pageSize);

        //페이징의 시작페이지와 끝페이지 구하기
        var s_page = (n_block - 1) * _.data.pageSize + 1; // 현재블럭의 시작 페이지
        var e_page = n_block * _.data.pageSize; // 현재블럭의 끝 페이지

        var sb='';
        var sbTemp ='';

        //console.log(_.data);
        //console.log(n_block+"/"+s_page+"/"+e_page);

        // 블럭의 페이지 목록 및 현재페이지 강조
        for (var j = s_page; j <= e_page; j++) {
            if (j > _.data.totalPageCnt ) break;
            if(j == _.data.currentPage) {
                sbTemp += "<li class=\"paginate_button page-item active\"><a href=\"javascript:;\" class= \"page-link\">"+j+"</a></li>";
            } else {
                sbTemp += "<li class=\"paginate_button page-item page\" pageNum='"+j+"'><a href='javascript:;' class= \"page-link\">"+j+"</a></li>";
            }
        }

        // 이전페이지 버튼
        sb = "<div style=\"margin-top:20px;\"><ul class=\"pagination text-center\" style=\"position:relative; display: -webkit-flex;display: flex;-webkit-justify-content: center;justify-content: center;-webkit-align-items: center;\n  align-items: center; \">"
        if(_.data.currentPage > s_page || _.data.totalCnt > _.data.maxListCount && s_page > 1){
            sb += "<li class=\"paginate_button page-item first\"  pageNum='1'><a href='javasript:;' class= \"page-link\">처음</a></li >"
            sb += "<li class=\"paginate_button page-item previous\"  pageNum='"+ (_.data.currentPage - 1) +"'><a href='javasript:;' class= \"page-link\">&lt;</a></li>"
        }

        // 현재블럭의 페이지 목록
        sb += sbTemp

        // 다음페이지 버튼
        if(_.data.currentPage < _.data.totalPageCnt ){
            sb += "<li class=\"paginate_button page-item next\"  pageNum='"+ (parseInt(_.data.currentPage) + 1) +"'><a href='javasript:;' class= \"page-link\">&gt;</a></li>"
            sb += "<li class=\"paginate_button page-item last\" pageNum='"+  (_.data.totalPageCnt) +"'><a href='javasript:;' class= \"page-link\">마지막</a></li >"
        }
        sb += "</ul></div>";

        return sb;
    },
    "makeNum":function(className, content ){     //필요없음.
        return "<li class='"+className+"''>["+content+"]</li>";
    },
    'setStartnumEndnum' : function() {
        // 시작 글번호
        this.data.startnum = (this.data.currentPage -1) * this.data.maxListCount + 1;

        // 마지막 글번호
        var tmp = this.data.currentPage * this.data.maxListCount;
        this.data.lastnum = (tmp > this.data.totalCnt?this.data.totalCnt:tmp);
    },
    'gotoPage':function(pageNum){
        //console.log(pageNum);

        this.data.currentPage = pageNum; //입력받은 페이지번호를 현재페이지로 설정
        this.setStartnumEndnum();    //입력받은 페이지의 startnum과 endnum구하기

        //콘솔 출력 (삭제)
        //console.log(this.data.currentPage+"/"+this.data.startnum +"/"+this.data.lastnum);
        //alert(this.data.currentPage+"/"+this.data.startnum +"/"+this.data.lastnum);

        //리스트 불러오는 ajax호출
        //////////////////////////
        $("#ajaxpaging").html(this.pagingHtml());
    }
}

function maskLayer(_obj,_is_parent ) {
    if(!_is_parent)	_is_parent = false;
    var win = $(window);
    var mask = _is_parent ? $(parent.document).find('#mask') : $('#mask');
    console.log('asdfasdfasdf');

    if(_obj.css('display') == 'block') {
        //console.log('block');
        //$('html, body').css({'overflow': 'auto','height':''});
        //$('body').off('scroll touchmove mousewheel');
        //$('html,body').css({'overflow':'auto'});
        mask.hide();
        _obj.hide();
        console.log('1');
    } else {
        var width = '100%';
        var height = (typeof(is_mobile) != 'undefined' && is_mobile) ? '100%'  :  $(document).height();

        var left = win.scrollLeft() + ((win.width() - _obj.width()) / 2);
        var top = (win.height() - _obj.height() ) / 2 ;

        mask.css({'width':width,'height':height ,'overflow':"hidden"}).fadeTo('fast',0.8);
        mask.fadeIn();

        _obj.css({'left':left,'top':top}).show();
        return;

    }
}



