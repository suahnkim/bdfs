<link rel="stylesheet" type="text/css" href="<?php echo MC_ASSETS_PATH; ?>/css/login.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
<div class="wrap">
	<div class="container_login">
		<div class="wrap_login">
			<h2 class="logo"><?php echo(SITE_NAME)?></h2>
		</div>
	</div>
</div>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/user.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>

<script>
	$(document).ready(function() {
		var agent = navigator.userAgent.toLowerCase();

		if ( (navigator.appName == 'Netscape' && agent.indexOf('trident') != -1) || (agent.indexOf("msie") != -1)) {
     		alert('Internet Explore에서는 정상적인 지원이 안되므로, Chrome이나 Edge를 사용해 주세요.');
		}
	});
</script>