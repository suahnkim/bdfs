/* Add here all your JS customizations */
var isRunningAjax = false;

window.commonAjax = function(param){
	param = param || {};
	var ajax_url = param.url || null;
	var ajax_type = param.type || null;
	var ajax_data = param.data || null;
	var ajax_datatype = param.dataType || "json";
	var ajax_fnc_success = param.success || null;
	var ajax_fnc_error = param.error || null;
	var ajax_fnc_beforeSend = param.beforeSend || null;
	var ajax_fnc_complete = param.complete || null;
	window.defaultAjaxStatus = window.defaultAjaxStatus || null;
	if(window.defaultAjaxStatus != null && window.defaultAjaxStatus.readyState != 4){
		window.defaultAjaxStatus.abort();
	}
	window.defaultAjaxStatus = $.ajax({
		url: ajax_url,
		dataType: ajax_datatype,
		type: ajax_type,
		data: ajax_data,
		beforeSend: function(xhr){
			if(typeof ajax_fnc_beforeSend == "function") ajax_fnc_beforeSend(xhr);
		}, complete: function(jqxhr, textStatus){
			if(typeof ajax_fnc_complete == "function") ajax_fnc_complete(jqxhr, textStatus);
		}, success: function(data, textStatus, jqXHR){
			if(typeof ajax_fnc_success == "function"){
				ajax_fnc_success(data, textStatus, jqXHR);
			}else{
				alert(data.message);
			}
		}, error: function(jqXHR, textStatus, errorThrown){
			console.log("[ERROR]");
			console.log(jqXHR.responseText);
			if(typeof ajax_fnc_error == "function"){
				ajax_fnc_error( jqXHR, textStatus, errorThrown );
			}else{
				try{
					var data = $.parseJSON(jqXHR.responseText);
					if(jqXHR.status == "401"){
						document.location.href = "/user/signin?r_url=" + encodeURIComponent(location.pathname); 
					}else{
						if(typeof data.message != undefined){
							alert(data.message);
						}else{
							alert("알 수 없는 에러 입니다.");
						}
					}
				}catch(e){
					alert("통신 중 에러가 발생하였습니다.");
				}
			}
		}
	});
}

var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
var stack_bar_bottom = {"dir1": "up", "dir2": "right", "spacing1": 0, "spacing2": 0};

window.commonMessage = function(param){
	var notice = new PNotify({
		title: param.title,
		text: param.message,
		type: param.type,
		addclass: param.addclass,
		stack: param.stack,
		width: "70%",
		delay: 2000,
	});
}