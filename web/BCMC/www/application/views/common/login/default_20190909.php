
<section class="body-sign">
	<div class="center-sign">
		<a href="/" class="logo float-left"><img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/logo_new.png" height="54" alt="Mediablockchain" /></a>
		<div class="panel card-sign">
			<div class="card-title-sign mt-3 text-right">
				<h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign In</h2>
			</div>
			<div class="card-body">
                <div class="alert alert-danger login-alert-message" style="display:none;">
                    <p class="m-0"></p>
                </div>
				<form class="jca-login-form">
                    <div class="form-group mb-3 loading_box" style="display:none;">
                        <div class="clearfix0  " >
                            <label class="float-left ethereum_account_txt"></label>
                        </div>
                    </div>
                    <div class="form-group mb-3">

                    </div>
                    <div class="form-group mb-3">
                        <label>account type</label>
                        <div class="input-group">
                            <select name="account_type" class="form-control" required>
                                <?php foreach(array_splice($this->menu->list , 1 ,3) as $key => $val){?>
                                <option value="<?php echo($key)?>"><?php echo(ENUM_ACCOUNT_TYPE::_print($key))?></option>
                                <?php }?>
                            </select>
                            <span class="input-group-append">
								<span class="input-group-text"><i class="fas fa-clipboard-list"></i></span>
							</span>
                        </div>
                    </div>
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
					<div class="form-group mb-3">
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
					<div class="row">
						<div class="col-sm-8">
							<div class="checkbox-custom checkbox-default">
								<input id="RememberMe" name="rememberme" type="checkbox"/>
								<label for="RememberMe">Remember Me</label>
							</div>
						</div>
						<div class="col-sm-4 text-right">
							<button type="submit" class="btn btn-primary mt-2 jca-login-button">Sign In</button>
						</div>
					</div>
                    <span class="mt-3 mb-3 line-thru text-center text-uppercase">
								<span>or</span>
					</span>

                    <p class="text-center">Don't have an account yet? <a href="/user/generate">Sign Up!</a></p>
				</form>
			</div>
		</div>

	</div>
    <!--<div class="ui-pnotify stack-bar-bottom" style="width: 70%; left: 0px; bottom: 0px; opacity: 0.333097;"><div class="notification ui-pnotify-container notification-danger" style="min-height: 16px;"><div class="ui-pnotify-closer" style="cursor: pointer; visibility: hidden;"><span class="fas fa-times" title="Close"></span></div><div class="ui-pnotify-sticker" style="cursor: pointer; visibility: hidden;"><span class="fas fa-pause" title="Stick"></span></div><div class="ui-pnotify-icon"><span class="fas fa-times"></span></div><h4 class="ui-pnotify-title">USR_1001</h4><div class="ui-pnotify-text">존재하지 않는 아이디입니다.</div><div style="margin-top: 5px; clear: both; text-align: right; display: none;"></div></div></div>-->
</section>

<script language="javascript">
$(document).ready(function(){
	$.onchain_proc('account/list', '', account_call_back, 'on');
	$("form.jca-login-form").submit(function(event){
		event.stopPropagation();
		event.preventDefault();

        $('.login-alert-message').fadeOut(100);
		var address = $(this).find("select[name=ethereum_account] option:selected").val();
		var password = $(this).find("input[name=ethereum_password]").val();
		var params = {
            accountId : address ,
            password : password
        }

        $('.loading_box').html('<img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/loading.gif"  alt="loading" /> 로그인중....').fadeIn();
		$.onchain_proc('account/login', params, login_call_back, 'on');
	});
});

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
    //console.log(data);
    $('.loading_box , .login-alert-message').fadeOut();

    //console.log(data);
    if(data.resultCode == 0){
        var account_type = $('form.jca-login-form').find('select[name=account_type] option:selected').val();
        var address = $('form.jca-login-form').find("select[name=ethereum_account] option:selected").val();
        var password = $('form.jca-login-form').find("input[name=ethereum_password]").val();

        var params = {
            account : address ,
            password : password,
            role : account_type,
            authority : master_eth_account == address ? "master" : "",
        }

        if(master_eth_account == address){
            var json_data = $.runsync('/user/signin' , params , 'json' , false);
            if(json_data.code == 200){
                document.location.replace('/');
            }
        }else{
            var auth_params = {
                target : address ,
                role : account_type
            }
            //console.log(auth_params);
            var auth_data = $.runsync(http_api_url +'/msp/verify', auth_params, 'json', true);
           //console.log(auth_data);
            if(auth_data.resultCode == 0 && auth_data.result == 'succeed'){

                var daemon_params = {
                    k : address +','+ '127.0.0.1,55442,'+ password
                }
                var daemon_data  = $.runsync( 'http://localhost:54777' , daemon_params , 'json' , true);


                var json_data = $.runsync('/user/signin' , params , 'json' , true);

                if(json_data.code == 200){
                    document.location.replace('/');
                }
            }else{
                $.runsync(http_api_url +'/account/logout' , '', 'json', true);
                //$('.login-alert-message').html(auth_data.resultMessage).fadeIn();
                commonMessage({
                    title: 'Error',
                    message: auth_data.resultMessage,
                    type: 'error',
                    addclass: 'stack-bar-bottom',
                    stack: stack_bar_bottom,
                });
            }
        }
    }else{
        //$('.login-alert-message').html(data.resultMessage).fadeIn();
        commonMessage({
            title: 'Error',
            message: data.resultMessage,
            type: 'error',
            addclass: 'stack-bar-bottom',
            stack: stack_bar_bottom,
        });
    }
}
</script>