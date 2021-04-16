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
	<script type="text/javascript" src="../../js/global_init.js"></script>
</head>

<script>
	$( document ).ready(function() {
		{
			$("#content_type").empty();
			$("#content_type").append('<option value="">All</option>');	
			for(var i=0; i<CONTENT_TYPE.length; i++) {
				var contentType = CONTENT_TYPE[i];
				$("#content_type").append('<option value="'+contentType['code']+'">'+contentType['name']+'</option>');	
			}	
		}
		{
			$("#genre").empty();
			$("#genre").append('<option value="">All</option>');	
			for(var i=0; i<GENRE.length; i++) {
				var genre = GENRE[i];
				$("#genre").append('<option value="'+genre['code']+'">'+genre['name']+'</option>');	
			}	
		}
		
		
		searchCcontents(1);
	});
	
	
	function checkEnterKey() {
		if(event.keyCode == 13) {
			searchCcontents(1);
		}
	}
	
	function searchCcontents(nowPage) {
		var contentType = $("#content_type").val();
		var genre       = $("#genre").val();
		var fromDate    = $("#fromDate").val();
		var toDate      = $("#toDate").val();
		var keyword     = $("#keyword").val();
		var rowPerPage  = $("#rowPerPage").val();
		var sortField   = $("#sort").val().split('-')[0];
		var sortOrder   = $("#sort").val().split('-')[1];
		
		
		//console.log("["+contentType+"]["+genre+"]["+fromDate+"]["+toDate+"]["+keyword+"]");
		
		var url = '/ccsearch/v1/search.do?searchTarget=ori&ccStatus=all&nowPage='+nowPage+'&rowPerPage=' + rowPerPage + '&sortField='+sortField+'&sortOrder='+sortOrder;
		if(contentType != '') {
			url = url + '&sk_content_type=' + contentType;
		}
		if(genre != '') {
			url = url + '&sk_genre=' + genre;
		}
		if(fromDate != '') {
			url = url + '&fromDate=' + fromDate + ' 00:00:00';
		}
		if(toDate != '') {
			url = url + '&toDate=' + toDate + ' 23:59:59';
		}
		if(keyword != '') {
			//url = url + '&sk_title=' + keyword;
			//url = url + '&sk_synopsis=' + keyword;
			url = url + '&keyword=' + keyword;
		}
		

		console.log("URL :: ["+url+"]");
		$.ajax({
	        url: url,
	        type:'get',
	        success:function(data){
	        	console.log(data);
	        	printCContentList(data);
	        },
	        error:function(request, status, error){
	        }
	    });
	}
	
	function printCContentList(data) {
		
		
		var result = data.result;
		var pagination = data.pagination;
		$("#totalNum").html(numberWithCommas(pagination['search_count']));
		
		$("#tbody").empty();
		for(var i=0; i<result.length; i++) {
			var ccontent = result[i];
			var ccid        = ccontent['ccid'];
			var version     = ccontent['version'];
			var status      = ccontent['status'];
			
			
			var ccidVer     = ccid + '-' + version;
			var fContent    = ccontent['meta_container'][0];
			var fMeta       = fContent['metadata'];
			var contentType = fContent['content-type'];
			var title       = fMeta['title'];
			var synopsis    = fMeta['synopsis'];
			var genre       = fMeta['genre'] + "";
			var metaQty     = ccontent['meta_container'].length;
			var ownerId     = ccontent['owner_id'];
			var ownerRegDate= ccontent['owner_reg_date'];
			
			var score       = ccontent['_score'];
			if(score == "NaN") {
				score = "";
			} else {
				score = "("+score+")";
			}
			
			//console.log("111-----------------["+ccid+"]["+version+"]>>> ["+status+"]------------------");
			
			
			var checkbox = '';
			if(status == 'index') {
				checkbox = '<p class="input_wrap"><input value="'+ccidVer+'" type="checkbox" class="s_checkbox chk i_check" name="ccid_ver_chk" value="" id="ccChk'+i+'"><label for="ccChk'+i+'"><em></em></label></p>';	
			}
			
			//console.log(ccontent);
			var trHtml ='<tr>' +
				'<td>'+contentType+'</td>' +
				'<td class="txt_left"><p class="txt_elip" title="'+ccidVer+'">'+ccidVer+'</p></td>' +
		        '<td class="txt_left"><p class="txt_elip"><a href="detail.jsp?ccid='+ccid+'&version='+version+'">'+title+' '+score+'</a></p></td>' +
		        '<td class="txt_left">' +
		        '    <p class="txt_elip">' + synopsis +'</p>' +
		        '</td>' +
		        '<td>'+genre+'</td>' +
		        '<td>'+metaQty+'</td>' +
				'<td>'+ownerId+'</td>' +
				'<td>'+ownerRegDate+'</td>' +
		        '<td>' +
		        	checkbox +
				'</td>' +
				'</tr>';
			$("#tbody").append(trHtml);
		}
		
		
		var pageRange = pagination['page_range'];
		$("#page_ul").empty();		
		if(pageRange[0] != 1) {
			$("#page_ul").append('<li><a href="javascript:searchCcontents(1)" class="pf" title="First"><span class="pf_p fas fa-angle-double-left"></span></a></li>');
			$("#page_ul").append('<li><a href="javascript:searchCcontents('+(pageRange[0]-1)+')" class="pf" title="Previous"><span class="pf_pre fas fa-angle-left"></span></a></li>');
		}
		for(var i=0; i<pageRange.length; i++) {
			var selPage = '';
			if(pagination['now_page'] == pageRange[i]) {
				selPage = 'pon';
			}
			$("#page_ul").append('<li><a href="javascript:searchCcontents('+pageRange[i]+')" class="pnum '+selPage+'">'+pageRange[i]+'</a></li>');
		}
		
		if(pageRange[pageRange.length-1] < pagination['total_page']) {
			$("#page_ul").append('<li><a href="javascript:searchCcontents('+(pageRange[pageRange.length-1]+1)+')" class="pf" title="Next"><span class="pf_nex fas fa-angle-right"></span></a></li>');
			$("#page_ul").append('<li><a href="javascript:searchCcontents('+pagination['total_page']+')" class="pf" title="Last"><span class="pf_f fas fa-angle-double-right"></span></a></li>');
		}	
	}
	
	
	
	
	
	
	function updateCcStatus() {
		var chkList = $("input:checkbox[name='ccid_ver_chk']:checked");
		console.log(chkList.length);
		
		if(chkList.length == 0) {
			alert("컨텐츠를 선택해주세요.");
			return;
		}
		if(confirm("본 복합 콘텐츠를 서비스 콘텐츠로 복사하시겠습니까?")) {
			
			
			var ccStatusList = [];
			for(var i=0; i<chkList.length; i++) {
				var ccidVer = chkList[i].value;
				ccid    = ccidVer.split("-")[0];
				version = ccidVer.split("-")[1];
				
				var ccStatus = {};
				ccStatus['ccid']    = ccid;
				ccStatus['version'] = version;
				ccStatus['status']  = 'ready';
				ccStatusList.push(ccStatus);
			}
			
			
			var reqData = {};
			reqData['cc_status_list'] = ccStatusList;
			
			console.log(reqData);
			var reqDataStr = JSON.stringify(reqData);
						
			var url = '/ccsearch/v1/transfer_cc_status.do';
			console.log("URL :: ["+url+"]");
			$.ajax({
		        url: url,
		        type:'post',
		        data: reqDataStr,
		        success:function(data){
		        	console.log(data);
		        	var result = data['cc_status_list'][0];
		        	var rStatus = result['result'];
		        	console.log(result['result']);
		        	
		        	if(rStatus == "Success") {
		        		alert("본 복합 콘텐츠를 서비스 콘텐츠로 복사했습니다.");
		        		location.reload();
		        	} else {
		        		alert("내부 오류 입니다.");
		        	}
		        	
		        	//Success
		        },
		        error:function(request, status, error){
		        }
		    });	
			
		}
		
	}
	
	
	
	
