var http_api_url = "http://127.0.0.1:55442";
var https_api_url = "https://127.0.0.1:55443";


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
        }, error : function(xhr){
            $('#loading_ajax').hide();
            alert(xhr.responseText);
            console.log(xhr.responseText);
        }
    });
}

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


function PopupCenter(url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var systemZoom = width / window.screen.availWidth;
    var left = (width - w) / 2 / systemZoom + dualScreenLeft
    var top = (height - h) / 2 / systemZoom + dualScreenTop
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w / systemZoom + ', height=' + h / systemZoom + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) newWindow.focus();
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

window.comDatePickers = new ComponentDatePicker({});
