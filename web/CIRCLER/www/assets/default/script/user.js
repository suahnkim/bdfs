$.account_list = function(){
    $.onchain_proc('account/list', '', account_call_back, 'on');
}

function account_call_back(data){
	var account_list = [];
	$.each(data.list, function(key, val){
		account_list.push(val.substr(2));
	});
	if(account_list.length > 0){
		$.ajax({
			url: "/user/check_eth_account",
			type: "get",
			dataType: "json",
			timeout: 1000,
			cache: false,
			data: "account_list="+account_list.join(","),
			beforeSend: function(xhr){
			}, complete: function(jqxhr, textStatus){
			}, success: function(json, textStatus, jqXHR){
				var append_html = '';
				if(json.data.list.length > 0){
					$.each(json.data.list, function(key, val){
						append_html += '<a href=\"javascript:;\" class=\"login_select_box\" jca-data-user-id=\"'+val.user_info_id+'\" jca-data-account=\"'+val.account+'\" jca-data-email=\"'+val.email+'">';
						append_html += '<dl>';
						append_html += '<dd>'+val.account+'</dd>'+(val.email.length > 0 ? '<dd class=\"txt_email\">이메일 : '+val.email+'</dd>' : '<dd class=\"txt_email_nolink\">이메일 : 연동안됨</dd>');
						append_html += '</dl>';
						append_html += '</a>';
					});
				}else{
					append_html += '<div class="none_list">';
					append_html += '<p><img src="/assets/default/img/login/no_id.png" alt="계정추가"></p>';
					append_html += '<p>생성된 ETH 계정이 없습니다. </p>';
					append_html += '</div>';
				}
				$("div.jca-account-list").html(append_html);
				$("a.login_select_box").on("click", function(event){
					event.stopPropagation();
					event.preventDefault();

					var user_info_id = $(this).attr("jca-data-user-id");
					var account = $(this).attr("jca-data-account");
					var email = $(this).attr("jca-data-email");

					if(email.length > 0){
						$.login_page_load('/user/login_view/step_4', 'user_info_id='+user_info_id+'&account='+account+'&email='+email);
					}else{
						$.login_page_load('/user/login_view/step_3', 'account='+account);
					}
				});
			}, error : function(xhr){
				console.log(xhr.responseText);
				try{
					var data = JSON.parse(xhr.responseText);
					
					if( data.message ) {
						alert(data.message);
					} else {
						alert('알 수 없는 오류가 발생하였습니다.');
					}
				}catch(e){
					alert('알 수 없는 오류가 발생하였습니다.');
				}
				return;
			}
		});
	}else{
		var append_html = '<ul><li>생성된 ETH 계정이 없습니다.</li></ul>';
		$("div.jca-account-list").html(append_html);
	}
}

$(document).ready(function(){
    $.login_page_load('/user/login_view/step_1', '');
});