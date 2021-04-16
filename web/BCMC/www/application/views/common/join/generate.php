<section class="body-sign ">
    <div class="center-sign ">

       <!-- <div class="row ">-->
            <div class="col">
                <a href="/" class="logo float-left">
                    <a href="/" class="logo float-left"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/logo_new.png" height="54" alt="Mediablockchain" /></a>
                </a>
                <section class="card-sign form-wizard " id="w4" style="width:800px;">
                    <div class="card-title-sign mt-3 text-right">
                        <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign Up</h2>
                    </div>
                    <div class="card-body">
                        <div class="wizard-progress wizard-progress-lg">
                            <div class="steps-progress">
                                <div class="progress-indicator" style="width: 0%;"></div>
                            </div>
                            <!--
                            completed
                            active
                            -->
                            <ul class="nav wizard-steps">
                                <li class="nav-item active" style="left:0;">
                                    <a class="" href="#w4-account" ><span>1</span>계정유형선택</a>
                                </li>
                                <li class="nav-item " style="margin-left:23%;">
                                    <a class=" " href="#w4-profile" ><span>2</span>이더리움 계정생성</a>
                                </li>
                                <li class="nav-item " style="margin-left:23%;">
                                    <a class=" " href="#w4-billing" ><span>3</span>신청완료</a>
                                </li>
                            </ul>
                        </div>

                        <form class="form-horizontal p-3 jca-join-form"  id="summary-form" method="post">
                            <input type="hidden" name="step_progress" value="1">
                            <input type="hidden" name="account_type">
                            <input type="hidden" name="account_id">
                            <input type="hidden" name="account_generate_type">
                            <div class="tab-content process_account ">
                                <div id="w4-account" class="tab-pane active div_form_area">
                                    <div class="form-group row account_type_list">
                                        <div class="pricing-table princig-table-flat row no-gutters mt-12 mb-12 center"  style="width:100%;cursor:pointer;">
                                            <?php foreach(array_slice($this->menu->list , 1, 3) as $key => $val){?>
                                                <div class="col-lg-4 col-sm-12 account_type" jca-account-type="<?php echo($key)?>">
                                                    <div class="plan">
                                                        <h3><?php echo(ENUM_ACCOUNT_TYPE::_print($key))?></h3>
                                                        <ul>
                                                            <li style="min-height:100px;"><?php echo(ENUM_ACCOUNT_TYPE::_print_exam($key))?></li>
                                                            <li onclick="$('.next').click();"><a class="btn btn-light" href="javascript:;">선택</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php }?>
                                        </div>
                                    </div>
                                </div>
                                <div id="w4-profile" class="tab-pane div_form_area">
                                    <!--<div class="form-group row">
                                        <label class="col-sm-3 control-label text-sm-right pt-1" for="w4-first-name">First Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control valid" name="first-name" id="w4-first-name" required="" aria-invalid="false">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label text-sm-right pt-1" for="w4-last-name">Last Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control valid" name="last-name" id="w4-last-name" required="" aria-invalid="false">
                                        </div>
                                    </div>-->
                                    <div class="form-group mb-3 loading_box" style="display:;">
                                        <!--img src="/assets/common/img/icon/loading.gif"  alt="loading" />-->
                                        <div class="clearfix0" >
                                            <label class="float-left ethereum_account_txt"></label>
                                        </div>
                                    </div>

                                    <div class="tabs">

                                        <ul class="nav nav-tabs nav-justified account_tab">
                                            <li class="nav-item active">
                                                <a class="nav-link active" href="#exists_etherum_account" data-toggle="tab"><!--<i class="fas fa-star">--></i> 기존계정사용</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#new_etherum_account" data-toggle="tab">신규계정생성</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content active">
                                            <div id="exists_etherum_account" class="tab-pane active form_area">
                                                <div class="form-group mb-3">
                                                    <label>ethereum account</label>
                                                    <div class="input-group">
                                                        <select name="ethereum_account" class="form-control" required>
                                                            <option value="">Loading...</option>
                                                        </select>
                                                        <span class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-3 password_area">
                                                    <div class="clearfix">
                                                        <label class="float-left">Password</label>
                                                    </div>
                                                    <div class="input-group">
                                                        <input name="ethereum_password" type="password" class="form-control form-control-lg" minlength="4" maxlength="20" />
                                                        <span class="input-group-append"><span class="input-group-text"><i class="fas fa-lock"></i></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="new_etherum_account" class="tab-pane form_area">
                                                <section class="card ethereum_account_box" style="display:none;">
                                                    <header class="card-header">
                                                        <h2 class="card-title">Success!</h2>
                                                    </header>
                                                    <div class="card">
                                                        <div class="modal-wrapper">
                                                            <div class="modal-icon">
                                                                <i class="fas fa-check" style="color: #47a447;"></i>
                                                            </div>
                                                            <div class="modal-text">
                                                                <h4 class="ethereum_alert_msg"></h4>
                                                                <p class="ethereum_accountid" style="font-weight:bold;font-size:15px;color:#0099e6"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--<footer class="card-footer">
                                                        <div class="row">
                                                            <div class="col-md-12 text-right">
                                                                <button type="submit" class="btn btn-primary mt-2 jca-login-button">Sign In</button>
                                                            </div>
                                                        </div>
                                                    </footer>-->
                                                </section>

                                                <div class="form-group mb-3 password_area">
                                                    <div class="clearfix">
                                                        <label class="float-left">Password</label>
                                                    </div>
                                                    <div class="input-group">
                                                        <input name="ethereum_password" type="password" class="form-control form-control-lg" minlength="4" maxlength="20" />
                                                        <span class="input-group-append"><span class="input-group-text"><i class="fas fa-lock"></i></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row distributor_input" style="display:none;"></div><!-- 유통업자 추가 정보-->
                                        </div>
                                        <div class="row password_area">
                                            <div class="col-sm-8">
                                                <div class="checkbox-custom checkbox-default">
                                                    <input id="AgreeTerms" name="agreeterms" type="checkbox"/>
                                                    <label for="AgreeTerms">I agree with <a href="#">terms of use</a></label>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 text-right">
                                                <button type="submit" class="btn btn-primary mt-2 jca-join-button">Sign Up</button>
                                            </div>
                                        </div>
                                        <div class="alert alert-danger" style="margin-top:10px;">
                                            <strong> ※ ETH 계정 비밀번호 분실 시 재확인 또는 변경이
                                                불가하오니, 필히 별도의 공간에 메모하여 보관하
                                                시기 바랍니다.<br>
                                                ※ ETH 계정은 생성된 PC에서만 사용가능합니다.</strong>
                                        </div>
                                        <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                                            <span>or</span>
                                        </span>
                                        <p class="text-center">Already have an account?  <a href="/user/signin">Sign In!</a></p>
                                    </div>
                                </div>
                                <div id="w4-confirm" class="tab-pane div_form_area">
                                    <div class="form-group row">
                                        <div class="alert alert-info info_text col-sm-12" style="margin-top:10px;">
                                            <span  style="font-weight:bold;font-size:15pt;">심사신청이 완료되었습니다.</span><br><br>
                                            <strong> ※ 신청시 심사기간은 2~5일 정도 소요됩니다.<br>
                                                ※ 승인시 저작권자 페이지를 이용하실 수 있습니다.</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <ul class="pager">
                            <li class="previous" style="display:none;">
                                <a><i class="fas fa-angle-left"></i> Previous</a>
                            </li>
                            <li class="finish float-right complete_account_btn" style="display:none;">
                                <a>Finish</a>
                            </li>
                            <li class="next ">
                                <a>Next <i class="fas fa-angle-right"></i></a>
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        <!--</div>-->
    </div>
