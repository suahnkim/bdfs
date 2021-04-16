
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php include_once(dirname(__FILE__) . "/../inc/top.php"); ?>
</head>
<body>
<div class="logincon">
    <div class="loginbox">
        <div class="loginlay">
            <div class="logoimg">
                <img src="<?php echo COM_ASSETS_PATH; ?>/img/logo_210_65.png" alt="">
            </div>
            <div class="logotxt">
                Contents Management System
            </div>
            <div class="blockchainLogin" >
                <div class="select">
                    <select name="ethereum_account" style="width:100%; height:65px; margin-bottom:10px; padding-left:10px; font-size:16px; color:#666">
                    </select>
                </div>
                <input type="password" name="pwd" placeholder="패스워드를 입력해주세요" class="logininp_pw" onkeypress="if(event.keyCode == 13) $('.blockchain_btn_login').click();">
                <button class="btn_login blockchain_btn_login" >로그인</button>
            </div>
            <div class="adminLogin" style="display:none;">
                <h1  style="margin:10px;">관리자 2차인증</h1>
                <input type="password" name="manager_pwd" placeholder="관리자 비빌번호를 입력해주세요" class="logininp_pw" onkeypress="if(event.keyCode == 13) $('.admin_btn_login').click();">
                <button class="btn_login admin_btn_login" >로그인</button>
            </div>
        </div>
    </div>
</div>
</body>
<div id="loading_ajax">
    <div class="img"><img src="<?php echo COM_ASSETS_PATH; ?>/img/loading.gif" alt="로딩" /></div>
</div>
</html>

<script language="javascript">

    $(document).ready(function () {
        $.account_list();

        $('.blockchain_btn_login').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            if($('select[name=ethereum_account] option').length > 0){
                if(!$('input[name=pwd]').val()){
                    alert('패스워드를 입력해 주세요.');
                    $('input[name=pwd]').focus();
                    return false;
                }
                var params = {accountId : $('select[name=ethereum_account] option:selected').val() , password : $('input[name=pwd]').val()};
                $.onchain_proc('account/login', params, login_call_back, 'on');
            }else{
                alert('이더리움 계정이 없습니다.');
            }
        });


        $('.admin_btn_login').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();
            $('#loading_ajax').show();

            if(!$('input[name=manager_pwd]').val()){
                alert('관리자 패스워드를 입력해주세요.');
                $('input[name=manager_pwd]').focus();
                $('#loading_ajax').hide();
                return false;
            }
            var params = {manager_id : $('select[name=ethereum_account] option:selected').val() , manager_pwd : $('input[name=manager_pwd]').val()};
            var data = $.runsync('/user/getMangerInfo', params, 'json', true);
            if(data.code == 200){
                document.location.href = '/';
            }else{
                alert(data.message);
            }
            $('#loading_ajax').hide();

        });
    });

    $.account_list = function(){
        $.onchain_proc('account/list', '', account_call_back, 'on');
    }

    function account_call_back(data){

        var account_list = [];
        $("select[name=ethereum_account] option").remove();
        if(data.list.length > 0 && data.resultCode == 0){
            $.each(data.list, function(key, val){
                $("select[name=ethereum_account]").append($('<option>', {
                    value: val.substr(2),
                    text: val.substr(2),
                }));
            });
        }else{
            $("select[name=ethereum_account]").append($('<option>', {
                value: "",
                text: '생성된 ethereum account가 없습니다.',
            }));
        }
    }

    function login_call_back(data){
        if(typeof  data != 'undefined'){
            if(data.resultCode == 0){
                $('.blockchainLogin').fadeOut(function () {
                    $('.adminLogin').fadeIn();
                });
            }else{
                alert(data.resultMessage);
            }
        }
    }
</script>