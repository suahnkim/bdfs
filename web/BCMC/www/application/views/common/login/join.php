<section class="body-sign ">
    <div class="center-sign">
        <a href="/" class="logo float-left">
            <a href="/" class="logo float-left"><img src="<?php echo COM_ASSETS_PATH; ?>/img/logo.png" height="54" alt="Mediablockchain" /></a>
        </a>	
        <div class="panel card-sign">
            <div class="card-title-sign mt-3 text-right">
                <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign Up</h2>
            </div>
            <div class="card-body">
                <form class="jca-join-form">
                    <div class="form-group mb-3 loading_box" style="display:none;">
                        <div class="clearfix0  " >
                            <label class="float-left ethereum_account_txt"></label>
                        </div>
                    </div>

                    <section class="card ethereum_account_box" style="display:none;">
                        <header class="card-header">
                            <h2 class="card-title">Success!</h2>
                        </header>
                        <div class="card-body">
                            <div class="modal-wrapper">
                                <div class="modal-icon">
                                    <i class="fas fa-check" style="color: #47a447;"></i>
                                </div>
                                <div class="modal-text">
                                    <h4 class="ethereum_alert_msg"></h4>
                                    <p class="ethereum_accountid"></p>
                                </div>
                            </div>
                        </div>
                        <footer class="card-footer">
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary mt-2 jca-login-button">Sign In</button>
                                </div>
                            </div>
                        </footer>
                    </section>

                    <div class="form-group mb-3 password_area">
                        <div class="clearfix">
                            <label class="float-left">Password</label>
                        </div>
                        <div class="input-group">
                            <input name="ethereum_password" type="password" class="form-control form-control-lg" minlength="4" maxlength="20" required/>
                            <span class="input-group-append">
								<span class="input-group-text"><i class="fas fa-lock"></i></span>
							</span>
                        </div>
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
                        <strong> ??? ETH ?????? ???????????? ?????? ??? ????????? ?????? ?????????
                            ???????????????, ?????? ????????? ????????? ???????????? ?????????
                            ?????? ????????????.<br>
                            ??? ETH ????????? ????????? PC????????? ?????????????????????.</strong>
                    </div>
                    <span class="mt-3 mb-3 line-thru text-center text-uppercase">
								<span>or</span>
							</span>
                    <p class="text-center">Already have an account? <a href="/user/singin">Sign In!</a></p>

                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-3 mb-3">&copy; Copyright 2017. All Rights Reserved.</p>
    </div>
</section>
<!-- end: page -->
<script language="javascript">
    $(document).ready(function(){
        /* ???????????? */
        $("form.jca-join-form").submit(function(event){
            event.stopPropagation();
            event.preventDefault();

            var password = $(this).find("input[name=ethereum_password]").val();
            var params = {
                password : password
            }
            $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> ???????????????....').fadeIn();

            $.onchain_proc('account/generate', params, join_call_back, 'on');
        });

        /* ????????? */
        $('form.jca-join-form .jca-login-button').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> ????????????....').fadeIn();
            var accountId = $(".ethereum_accountid").text();
            var password = $('form.jca-join-form input[name=ethereum_password]').val();
            var params = {
                accountId : accountId ,
                password : password
            }

            $.onchain_proc('account/login', params, login_call_back, 'on');
        });

    });
    function join_call_back(data){

        if(data.resultCode == 0){
            $('.ethereum_alert_msg').text('');
            $('.ethereum_accountid').text('');
            switch(data.state){
                case 'new'  :
                    $('.loading_box , .password_area').fadeOut(function () {
                        $('.ethereum_alert_msg').text('?????? ????????? ???????????? ??????');
                        $('.ethereum_accountid').text(data.accountId.substr(2));
                        $('.ethereum_account_box').fadeIn();
                        $('.ethereum_account_box').fadeIn();
                    });
                    break;
                case 'exists' :
                    $('.loading_box').fadeOut(function () {
                        $('.ethereum_alert_msg').text('????????? ???????????? ???????????? ????????? ????????????.');
                        $('.ethereum_accountid').text(data.accountId.substr(2));
                        $('.ethereum_account_box').fadeIn();
                    });
                    break;
            }
            //document.location.replace('/');
        }else{
            alert(data.resultMessage);
        }
    }

    function login_call_back(data){
        $('.loading_box').fadeOut();
        if(data.resultCode == 0){
            var accountId = $(".ethereum_accountid").text();
            var password = $('form.jca-join-form input[name=ethereum_password]').val();
            var params = {
                account : accountId ,
                password : password
            }

            var json_data = $.runsync('/user/signin' , params , 'json' , true);

            if(json_data.code == 200){
                document.location.replace('/');
            }
        }else{
            alert(data.resultMessage);
        }
    }

</script>