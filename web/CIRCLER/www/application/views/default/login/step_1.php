<aticle class="login_contents hide" id="login_depth_1">
	<div class="select_list">
		<h4>ETH 계정 리스트</h4>
		<h5>이더리움 계정과 이메일을 연동하신 후 서비스 이용이 가능합니다.</h5>
		<div class="jca-account-list">
			리스트 불러오는 중...
		</div>
	</div>
	<button type="button" class="btn_login_type01 jca-account-create">신규 ETH 계정 생성 및 이메일 연동</button>

	<a href="/main/programDownload" class="btn_login_type01 jca-program-download" target="_blank" style="margin-top:10px;">프로그램 다운로드</a>

	<div class="login_foot">
		<a href="#" class="btn01">서비스이용약관</a>
		<a href="#" class="btn02">개인정보취급방침</a>
	</div>
	<script language="javascript">
	$(document).ready(function(){
		$.account_list();

		$("button.jca-account-create").one("click", function(event){
			event.stopPropagation();
			event.preventDefault();

			$.login_page_load('/user/login_view/step_2');
		});
	});
	</script>
</aticle>