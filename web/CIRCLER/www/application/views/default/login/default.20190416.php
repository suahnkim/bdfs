<link rel="stylesheet" type="text/css" href="<?php echo MC_ASSETS_PATH; ?>/css/login.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="wrap">
	<div class="container_login">
		<div class="wrap_login">
			<h2 class="logo"><?php echo(SITE_NAME)?></h2>
			<aticle class="login_contents" id="login_depth_1">
				<div class="select_list">
					<h4>ETH 계정 리스트</h4>
					<h5>이더리움 계정과 이메일을 연동하신 후 서비스 이용이 가능합니다.</h5>
					<div class="jca-account-list">
						리스트 불러오는 중...
					</div>
				</div>
				<button type="button" class="btn_login_type01">신규 ETH 계정 생성 및 이메일 연동</button>
				<div class="login_foot">
					<a href="#" class="btn01">서비스이용약관</a>
					<a href="#" class="btn02">개인정보취급방침</a>
				</div>
			</aticle>
			<aticle class="login_contents hide" id="login_depth_2">
				<form class="login_form">
					<span class="label_input">인증할 이메일 입력</span>
					<span class="label_sub">ETH 계정과 연동하실 이메일을 입력해주세요.</span>
					<div class="wrap_input">
						<input class="input" type="text" name="email"  value="ETH 계정과 연동하실 이메일을 입력해주세요">
						<span class="focus_input"></span>
					</div>
					<span class="label_input">ETH 계정에 사용할 비밀번호</span>
					<div class="wrap_input">
						<input class="input" type="password" name="password"  value="password">
						<span class="focus_input"></span>
					</div>
				</form>
				<ul class="note_box">
					<li>※ ETH 계정 비밀번호 분실시 재확인 또는 변경이 불가하오니, 필히 별도의 공간에 메모하여 보관하시기 바랍니다.</li>
					<li>※ ETH 계정은 생성된 PC에서만 사용가능합니다.</li>
				</ul>
				<button type="button" class="btn_login_type01">인증메일 발송하기</button>
				<div class="login_foot">
					<a href="#" class="btn01">서비스이용약관</a>
					<a href="#" class="btn02">개인정보취급방침</a>
				</div>
			</aticle>
		</div>
	</div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/user.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
<script language="javascript">
$(document).ready(function(){
	$.account_list();
});
</script>