<aticle class="login_contents hide" >
	<div class="create_step01">
		<p class="txt_bk18">인증이메일 : <span class="txt_pl18"><?php echo($request_params->email)?></span></p>
	</div>
	<ul class="note_box">
		<li>※ 계정은 생성된 PC에서만 사용가능합니다.</li>
	</ul>
	<div class="login_step03">
		<p class="txt_bk18 center">연동완료 !!</p>
		<p class="txt_gr12">지갑KEY : <?php echo($request_params->account)?></p>
	</div>
	<button type="button" class="btn_login_type01" onclick="location.replace('/user/signin')">로그인 화면으로 이동하기</button>
	<div class="login_foot">
		<a href="#" class="btn01">서비스이용약관</a>
		<a href="#" class="btn02">개인정보취급방침</a>
	</div>
</aticle>