</section>
<script>
$(function(){

    // validation summary
/*    var $summaryForm = $("#summary-form");
    $summaryForm.validate({
        errorContainer: $summaryForm.find( 'div.validation-message' ),
        errorLabelContainer: $summaryForm.find( 'div.validation-message ul' ),
        wrapper: "li"
    });*/

    $('.account_type_list .account_type').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        var index = $('.account_type_list .account_type').index(this);

        $('.account_type .plan').removeClass('most-popular');
        $('.account_type .plan').eq(index).addClass('most-popular');
        $('.account_type a.btn').removeClass('btn-primary').addClass('btn-light');
        $('.account_type a.btn').eq(index).addClass('btn-primary').removeClass('btn-light');
        $('input[name=account_type]').val($('.account_type').eq(index).attr('jca-account-type'));

    });

    $('.next').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();

        var step = parseInt($('input[name=step_progress]').val());

        switch(step){
            case 1 :
                if(!$("input[name=account_type]").val()){
                    alert('계정유형을 선택해 주세요.');
                    return false;
                }else{
                    $.onchain_proc('account/list', '', account_call_back, 'on');
                    $('.wizard-steps li.nav-item').eq(step - 1).removeClass('active').addClass('completed');
                    $('input[name=step_progress]').val(2)
                    $('.progress-indicator').css('width','50%');
                    $('.tab-pane').removeClass('active');
                    $('.div_form_area').eq(step).addClass('active');
                    $('.nav-item').eq(step).addClass('active');
                    if($("input[name=account_type]").val() == 'D'){
                        putHtml();
                    }else{
                        $('.distributor_input').empty();
                    }

                    $('.previous').fadeIn();
                    $('.next').fadeIn();
                    $('.finish').fadeOut();

                }
                break;
            case 2 :
                  if(!$('input[name=account_id]').val()){
                      alert('이더리움계정이 생성되지 않았습니다.');
                      return false;
                  }else{

                      $('.wizard-steps li.nav-item').eq(step - 1).removeClass('active').addClass('completed');
                      $('input[name=step_progress]').val(3)
                      $('.progress-indicator').css('width','100%');
                      $('.tab-pane').removeClass('active');
                      $('.div_form_area').eq(step).addClass('active');
                      $('.nav-item').eq(step).addClass('active');
                      $('.previous , .finish').fadeIn();
                      $('.next').fadeOut();
                  }

                break;
        }
    });

    $('.previous').on('click' ,function(event){
        event.stopPropagation();
        event.preventDefault();

        var step = parseInt($('input[name=step_progress]').val()) - 1;
        $('input[name=step_progress]').val(step);
        $('.process_account .tab-pane').removeClass('active')
        $('.process_account .tab-pane').eq(step - 1).addClass('active');
        //$('input[name=step_progress]').val(step)
    });

    /* 계정생성 */
    $("form.jca-join-form").submit(function(event){
        event.stopPropagation();
        event.preventDefault();
        /*
        console.log($('form.jca-join-form ').serialize());
        return;*/


        if($('input[name=account_type]').val() == 'D')  $('body').addClass('loading-overlay-showing');

        $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> 계정신청중....').fadeIn();

        var account_generate_type  = $('form.jca-join-form input[name=account_generate_type]').val();

        if(account_generate_type == 1){

            var verfity_params = {
                    'account_id'        : $('#exists_etherum_account select[name=ethereum_account] option:selected').val(),
                    'join_type'       : $('input[name=account_type]').val()
            }
            var exists_account_data = $.runsync('/user/vertify', verfity_params , 'json' , false);

            if(exists_account_data.code == 200){
                var params = {
                    accountId : $('#exists_etherum_account select[name=ethereum_account] option:selected').val() ,
                    password  : $('#exists_etherum_account input[name=ethereum_password]').val()
                }
                $.onchain_proc('account/login', params, exist_accout_back, 'on');
            }else{
                if($('input[name=account_type]').val() == 'D')  $('body').removeClass('loading-overlay-showing');
                $('.loading_box').fadeOut();
                commonMessage({
                    title: 'info',
                    message: exists_account_data.message,
                    type: 'info',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
            }

        }else{
            var password = $(this).find("#new_etherum_account input[name=ethereum_password]").val();
            var params = {
                password : password
            }
            $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> 계정생성중....').fadeIn();

            $.onchain_proc('account/generate', params, join_call_back, 'on');
        }

    });

    $('.complete_account_btn').on('click' , function(event){
        event.stopPropagation();
        event.preventDefault();

        var step = parseInt($('input[name=step_progress]').val());
        $('.wizard-steps li.nav-item').eq(step - 1).removeClass('active').addClass('completed');

        alert('신청이 완료되었습니다.');
        document.location.replace('/user/signin');

    });

    $('.account_tab li').on('click' , function (event) {
        event.stopPropagation();
        event.preventDefault();
        var id = $(this).find('a').attr('href');
        $('.account_tab li').find('a').removeClass('active');
        $('.account_tab li').removeClass('active');
        $(this).find('a').addClass('active');
        $(this).addClass('active');
        $('.form_area').removeClass('active');
        $('form.jca-join-form input[name=account_generate_type]').val($(this).index() + 1);
        $(id).addClass('active');
        if($(this).index() == 1){
            $('#exists_etherum_account select[name="ethereum_account"]').prop('required', false);
            $('#new_etherum_account input[name="ethereum_password"]').prop('required', true).prop('disabled', false);
            $('#exists_etherum_account input[name="ethereum_password"]').prop('required', false).prop('disabled', true);
        }else{
            $('#exists_etherum_account select[name="ethereum_account"]').prop('required', true);
            $('#exists_etherum_account input[name="ethereum_password"]').prop('required', true).prop('disabled', false);
            $('#new_etherum_account input[name="ethereum_password"]').prop('required', false).prop('disabled', true);
        }
    });


});

