$(document).ready(function(){
	$("button.jca-account-create").on("click", function(event){
		if($("aticle > form.login_form > input[name=account]").val().length < 1){
			$.account_create($("aticle > form.login_form > input[name=password]").val());
		}else{
			account_create_back();
		}
	});
});

$.account_create = function(password){
	$('#loading_ajax').show();
	var params = {password : password};
	$.onchain_proc('account/generate', params   , account_create_back , 'on');
}

function account_create_back(data){

	if(typeof(data) != "undefined"){
		var account = data.accountId.replace('0x', '');
		$("aticle > form.login_form > input[name=account]").val(account);
	}

	$.ajax({
		url: "/auth/result",
		dataType: "json",
		type: "post",
		data: $("form.login_form").serialize(),
		beforeSend: function( xhr ){
		}, complete: function( jqxhr, textStatus ){
			$('#loading_ajax').hide();
		}, success: function( data, textStatus, jqXHR ){
			if(data.code == "200"){
				$.login_page_load('/auth/auth_view/result', 'email='+data.data.email+'&account='+data.data.account);
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
}