</script>
<body>

	<%@include file="../common/top.jsp"%>

	<div id="menu_id" class="smenu_101"></div>
	<div id="body_wrap" class="menu_exp">

		<div id="body_boxl">

			<!-- S : Slide Sub Menu -->
			<%@include file="../common/left.jsp"%>
		</div>

		<div id="body_boxr">

			<!-- S : Page Title -->
			<div class="ptitle fontw_500">
				<h2>Original Contents</h2>
			</div>

			<!-- S : Page Main Area -->
			<div id="body_sbox">		

				<div class="box_type">

					<div class="box_sbody">
						<dl class="attributes_e">
							<dd>
								<div class="i_group">
									<dl class="i_group_e">
										<dt>Category1</dt>
										<dd>
											<select id="content_type" class="input_type i_select i_size_110">
											</select>
										</dd>
									</dl>
									<dl class="i_group_e">
										<dt>Genre</dt>
										<dd>
											<select id="genre" class="input_type i_select i_size_110">
											</select>
										</dd>
									</dl>
									<dl class="i_group_e">
										<dt>Period</dt>
										<dd>
											<label for="from"></label>
											<input id="fromDate" type="text" name="from" class="from i_period input_type i_size_110" placeholder="Start">
											<label for="to" class="period_bar"> ~ </label>
											<input id="toDate" type="text" name="to" class="to i_period input_type i_size_110" placeholder="End">
										</dd>
									</dl>
									<dl class="i_group_e">
										<dt>Keyword</dt>
										<dd>
											<input id="keyword" type="text" class="input_type i_size_300" placeholder="Keyword" style="float:left;" onkeypress="checkEnterKey();">
											
										</dd>
									</dl>
								</div>
							</dd>
						</dl>
					</div>
					<div class="box_bottom">
						<div class="btn_group_left">
							<a href="" class="btn_type_sm btn_type_none">Reset</a>
						</div>
						<div class="btn_group_right btn_box_bottom float_right">
							<a href="javascript:searchCcontents(1)" class="btn_type_sm btn_type_pink_fill">Search</a>
						</div>
					</div>
                </div>
                
                <div class="result_num_wrap">
					<div class="result_num_left">Contents Number : <span id="totalNum">0</span></div>
					
					<div class="sort_con1">
						<div class="i_group">
							<dl class="i_group_e" >
								<dt></dt>
								<dd>
									<select id="rowPerPage" class="input_type i_select i_size_110">
										<!-- <option value="2">2 View</option> -->
										<!-- <option value="3">3 View</option> -->
										<!-- <option value="5">5 View</option> -->
										
										<option value="20">20 View</option>
										<option value="50">50 View</option>
										<option value="100">100 View</option>
										<option value="200">200 View</option>
									</select>
								</dd>
							</dl>
							<dl class="i_group_e" >
								<dt></dt>
								<dd>
									<select id="sort" class="input_type i_select i_size_150">
										<option value="owner_reg_date-desc">등록일 (Descending)</option>
										<option value="owner_reg_date-asc">등록일 (Ascending)</option>
										<option value="title-desc">제목순 (가나다)</option>
										<option value="title-asc">제목순 (ABC)</option>
										<option value="score-desc">관련도순</option>
									</select>
								</dd>
							</dl>
						</div>
					</div>
						
                </div>

				<div class="box_type">
					<table class="tb_type_1">
						<caption>table</caption>
						<colgroup>
							<col style="width:100px">
							<col style="width:10%">
							<col style="width:20%">
							<col>
							<col style="width:7%">
							<col style="width:7%">
                            <col style="width:7%">
                            <col style="width:7%">
							<col style="width:40px;">
						</colgroup>
						<tr>
							<th>Cate.1</th>
							<th>CCID/Ver</th>
							<th>Title</th>
							<th>Synopsis</th>
							<th>Genre</th>
							<th>Contents</th>							
							<th>Production</th>
							<th>Reg. Date</th>
                            <th>
								<p class="input_wrap"><input type='checkbox' class='s_checkbox chk_all_top' name="ptype" value="" id="t0"><label for="t0"><em></em></label></p>
							</th>
                        </tr>
                        
                        <tbody id="tbody">
                        </tbody>
                        
					</table>
				</div>

				<a href="javascript:updateCcStatus()" class="btn_type_bg btn_type_pink_none_fill" style="position:absolute;right:30px;">서비스 콘텐츠로 복사</a>

				<div class="page_wrap">
					<ul class="page_list" id="page_ul">
						
					</ul>
                </div>                

			</div>

		</div>

	</div>

	<!-- S : Menu Control  -->
	<div id="menu_control">
		<i class="fa fa-chevron-left" title="Menu Hide"></i>
		<i class="fa fa-chevron-right" title="Menu Show"></i>
	</div>

	<!-- S : Top Slide -->
	<div id="scrolltop"><i class="fas fa-arrow-alt-circle-up"></i></div>

</body>

</html>