<link rel="stylesheet" type="text/css" href="<?php echo MC_ASSETS_PATH; ?>/css/login.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="wrap">
	<div class="container_login">
		<div class="wrap_login">
			<h2 class="logo"><?php echo(SITE_NAME)?></h2>
			<aticle class="login_contents">
				<form class="login_form">
				<input type="hidden" name="token" value="<?php echo(@$request_params->token)?>">
				<input type="hidden" name="account" value="<?php echo(@$token->account)?>">
				<input type="hidden" name="password" value="<?php echo(@$token->password)?>">
				<input type="hidden" name="email" value="<?php echo(@$token->email)?>">
				<div class="create_step01">
					<p class="txt_bk18">인증이메일 : <span class="txt_pl18"><?php echo($token->email)?></span></p>
				</div>
				<ul class="note_box">
					<li>※ 이더리움 계정과 인증된 이메일 연동을 위하여 인터넷 품질에 따라 5~10초 정도 소요됩니다.</li>
					<li>※ 연동된 계정은 생성한 PC에서만 사용가능합니다.</li>
				</ul>
				<button type="button" class="btn_login_type01 jca-account-create">인증된 ETH 계정 연동</button>
				<div class="login_foot">
					<a href="#" class="btn01">서비스이용약관</a>
					<a href="#" class="btn02">개인정보취급방침</a>
				</div>
				</form>
			</aticle>
		</div>
	</div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/auth.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>