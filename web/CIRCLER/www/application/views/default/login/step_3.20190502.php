<aticle class="login_contents hide" id="login_depth_3">
	<form class="login_form">
		<input type="hidden" name="account" value="<?php echo($request_params->account)?>">
		<span class="label_input">선택된 ETH 계정</span>
		<div class="wrap_input pick">
			<input class="input" type="text" name="text" value="<?php echo($request_params->account)?>" disabled>
			<span class="focus_input"></span>
		</div>
		<span class="label_input">인증할 이메일 입력</span>
		<span class="label_sub">ETH 계정과 연동하실 이메일을 입력해주세요</span>
		<div class="wrap_input">
			<input class="input" type="email" name="email" value="" placeholder="ETH 계정과 연동하실 이메일을 입력해주세요" title="이메일" required="required" maxlength="40">
			<span class="focus_input"></span>
		</div>
	</form>
	<ul class="note_box">
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
/*			alert: function(msg, break_obj){
				$(break_obj).siblings('span.focus_input').html(msg);
			},*/
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