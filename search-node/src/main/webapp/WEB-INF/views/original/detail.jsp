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
	<script type="text/javascript" src="../../js/jstree.js" ></script>
	
	<link rel="stylesheet" href="https://cdn.plyr.io/1.8.2/plyr.css">
    <script src="https://cdn.plyr.io/1.8.2/plyr.js"></script>
    <script src="https://cdn.jsdelivr.net/hls.js/latest/hls.js"></script>
    
    
    <style>

		.demo { overflow:auto; border:1px solid silver; min-height:300px; }
		</style>
	<link rel="stylesheet" href="../../css/themes/default/style.css" />
</head>
<script>

/**


//Player Expend
	

 
*/



	$( document ).ready(function() {
		ccid    = getParameterByName("ccid");
		version = getParameterByName("version");
		loadInitData();
		
		
		
	});
	

	var ccid = '';
	var version = '';
	
	function loadInitData() {
		var url = '/ccsearch/v1/search.do?searchTarget=ori&ccStatus=all&ccid='+ccid+'&version='+version;
		console.log("URL :: ["+url+"]");
		$.ajax({
	        url: url,
	        type:'get',
	        success:function(data){
	        	console.log(data);
	        	printTab(data);
	        	
	        },
	        error:function(request, status, error){
	        }
	    });
	}
	
	var gMetaArr  = null;
	function printTab(data) {
		
		var ccontent = data['result'][0];
		var metaArr  = ccontent['meta_container'];
		gMetaArr     = ccontent['meta_container'];
		var ccid     = ccontent['ccid'];
		var version  = ccontent['version'];
		
		$("#tab_ul").empty();
		$("#tab_container").empty();
		for(var i=0; i<metaArr.length; i++) {
			var content = metaArr[i];
			var meta    = content['metadata'];
			var target  = content['target'] + '';
			var synopsis = meta['synopsis'];
			
			
			if(target != '') {
				target = target.replace(/,/g, '<br/>');
			}
			if(synopsis != '') {
				synopsis = synopsis.replace(/\n/g, '<br/>');
			}
			
			

			var contentType = content['content-type'];
			var title       = meta['title'];
			var genre       = meta['genre'] + "";
			var castArr    = meta['cast'];
			var crewArr    = meta['crew'];
			var artworkArr = meta['artwork'];
			
			var castStr = '';
			for(var j=0; j<castArr.length; j++) {
				var name = castArr[j]['name'];
				var cName = castArr[j]['cast_name'];

				if(castStr == '') {
					castStr = name + "("+cName+")";
				} else {
					castStr = castStr + ", " + name + "("+cName+")";
				}
			}
			
			var crewStr = '';
			for(var j=0; j<crewArr.length; j++) {
				var name  = crewArr[j]['name'];
				var role  = crewArr[j]['role'];
				
				if(crewStr == ''){
					crewStr = name + "("+role+")";	
				} else {
					crewStr = crewStr + ", " + name + "("+role+")";
				}
			}
			
			var artWorkHtml = '';
			for(var j=0; j<artworkArr.length; j++) {
				var path  = artworkArr[j]['file_name'];
				var rep   = artworkArr[j]['rep'];
				var repString = "";
				if(rep == "true") {
					repString = "rep";
				}
				
				artWorkHtml = artWorkHtml + 
				'<li>' +                       
		        '	<img src="/ccsearch/v1/ccontent/'+ccid+'/'+version+'/'+path+'" class="'+repString+'" alt=""> '+
		        '</li>';
			}
			
			$("#tab_ul").append("<li>["+contentType+"] "+title+"</li>");
			
			var tabHtml = '' +
		        '<div id="c'+(i+1)+'" class="cbox tab_box"> '+
		        '	<!-- S : Page Title --> '+
				'    <div class="ptitle fontw_500"> '+
		        '        <h2>'+title+'</h2> '+
		        '    </div> '+
		        '    <!-- S : Page Main Area --> '+
		        '    <div id="body_sbox">		 '+
		        '        <h3>Descriptor</h3> '+
		        '        <div class="box_type"> '+
		        '            <table class="tb_type_1 fontc_pink"> '+
		        '                <caption>table</caption> '+
		        '                <colgroup> '+
		        '                    <col style="width:220px"> '+
		        '                    <col> '+
		        '                </colgroup> '+
		        '                <tr> '+
		        '                    <th>Property</th> '+
		        '                    <th>Data</th> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Category (Content-Type)</td> '+
		        '                    <td class="txt_left">'+contentType+'</td> '+
		        '                </tr> '+
		        //'                <tr> '+
		        //'                    <td>Format</td> '+
		        //'                    <td class="txt_left">.MP4</td> '+
		        //'                </tr> '+
		        '                <tr> '+
		        '                    <td>Target</td> '+
		        '                    <td class="txt_left"> '+
		        '                       '+ target +
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>CCID</td> '+
		        '                    <td class="txt_left">'+ccid+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Version</td> '+
		        '                    <td class="txt_left">'+version+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Complex Contents<br/>Structure</td> '+
		        '                    <td class="txt_left"><div id="tree-'+version+'" class="demo"></div></td> '+
		        '                </tr> '+
		        
		        '            </table> '+
		        '        </div> '+
		        '        <h3>Basic Metadata</h3> '+
		        '        <div class="box_type"> '+
		        '            <table class="tb_type_1 fontc_pink"> '+
		        '                <caption>table</caption> '+
		        '               <colgroup> '+
		        '                    <col style="width:220px"> '+
		        '                    <col> '+
		        '                </colgroup> '+
		        '                <tr> '+
		        '                    <th>Property</th> '+
		        '                    <th>Data</th> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Meta-Type</td> '+
		        '                    <td class="txt_left">'+content['meta-type']+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Vendor_id</td> '+
		        '                    <td class="txt_left">'+meta['vender_id']+'</td> '+
		        '                </tr> '+
		        //'                <tr> '+
		        //'                    <td>Register Date</td> '+
		        //'                    <td class="txt_left">'+meta['theatrical_release_date']+'</td> '+
		        //'                </tr> '+
		        '                <tr> '+
		        '                    <td>Country</td> '+
		        '                    <td class="txt_left">'+meta['country']+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Original spoken local</td> '+
		        '                    <td class="txt_left">'+meta['original_spoken_locale']+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Synopsis</td> '+
		        '                    <td class="txt_left"> '+
				'						<div class="pre">'+synopsis+'</div> '+
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Production Company</td> '+
		        '                    <td class="txt_left">'+meta['production_company']+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Copyright cline</td> '+
		        '                    <td class="txt_left">'+meta['copyright_cline']+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Theatrical release date</td> '+
		        '                    <td class="txt_left">'+meta['theatrical_release_date']+'</td> '+
		        '               </tr> '+
		        '                <tr> '+
		        '                    <td>Genres</td> '+
		        '                    <td class="txt_left">'+genre+'</td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Rating</td> '+
		        '                    <td class="txt_left">'+meta['rating']+'</td> '+
		        '                </tr> '+
		        //'                <tr> '+
		        //'                    <td>Content Price</td> '+
		        //'                    <td class="txt_left">????원</td> '+
		        //'               </tr> '+
		        '                <tr> '+
		        '                    <td>Cast</td> '+
		        '                     <td class="txt_left"> '+
		        '                        <div class="txt_word_wrap"> '+
		        							castStr +
		        '                        </div> '+
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Crew</td> '+
		        '                    <td class="txt_left"> '+
		        '                        <div class="txt_word_wrap"> '+
		        							crewStr +
		        '                        </div> '+
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Artwork</td> '+
		        '                    <td class="txt_left"> '+
		        '                        <ul class="artwork_ul"> '+
		        							artWorkHtml +
		        '                        </ul> '+
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td>Contents Info</td> '+
		        '                    <td class="txt_left"> '+
		        '                        <ul class="txt_word_wrap"> '+
		        meta['contents_info'] +
		        '                        </ul> '+
		        '                    </td> '+
		        '                </tr> '+
		        '                <tr> '+
		        '                    <td colspan="2" class="txt_center"> '+
		        '                        <div class="btn_box_scenter"> '+
		        '                        <a class="btn_type_bg btn_type_blue_fill" onclick="javascript:playContent('+i+');">Content PLAY</a> '+
		        //'                        <a class="btn_type_bg btn_type_green_fill" onclick="javascript:downloadContent('+i+');">DOWNLOAD</a> '+
		        '                        </div> '+
		        '                    </td> '+
		        '                </tr> '+
		        '            </table> '+
		        '        </div> '+
		        '    </div> '+
		        '</div> ';
		        
		        $("#tab_container").append(tabHtml);
		}
		//tab_ul
		addTabClickEvent();		
		addArtworkEvent();
		changeTab(0);
		
		loadingCCTree(ccid, version);
	}
	
	
	var contentsMap = [];
	function loadingCCTree(ccid, version) {
		console.log(">>>>>>printCCTree ["+ccid+"]["+version+"]");
		
		//http://localhost:8088/ccsearch/v1/ccontent/ComplexContent/1/basicMeta/001-1.jpg
		
		var url = '/ccsearch/v1/ccontent/'+ccid+"/"+version;
		console.log("URL :: ["+url+"]");
		$.ajax({
	        url: url,
	        type:'get',
	        success:function(data){
	        	console.log(data);
	        	var contentsFiles = JSON.parse(data)['contents'];
	    		for(var i=0; i<contentsFiles.length; i++) {
	    			contentsMap[contentsFiles[i]['path']] = contentsFiles[i];
	    		}
	    		
	    		
	    		var treeObj = makeTree(JSON.parse(data));
	        	printCCTree(treeObj, ccid, version);
	        	
	        },
	        error:function(request, status, error){
	        }
	    });
	}
	
	
	function makeTree(manifest) {
		var treeObj = {};
    	treeObj['text'] = "root";
		treeObj['state'] = { "opened" : true };
		treeObj['children'] = [];
		
		
		var files = manifest['basic-meta'];
		append(files, manifest['contents']);
		//append(files, manifest['extended-meta']);
		//append(files, manifest['derivedContents']);
		
		console.log(files);
		
		for(var i=0; i<files.length; i++) {
			var path = files[i]['path'].split('/');
			var initNode = treeObj;
			for(var j=0; j<path.length; j++){
				if(j+1 == path.length) {
					initNode = makeFile(initNode, path[j], files[i]['size'], true);
				} else {
					initNode = makeFile(initNode, path[j], 0, false);
				}
			}
			//console.log(path);
			//makeFile(treeObj, path, f['size']);
		}
		
		
		{
			var nodeObj = {};
			nodeObj['text'] = "extendedMeta";
			nodeObj['state'] = { "opened" : true };
			treeObj['children'].push(nodeObj);
		}
		{
			var nodeObj = {};
			nodeObj['text'] = "derivedContents";
			nodeObj['state'] = { "opened" : true };
			treeObj['children'].push(nodeObj);
		}
		
		return treeObj;
	}
	
	function append(array1, array2){
		
		for(var i=0; i<array2.length; i++) {
			array1.push(array2[i]);
		}
		
	}
	
	function makeFile(nodeObj, folderName, size, isFile) {
		var children = nodeObj['children'];
		var exists = false;
		
		var retObj = null;
		for(var i=0; i<children.length; i++) {
			var dir = children[i];
			if(dir['text'] == folderName) {
				exists = true;
				retObj = dir;
				break;
			}
		}
		
		if(exists == false) {
			var treeObj = {};
	    	if(isFile == false) {
	    		treeObj['state'] = { "opened" : true };	
	    		treeObj['text']  = folderName;
	    	} else {
	    		treeObj['icon']  = 'jstree-file';
	    		treeObj['text']  = folderName + " ("+humanFileSize(size)+")";
	    	}
			
			treeObj['children'] = [];
			children.push(treeObj);
			return treeObj;
		} else {
			return retObj;
		}
	}
	
	
	function humanFileSize(size) {
	    var i = Math.floor( Math.log(size) / Math.log(1024) );
	    return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
	}
	
	function printCCTree(treeObj, ccid, version) {
		$('#tree-'+version).jstree("destroy").empty();
		$('#tree-'+version)
			.on("changed.jstree", function (e, data) {
			})
			.jstree({
				'core' : {
					'data' : [
						treeObj
					]
				}
			});
	}
	
	
	function updateCcStatus(ccid, version) {
		if(confirm("본 복합 콘텐츠를 서비스 콘텐츠로 복사하시겠습니까?")) {
			
			var ccStatus = {};
			ccStatus['ccid']    = ccid;
			ccStatus['version'] = version;
			ccStatus['status']  = 'ready';
			
			var ccStatusList = [];
			ccStatusList.push(ccStatus);
			
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
	
	
	
	
	
	
	
	
	

	
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
	
	
	
	
	
	
	var hlsPlayer = new Hls();
	function playContent(metaIdx) {
		var contentFile = gMetaArr[metaIdx]['target'][0];
		//var contentUrl = 'http://'+window.location.hostname+':8080/ipfs/' + version + "/" + contentFile;
		var contentUrl = 'http://13.209.14.7:8080/ipfs/' + version + "/" + contentFile;
		//13.209.14.7

		console.log(contentUrl);
		if(contentUrl.endsWith(".m3u8") == false) {
			alert("HLS 컨텐츠만 재생 가능합니다.");
			
		} else {
			
			if (Hls.isSupported()) {
				jQuery('#player_wrap').show();
				jQuery('html, body').css({'overflow': 'hidden', 'height': '100%'}); // 모달팝업 중 html,body의 scroll을 hidden시킴 
				jQuery('#player_wrap').on('scroll touchmove mousewheel', function(event) { // 터치무브와 마우스휠 스크롤 방지     
					event.preventDefault();     
					event.stopPropagation();     
					return false; 
				});
				
				
				var video = document.querySelector('#player');
				hlsPlayer.loadSource(contentUrl);
				hlsPlayer.attachMedia(video);
				hlsPlayer.on(Hls.Events.MANIFEST_PARSED, function () {
	            });
				
				
			} else {
	        	window.open(contentUrl,'_blank');
	        }
		}
	}
	
	
	function downloadContent(metaIdx) {
		var contentFile = gMetaArr[metaIdx]['target'][0];
		var contentUrl = 'http://13.209.14.7:8080/ipfs/' + version + "/" + contentFile;
		
		
		console.log(contentUrl);
		
		if(contentUrl.endsWith(".m3u8") == false) {
			alert("HLS 컨텐츠만 다운로드 가능합니다.");
			
		} else {
			//contentsMap
			var dlUrl = null;
			if(contentsMap['contents/360p/video.ts'] != null) {
				dlUrl = 'contents/360p/video.ts';
			} else if(contentsMap['contents/720p/video.ts'] != null) {
				dlUrl = 'contents/720p/video.ts';
			} else if(contentsMap['contents/1080p/video.ts'] != null) {
				dlUrl = 'contents/1080p/video.ts';
			}
			if(dlUrl == null) {
				alert("다운로드할 컨텐츠가 없습니다.");
			} else {
				var dlUrl = 'http://13.209.14.7:8080/ipfs/' + version + '/' + dlUrl;
				window.open(dlUrl, "_blank", null, null);	
			}
		}
	}
	
	function hidePlayer() {
		jQuery('#player_wrap').hide();
		jQuery('html, body').css({'overflow': 'auto', 'height': '100%'}); //scroll hidden 해제 
		jQuery('#player_wrap').off('scroll touchmove mousewheel'); // 터치무브 및 마우스휠 스크롤 가능
		var video = document.querySelector('#player');
		video.pause();
	}
	
</script>
<body>	

    <div id="modal_wrap" style="display:none;">
        <div class="modal_body">
            <a class="btn_type_sm btn_type_pink_fill">Close</a>
            <img src="" id="imgsize" alt="">            
        </div>
        <div class="modal_bg"></div>
    </div>

    <div id="player_wrap" style="display:none;">
        <div class="player_body">
            <a class="btn_type_sm btn_type_pink_fill" onclick="hidePlayer()">Close</a>
            <div class="player_box">
                <video preload="auto" id="player" autoplay controls crossorigin></video>
            </div>           
        </div>
        <div class="player_bg"></div>
    </div>

	<%@include file="../common/top.jsp"%>


	<div id="menu_id" class="smenu_101"></div>
	<div id="body_wrap" class="menu_exp">

		<div id="body_boxl">

			
			<!-- S : Slide Sub Menu -->
			<%@include file="../common/left.jsp"%>
		</div>
            


		<div id="body_boxr">

            <div class="tab_frame tab_btn">
                <ul id="tab_ul">
                </ul>
            </div>
    
            <div id="tab_container" class="tab_con">
            </div>

            <div class="btn_box_center">
                <a href="javascript:updateCcStatus(ccid, version);" class="btn_type_xbg btn_type_red_none_fill">Complex Content Copy</a>
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