function join_call_back(data){

    if(data.resultCode == 0){

        var step = parseInt($('input[name=step_progress]').val());
        var account_type = $('input[name=account_type]').val();
        var params = {
            accountId : data.accountId.substr(2) ,
            password  : $('#new_etherum_account input[name=ethereum_password]').val()
        }

        var login_data = $.runsync(http_api_url + '/account/login', params, 'json', false);


        if(login_data.resultCode == 0) {

            var auth_params = {role : account_type}
            var auth_data = $.runsync(http_api_url +'/msp/authRequest', auth_params, 'json', false);

            $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> 계정신청중....').fadeIn();
            if(auth_data.resultCode == 0 && auth_data.result == 'succeed'){
                $('input[name=account_id]').val(data.accountId.substr(2));
                var user_put_data = $.runsync('/user/putUser' , $('form.jca-join-form').serialize() , 'json', true);
                if($('input[name=account_type]').val() == 'D')  $('body').removeClass('loading-overlay-showing');
                if(user_put_data.code == 200) {

                    var logout_data =$.runsync(http_api_url +'/account/logout' , '', 'json', false);
                    if(logout_data.resultCode == 0) {
                        //$('.scroll-to-top').click();
                        $('.ethereum_alert_msg').text('');
                        $('.ethereum_accountid').text('');
                        switch (data.state) {
                            case 'new'  :
                                $('.loading_box , .password_area , .distributor_input').fadeOut(function () {
                                    $('.ethereum_alert_msg').text('신규 생성된 이더리움 계정');
                                    $('.ethereum_accountid').text(data.accountId.substr(2));
                                    $('.ethereum_account_box').fadeIn();

                                });
                                break;
                            case 'exists' :
                                $('.loading_box').fadeOut(function () {
                                    $('.ethereum_alert_msg').text('기존에 존재하는 이더리움 계정이 있습니다.');
                                    $('.ethereum_accountid').text(data.accountId.substr(2));
                                    $('.ethereum_account_box').fadeIn();

                                });
                                break;
                        }

                    }
                }else{
                    $('.loading_box').fadeOut();
                    commonMessage({
                        title: 'Error',
                        message: 'DB error',
                        type: 'error',
                        addclass: 'stack-bar-bottom',
                        stack: stack_bar_bottom,
                    });
                }




            }else{
                $('.loading_box').fadeOut();
                //alert(auth_data.resultMessage);
                commonMessage({
                    title: 'Error',
                    message: auth_data.resultMessage,
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
            }
        }else{
            $('.loading_box').fadeOut();
            //alert(login_data.resultMessage);
            commonMessage({
                title: 'Error',
                message: login_data.resultMessage,
                type: 'error',
                addclass: 'stack-bar-bottom',
                stack: stack_bar_bottom,
            });

        }
         //document.location.replace('/');
    }else{
        $('.loading_box').fadeOut();
        commonMessage({
            title: 'Error',
            message: data.resultMessage,
            type: 'error',
            addclass: 'stack-bar-bottom',
            stack: stack_bar_bottom,
        });
        //alert(data.resultMessage);
    }

}

function account_call_back(data) {

    var account_list = [];
    $("select[name=ethereum_account] option").remove();
    if (data.list.length > 0 && data.resultCode == 0) {
        $.each(data.list, function (key, val) {
            $("select[name=ethereum_account]").append($('<option>', {
                value: val.substr(2),
                text: val.substr(2),
            }));
        });
        $('.account_tab li').eq(0).click();
        $('#exists_etherum_account').addClass('active');
        $('#exists_etherum_account input[name=ethereum_password]').attr('required' , true);
        $('#new_etherum_account input[name=ethereum_password]').removeAttr('required' , false);
        $('form.jca-join-form input[name=account_generate_type]').val(1);

    } else {
        $("select[name=ethereum_account]").append($('<option>', {
            value: "",
            text: '생성된 ethereum account가 없습니다.',
        }));
        $('.account_tab li').eq(1).click();
        $('#new_etherum_account').addClass('active');
        $('form.jca-join-form input[name=account_generate_type]').val(2);
        $('#exists_etherum_account input[name=ethereum_password]').attr('required' , true);
        $('#new_etherum_account input[name=ethereum_password]').removeAttr('required' ,false);
    }
}


function exist_accout_back(data){

    if(data.resultCode == 0) {
        var account_type = $('input[name=account_type]').val();
        var auth_params = {role : account_type}

        var auth_data = $.runsync(http_api_url +'/msp/authRequest', auth_params, 'json', false);
        if(auth_data.resultCode == 0 && auth_data.result == 'succeed'){

            $('input[name=account_id]').val($('#exists_etherum_account select[name=ethereum_account] option:selected').val());
            var user_put_data = $.runsync('/user/putUser' , $('form.jca-join-form').serialize() , 'json', true);

            if(user_put_data.code == 200) {

                var logout_data = $.runsync(http_api_url + '/account/logout', '', 'json', false);

                if (logout_data.resultCode == 0) {

                    $('.loading_box').fadeOut(function () {
                        if($('input[name=account_type]').val() == 'D')  $('body').removeClass('loading-overlay-showing');
                        $('.ethereum_alert_msg').text('');
                        $('.ethereum_accountid').text('');
                        $('li.next').click();
                    });
                }
            }else{
                $('.loading_box').fadeOut();
                commonMessage({
                    title: 'Error',
                    message: 'DB error',
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
            }
        }else{
            $('.loading_box').fadeOut();
            commonMessage({
                title: 'Error',
                message: auth_data.resultMessage,
                type: 'error',
                addclass: 'stack-bar-bottom',
                stack: stack_bar_bottom,
            });
        }
    }else{
        $('.loading_box').fadeOut();
        commonMessage({
            title: 'Error',
            message: data.resultMessage,
            type: 'error',
            addclass: 'stack-bar-bottom',
            stack: stack_bar_bottom,
        });
    }
}


function putHtml(){

    var  in_html = '<div class="col-lg-12">\n' +
    '                                                <section class="card">\n' +
    '                                                    <div class="card-body" style="display: block;font-size:11px;">\n' +
    '                                                        <header class="card-header" style="margin;5px 0;">\n' +
    '                                                            <h2 class="card-title " style="font-size:14px;">유통사업자 부가정보입력 (회사정보)</h2>\n' +
    '                                                        </header><br>\n' +
    '                                                        <div class="validation-message"></div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">법인명 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="company_cname" class="form-control" title="법인명을 입력해 주세요." placeholder="법인명" required="">\n' +
    '                                                            </div>\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">대표자명 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="company_name" class="form-control" title="대표자명 입력해 주세요." placeholder="대표자명" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사업자번호 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-10">\n' +
    '                                                                <input type="text" name="company_number" class="form-control" title="사업자등록번호를 입력해 주세요.." placeholder="사업자등록번호" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사업(업태) <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="company_type" class="form-control" title="사업종류(업태)를 입력해 주세요" placeholder="사업종류(업태)를 입력해 주세요" required="">\n' +
    '                                                            </div>\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사업(종목) <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="company_kind" class="form-control" title="사업종류(종목)를 입력해 주세요" placeholder="사업종류(종목)를 입력해 주세요" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사업장주소 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-10">\n' +
    '                                                                <input type="text" name="company_addr" class="form-control" title="사업장주소를 입력해 주세요" placeholder="사업장주소를 입력해 주세요" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">통신판매번호 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-10">\n' +
    '                                                                <input type="text" name="company_com_number" class="form-control" title="통신판매번호를 입력해 주세요" placeholder="통신판매번호를 입력해 주세요" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '\n' +
    '                                                        <header class="card-header">\n' +
    '                                                            <h2 class="card-title" style="font-size:14px;">유통사업자 부가정보입력 (사이트정보)</h2>\n' +
    '                                                        </header><br>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사이트명 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-10">\n' +
    '                                                                <input type="text" name="site_name" class="form-control" title="사이트명을 입력해 주세요" placeholder="사이트명을 입력해 주세요" required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">사이트주소 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-10">\n' +
    '                                                                <input type="text" name="site_url" class="form-control" title="사이트주소를 입력해 주세요." placeholder="사이트주소를 입력해 주세요." required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +

    '                                                        <header class="card-header" >\n' +
    '                                                            <h2 class="card-title" style="font-size:14px;">유통사업자 부가정보입력 (담장자정보)</h2>\n' +
    '                                                        </header><br>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">담당자명 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="manage_name" class="form-control" title="담당자명을 입력해 주세요." placeholder="담당자명을 입력해 주세요." required="">\n' +
    '                                                            </div>\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">이메일 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="manage_email" class="form-control" title="담당자명을 입력해 주세요." placeholder="이메일를 입력해 주세요." required="">\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +
    '                                                        <div class="form-group row">\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">연락처 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="manage_hp" class="form-control" title="연락처를 입력해 주세요." placeholder="연락처를 입력해 주세요." >\n' +
    '                                                            </div>\n' +
    '                                                            <label class="col-sm-2 control-label text-sm-right pt-2">팩스 <span class="required">*</span></label>\n' +
    '                                                            <div class="col-sm-4">\n' +
    '                                                                <input type="text" name="manage_fax" class="form-control" title="팩스를 입력해 주세요." placeholder="팩스를 입력해 주세요." >\n' +
    '                                                            </div>\n' +
    '                                                        </div>\n' +        
    '                                                </section>\n' +
    '                                            </div>';

    $('.distributor_input').html(in_html).fadeIn();

}
</script>


