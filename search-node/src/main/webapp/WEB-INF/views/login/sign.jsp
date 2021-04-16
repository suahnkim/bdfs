<%@ page language="java" contentType="text/html; charset=UTF-8"
    pageEncoding="UTF-8"
%><%
	response.setHeader("Cache-Control","no-store");   
    response.setHeader("Pragma","no-cache");
%><!DOCTYPE HTML>
<html lang="en">

<head>
    <!-- meta -->
    <meta charset="utf-8">
    <title>Search NODE Reference</title>
    <!-- link -->
    <link rel="shortcut icon" href="../../images/icon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="../../css/common_css.css">
    <link rel="stylesheet" type="text/css" href="../../css/fontawesome.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S"
        crossorigin="anonymous">
    <link rel="apple-touch-icon" sizes="57x57" href="../../images/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../../images/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../../images/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../../images/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../../images/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../../images/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../../images/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../../images/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../../images/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../../images/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../images/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="../../images/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../images/icon/favicon-16x16.png">
    <!-- js -->
    <script type="text/javascript" src="../../js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="../../js/jquery-1.10.2.js"></script>
    <script type="text/javascript" src="../../js/jquery-ui.js"></script>
    <script type="text/javascript" src="../../js/calendar_beans_v2.2.js"></script>
    <script type="text/javascript" src="../../js/common_ui.js"></script>
</head>
<script>
	$( document ).ready(function() {
		var defLoginId = getCookie("def_login_id");
		if(defLoginId != null) {
			$("#login_id").val(defLoginId);
		}
		
		
		console.log("getCookie def_login_id ["+getCookie("def_login_id")+"]");
	});
	
	function checkEnterKey() {
		if(event.keyCode == 13) {
			login();
		}
	}

	function login() {
		var login_id = $("#login_id").val();
		var password = $("#password").val();
		var rember_account = $("#rember_account").is(":checked");
		
		if(login_id == "") {
			alert("ID를 입력하세요.");return;
		}
		if(password == "") {
			alert("비밀번호를 입력하세요.");return;
		}
		
		var reqData = {};
		reqData['user_id'] = login_id;
		reqData['password'] = password;
		reqData['rember_account'] = rember_account;
		
		//console.log(reqData);
		var reqDataStr = JSON.stringify(reqData);
		
		$.ajax({
	        url:'ajax_sign_in_proc.do',
	        type:'post',
	        data: reqDataStr,
	        success:function(data){
	        	console.log(data);
	        	if(data['status'] == 'success') {
	        		$(location).attr('href', '../service/list.do');
	        		
	        		if(rember_account == true) {
	        			setCookie("def_login_id", login_id);
	        			console.log("def_login_id ["+login_id+"]");
	        		} else {
	        			setCookie("def_login_id", "");
	        			console.log("def_login_id []");
	        		}
	        		
	        		
	        	} else {
	        		alert("로그인ID와 비밀번호를 확인하세요.");	
	        	}
	        },
	        error:function(request, status, error){
	        	console.log(request);
	        	alert("HTTP STSTUS : " + request.status);
	        }
	    });
	}
	
	function setCookie(cookie_name, value, days) {
		  var exdate = new Date();
		  exdate.setDate(exdate.getDate() + days);
		  // 설정 일수만큼 현재시간에 만료값으로 지정

		  var cookie_value = escape(value) + ((days == null) ? '' : ';    expires=' + exdate.toUTCString());
		  document.cookie = cookie_name + '=' + cookie_value;
		}
		
		
	function getCookie(cookie_name) {
		  var x, y;
		  var val = document.cookie.split(';');

		  for (var i = 0; i < val.length; i++) {
		    x = val[i].substr(0, val[i].indexOf('='));
		    y = val[i].substr(val[i].indexOf('=') + 1);
		    x = x.replace(/^\s+|\s+$/g, ''); // 앞과 뒤의 공백 제거하기
		    if (x == cookie_name) {
		      return unescape(y); // unescape로 디코딩 후 값 리턴
		    }
		  }
		}
	
</script>
<body class="menu_min">

    <div id="head_wrap">
        <h1><span>Search NODE (Reference)</span></h1>
    </div>

    <div id="body_wrap" class="menu_exp">

        <!-- Description : smenu_xxx 1ìë¦¬ë 1Depth, ë¤ 2ìë¦¬ë 2Depthë¡ ì¤ì í©ëë¤.  -->
		<div id="menu_id" class="smenu_000"></div>

        <div id="body_boxr">

            <!-- S : Page Main Area -->
            <div class="body_signbox">

                <div class="box_type">

                    <div class="box_sbody">
                        <dl class="attributes_e">
                            <dt style="height:10px;"></dt>
                            <dd>
                                <div class="i_group">
                                    <dl class="i_group_e">
                                        <dt class="txt_right" style="width:180px;padding-right:20px;">Admin ID</dt>
                                        <dd>
                                            <input id="login_id" type="text" class="input_type i_size_200" style="float:left;">
                                        </dd>
                                    </dl>
                                    <dl class="i_group_e">
                                        <dt class="txt_right" style="width:180px;padding-right:20px;">Admin PWD</dt>
                                        <dd>
                                            <input id="password" type="password" class="input_type i_size_200" style="float:left;" onkeypress="checkEnterKey();">
                                        </dd>
                                    </dl>
                                    <dl class="i_group_e" style="clear:both;">
                                        <dt></dt>
                                        <dd style="padding-left:190px;">
                                            <input type='checkbox' class='s_checkbox' id="rember_account" checked="checked"><label
                                                for="rember_account"><em></em>Remeber Account</label>
                                        </dd>
                                    </dl>
                                </div>
                            </dd>
                        </dl>
                    </div>
                    <div class="box_bottom" style="padding:25px 0 20px 0;">
                        <div class="btn_box_center">
                            <a href="javascript:login();" class="btn_type_bg btn_type_pink_none_fill">Sign in</a>
                        </div>
                    </div>
                    
                </div>

            </div>

        </div>

    </div>

</body>

</html>