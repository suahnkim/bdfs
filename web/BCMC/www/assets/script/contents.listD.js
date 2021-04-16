$(document).ready(function () {
    $('.contents_modal_view a').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('.contentSellFom').empty();
        $('.data-loading').removeClass('loading-overlay').addClass('loading-overlay-showing');
        var contents_id = $(this).attr('media-contnets-id');
        var ccid = $(this).parents('tr').attr('ccid');
        var ccid_ver = $(this).parents('tr').attr('ccid_ver');
        var productId = $(this).parents('tr').attr('productId');

        //console.log("ccid => " + ccid +"  :: ccid_ver =>"+ccid_ver);

        var params = {contents_id : contents_id}
        //console.log(params);
        var data = $.runsync('/contents/getAjaxContents', params, 'json', false);

        if(data.result){
            setTimeout(function () {
                $('.data-loading').removeClass('loading-overlay-showing').addClass('loading-overlay');
                $('.contentSellFom').html(data.contents_html);
                $('.contents_sell_info').append('<input type="hidden" name="ccid" value="'+ccid+'">');
                $('.contents_sell_info').append('<input type="hidden" name="version" value="'+ccid_ver+'">');
                $('.contents_sell_info').append('<input type="hidden" name="productId" value="'+productId+'">');
                $('input[name=sell_cash]').focus();
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

    $("button.btn-submit").click(function(event){
        event.stopPropagation();
        event.preventDefault();

        $('form.contents_sell_info').submit();
    });

    $(document).on('submit' , 'form.contents_sell_info' ,function (event) {
        //event.stopPropagation();
        //event.preventDefault();

        var cash = parseInt($('form.contents_sell_info input[name=cash]').val()) || 0;
        var sell_cash = parseInt($('form.contents_sell_info input[name=sell_cash]').val()) || 0;
        var contents_id =$(this).find('input[name=contents_id]').val();
        var productId = $('.contents_sell_info input[name=productId]').val();
        var loading  = true;
        if(!$('form.contents_sell_info input[name=sell_cash]').val()) {
            commonMessage({
                title: 'Error',
                message: '유통 가격을 입력해주세요.',
                type: 'error',
                addclass: 'stack-bar-bottom',
                stack: stack_bar_bottom,
            });
            $('form.contents_sell_info input[name=sell_cash]').focus();
            return false;
        }

        if(cash >= sell_cash){
            commonMessage({
                title: 'Error',
                message: '유통가격을 저작권료보다 높게 설정해주세요.',
                type: 'error',
                addclass: 'stack-bar-bottom',
                stack: stack_bar_bottom,
            });
            return false;
        }
        //maskLayer($('#loading_ajax'));

        //$('.progress_loading_area').fadeIn();
        /*if(loading){
            $('body').addClass('loading-overlay-showing');
            console.log('11111111111');
        }*/
        $('button.btn-submit').prop('disabled' , true);
         if(productId) $('.progress_loading_area .progress_loading_txt').text(' 상품정보 수정중...');
         else $('.progress_loading_area .progress_loading_txt').text(' 상품코드 발급받는중...');
        $('.progress_loading_area').fadeIn(function () {

            if(productId){

                var product_data = {};
                product_data.resultCode = 0;
                product_data.productId = productId;

            }else{
                var product_params = {ccid : $('.contents_sell_info input[name=ccid]').val() , version : $('.contents_sell_info input[name=version]').val() , price : parseInt($('.contents_sell_info input[name=sell_cash]').val()) }

                //console.log(product_params);
                var product_data = $.runsync(http_api_url + '/register/product', product_params, 'json', true);
            }

            /* var productId = $('form.contents_sell_info input[name=productId]').val() ?  $('form.contents_sell_info input[name=productId]').val() : randNum();
             var product_data = {};
             product_data.resultCode = 0;
             product_data.productId = productId;*/
            if(product_data.resultCode == 0){
                var params = {contents_id : contents_id , sell_cash : sell_cash , productId : product_data.productId}
                //console.log(params);
                var data = $.runsync('/contents/postSellContentsReg', params, 'json', true);
                if(data.code == 200){
                    alert('정상적으로 유통설정이 되었습니다.');
                    document.location.reload();
                }
            }else{
                commonMessage({
                    title: 'Error',
                    message: product_data.resultMessage ,
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
                $('button.btn-submit').prop('disabled' , false);
            }

        });
        $('.progress_loading_area').fadeOut();
        return false;

    });

    $(document).on('keyup' , 'form.contents_sell_info input[name=sell_cash]' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).val($(this).val().replace(/[^0-9]/g,''));
    });

});

function randNum(){
    var ALPHA = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'];
    var rN='';
    for(var i=0; i<12; i++){
        var randTnum = Math.floor(Math.random()*ALPHA.length);
        rN += ALPHA[randTnum];
    }
    return rN;
}
