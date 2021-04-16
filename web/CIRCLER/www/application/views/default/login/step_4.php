<aticle class="login_contents hide" id="login_depth_4">
	<form class="login_form">
		<input type="hidden" name="user_info_id" value="<?php echo($request_params->user_info_id)?>">
		<input type="hidden" name="account" value="<?php echo($request_params->account)?>">
		<span class="label_input">선택된 ETH 계정</span>
		<div class="wrap_input pick">
			<input class="input" type="text" name="tmp_account" value="<?php echo($request_params->account)?>" disabled>
			<span class="focus_input"></span>
		</div>
		<span class="label_input">비밀번호</span>
		<span class="label_sub">ETH 계정생성시 입력하신 비밀번호 입력해주세요</span>
		<div class="wrap_input">
			<input class="input" type="password" name="password" value="" placeholder="사용할 비밀번호를 입력해주세요" title="비밀번호" maxlength="30" required="required" minlength="5" onkeydown="if(event.keyCode == 13) $('.login_btn').click() ">
			<span class="focus_input"></span>
		</div>
	</form>
	<button type="button" class="btn_login_type01 login_btn">서비스 로그인하기</button>
	<a href="javascript:;" class="txt_back back_btn" style="display:block; text-decoration:underline; font-size:12px; font-weight:500; color:#999; text-align:center; padding-top:10px;">이전으로 돌아가기</a>
	<div class="login_foot">
		<a href="#" class="btn01">서비스이용약관</a>
		<a href="#" class="btn02">개인정보취급방침</a>
	</div>
	<script language="javascript">
	$(document).ready(function(){
		$("a.back_btn").off("click").on("click", function(event){
			event.stopPropagation();
			event.preventDefault();

			$.login_page_load('/user/login_view/step_1');
		});
		var login_form = new commonFormValidation({
			form: ".login_form",
		});

		$(".login_form").submit(function(event){
			event.stopPropagation();
			event.preventDefault();

			if(!login_form.formValidate(true)) return;

			var account = $(this).find("input[name=account]").val();
			var password = $(this).find("input[name=password]").val();


			//$.onchain_proc('c', 'app.js account --balance --address '+account+' --password '+password, balance_call_back);
            var params = {accountId :  account , password : password};
            $('#loading_ajax').show();
            $.onchain_proc('account/login', params, balance_call_back, '');
            //$('#loading_ajax').hide();
		});

		$("button.login_btn").off("click").on("click", function(event){
			event.stopPropagation();
			event.preventDefault();

			$(".login_form").submit();
		});

		$("input[name=password]").focus();
	});

	function balance_call_back(data){

		if(data.resultCode == 0){


            $.ajax({
                url: "/user/signin",
                dataType: "json",
                type: "post",
                data: $(".login_form").serialize(),
                beforeSend: function( xhr ){

                }, complete: function( jqxhr, textStatus ){
                    //$('#loading_ajax').hide();
                }, success: function( data, textStatus, jqXHR ){

                    if(data.code == "200"){
                        var account = $('form.login_form').find("input[name=tmp_account]").val();
                        var password = $('form.login_form').find("input[name=password]").val();
                        var params = {
                            k : account +','+ '127.0.0.1,55442,'+ password
                        }
                        var daemon_data =  $.runsync( 'http://localhost:54777' , params , 'json' , false);
                        if(daemon_data.result == 1) {
                            location.replace('/');
						}else if (daemon_data.result == 2) {
							alert("IPFS Deamon을 확인해 주세요.");
							$("a.back_btn").click();
						}						
                    }else{
                        $('#loading_ajax').hide();
                        if(typeof data.message != undefined){
                            alert(data.message);
                        }else{
                            alert("알 수 없는 에러 입니다.");
                        }
                    }
                }, error: function( jqXHR, textStatus, errorThrown ){
                    $('#loading_ajax').hide();
                    alert("통신 중 에러가 발생하였습니다.");
                }
            });


		}else{
			alert("로그인이 실패하였습니다.\n\r\n\r다시 확인바랍니다.");
			$("a.back_btn").click();
		}
	}
	</script>
</aticle>