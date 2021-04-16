<aticle class="login_contents hide" id="login_depth_2">
	<form class="login_form">
		<input type="hidden" name="account" value="<?php echo($request_params->account)?>">
		<span class="label_input">인증할 이메일 입력</span>
		<span class="label_sub">ETH 계정과 연동하실 이메일을 입력해주세요.</span>
		<div class="wrap_input">
			<input class="input" type="email" name="email" value="" placeholder="ETH 계정과 연동하실 이메일을 입력해주세요" title="이메일" required="required" maxlength="40">
			<span class="focus_input"></span>
		</div>
		<span class="label_input">ETH 계정에 사용할 비밀번호</span>
		<div class="wrap_input">
			<input class="input" type="password" name="password" value="" placeholder="사용할 비밀번호를 입력해주세요" title="비밀번호" maxlength="30" required="required" minlength="5">
			<span class="focus_input"></span>
		</div>
	</form>
	<ul class="note_box">
		<li>※ ETH 계정 비밀번호 분실시 재확인 또는 변경이 불가하오니, 필히 별도의 공간에 메모하여 보관하시기 바랍니다.</li>
		<li>※ ETH 계정은 생성된 PC에서만 사용가능합니다.</li>
	</ul>
	<button type="button" class="btn_login_type01 email_send_btn">인증메일 발송하기</button>
	<div class="login_foot">
		<a href="#" class="btn01">서비스이용약관</a>
		<a href="#" class="btn02">개인정보취급방침</a>
	</div>
	<script language="javascript">
	$(document).ready(function(){
		var login_form = new commonFormValidation({
			form: ".login_form",
		});

		$(".login_form").submit(function(event){
			event.stopPropagation();
			event.preventDefault();

			if(!login_form.formValidate(true)) return;

			$.ajax({
				url: "/user/signup",
				dataType: "json",
				type: "post",
				data: $(this).serialize(),
				beforeSend: function( xhr ){
					$('#loading_ajax').show();
				}, complete: function( jqxhr, textStatus ){
					$('#loading_ajax').hide();
				}, success: function( data, textStatus, jqXHR ){
					if(data.code == "200"){
						$.login_page_load('/user/login_view/step_email', 'email='+data.data.email);
					}else{
						if(typeof data.message != undefined){
							alert(data.message);
						}else{
							alert("알 수 없는 에러 입니다.");
						}
					}
				}, error: function( jqXHR, textStatus, errorThrown ){
					alert("통신 중 에러가 발생하였습니다.");
				}
			});
		});

		$("button.email_send_btn").off("click").on("click", function(event){
			event.stopPropagation();
			event.preventDefault();

			$(".login_form").submit();
		});

		$("input[name=email]").focus();
	});
	</script>
</aticle>