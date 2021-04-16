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
		
		loadContentTypeStat();
	});
	
	var statGenre = null;
	var statCType = null;
	
	function loadContentTypeStat() {
		var url = '/ccsearch/v1/group_by_count.do?index=ccontents_original&key=meta_container.metadata.genre';
		console.log("URL :: ["+url+"]");
		$.ajax({
	        url: url,
	        type:'get',
	        success:function(data){
	        	console.log(data);
	        	statGenre = data;
	        	//printCContentList(data);
	        	
	        	loadGenreStat();
	        	
	        },
	        error:function(request, status, error){
	        }
	    });
		
	}
	
	function loadGenreStat() {
		var url = '/ccsearch/v1/group_by_count.do?index=ccontents_original&key=meta_container.content-type';
		console.log("URL :: ["+url+"]");
		$.ajax({
	        url: url,
	        type:'get',
	        success:function(data){
	        	console.log(data);
	        	statCType = data;
	        	//printCContentList(data);	   
	        	printContentTypeChart();
	        	printGenreChart();
	        },
	        error:function(request, status, error){
	        }
	    });
	}
	
	function printContentTypeChart() {
		var dataPoints = [];
		
		for(var i=0; i<statCType['result'].length; i++) {
			var data = {};
			
			var cType = statCType['result'][i]['key'];
			if(CONTENT_TYPE_MAP[cType] != null) {
				cType = CONTENT_TYPE_MAP[cType];
			}
			
			data['label'] = cType;
			data['y'] = statCType['result'][i]['value'];
			dataPoints.push(data);
		}
		
		var options = {
                animationEnabled: true,
                title: {
                    text: "Category"
                },
                data: [              
                {
                    type: "column",
                    dataPoints: dataPoints
                }
                ]
            };
        
			$("#chartContainer").CanvasJSChart(options);
	}
	function printGenreChart() {
		var dataPoints = [];
		for(var i=0; i<statGenre['result'].length; i++) {
			var data = {};
			
			var genreKey = statGenre['result'][i]['key'];
			if(GENRE_MAP[genreKey] != null) {
				genreKey = GENRE_MAP[genreKey];
			}
			
			data['label'] = genreKey;
			data['y']     = statGenre['result'][i]['value'];
			dataPoints.push(data);
		}
		
		var options = {
                animationEnabled: true,
                title: {
                    text: "Genre"              
                },
                data: [              
                {
                    type: "column",
                    dataPoints: dataPoints
                }
                ]
            };
            $("#chartContainer1").CanvasJSChart(options);
	}
	
	
	
	
</script>
<body>

	<%@include file="../common/top.jsp"%>

	<div id="menu_id" class="smenu_102"></div>
	<div id="body_wrap" class="menu_exp">

		<div id="body_boxl">

			<!-- S : Slide Sub Menu -->
			<%@include file="../common/left.jsp"%>
		</div>

		<div id="body_boxr">

			<!-- S : Page Title -->
			<div class="ptitle fontw_500">
				<h2>Original Analytics</h2>
			</div>

            <div id="chartContainer" style="height: 400px; width: 100%;margin-top:50px;"></div>
			<div class="graph_cover"></div>

			<div style="position:absolute;margin-top:50px;right:42px;z-index:999;">
			<select class="input_type i_select i_size_100">
				<option>Video</option>
			</select>
			</div>
			
			<div id="chartContainer1" style="height: 400px; width: 100%;margin-top:50px;"></div>
            <div class="graph_cover1"></div>
            <script type="text/javascript" src="../../js/jquery.canvasjs.min.js"></script>
            <style>
            .canvasjs-chart-credit {display:none;}
			.graph_cover {position:absolute;z-index:100;background:#f2f3f5;width:100px;height:10px;margin-top:-12px;}
			.graph_cover1 {position:absolute;z-index:100;background:#f2f3f5;width:100px;height:10px;margin-top:-12px;}
            </style>
            
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