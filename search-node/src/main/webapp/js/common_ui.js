//Menu Sliding
jQuery(function () {
	var verticalNavigation = new SSDSystem.VerticalNavigation();
	verticalNavigation.init();
});
var SSDSystem = SSDSystem || {}; SSDSystem.VerticalNavigation = function () { }; SSDSystem.VerticalNavigation.prototype = { navigationIdentity: "#leftNavigation", isEmpty: function (thisValue) { if (jQuery.isArray(thisValue)) { return (thisValue.length <= 0) } else { return (thisValue === "" || thisValue === null || typeof thisValue === "undefined") } }, replaceHeading: function (thisHeading) { if (!this.isEmpty(thisHeading)) { jQuery("h1#heading").html(thisHeading.trim()) } }, leftNavigationActiveMain: function (thisLi) { thisLi.toggleClass("active").siblings().removeClass("active") }, leftNavigationActiveSub: function (thisLi) { thisLi.addClass("active").siblings().removeClass("active").closest("ul").closest("li").siblings().find("li").removeClass("active"); this.replaceHeading(thisLi.text()) }, leftNavigationClick: function (thisParentUl, thisLi) { if (thisParentUl.is(this.navigationIdentity)) { this.leftNavigationActiveMain(thisLi) } else { this.leftNavigationActiveSub(thisLi) } }, leftNavigation: function () { var self = this; jQuery(document).on("click", self.navigationIdentity + " li a", function (event) { try { var thisA = jQuery(this), thisLi = thisA.closest("li"), thisParentUl = thisLi.closest("ul"); self.leftNavigationClick(thisParentUl, thisLi) } catch (errorMessage) { console.log(errorMessage) } }) }, init: function (thisIdentity) { try { if (!this.isEmpty(thisIdentity)) { this.navigationIdentity = thisIdentity } this.leftNavigation() } catch (errorMessage) { console.log(errorMessage) } } };

//Common Ready Script
jQuery(document).ready(function () {

	jQuery(window).scroll(function () { offsetTop(); });

	//scrolltop move
	jQuery("#scrolltop").click(function () {
		jQuery('html, body').stop().animate({ scrollTop: '0' });
	});

	//Modal Open + Size
	var winHeight = jQuery(window).height();
	jQuery('#modal_wrap, .modal_bg, #player_wrap, .player_bg').css('height', winHeight);
	jQuery('.modal_body img').load(function(){ 
		var imgWidth = this.naturalWidth; 
		var imgHeight = this.naturalHeight;
		jQuery('.modal_body img').css('width', imgWidth);
		jQuery('.modal_body img').css('height', imgHeight);
		jQuery('.modal_body').css('margin-left', -imgWidth/2);
		jQuery('.modal_body').css('margin-top', -imgHeight/1.85);
	});	

	/*
	//Modal Close
	jQuery('.modal_body a, .modal_bg').on("click", function(){
		jQuery('#modal_wrap').hide();
		jQuery('html, body').css({'overflow': 'auto', 'height': '100%'}); //scroll hidden 해제 
		jQuery('#modal_wrap').off('scroll touchmove mousewheel'); // 터치무브 및 마우스휠 스크롤 가능
	});
	jQuery('.player_body a, .player_bg').on("click", function(){
		jQuery('#player_wrap').hide();
		jQuery('html, body').css({'overflow': 'auto', 'height': '100%'}); //scroll hidden 해제 
		jQuery('#player_wrap').off('scroll touchmove mousewheel'); // 터치무브 및 마우스휠 스크롤 가능
		$.each($('#player'), function () {
			$(this).stop();
		});
	});
	*/
	

	


	//input width control
	var i_resize_20 = jQuery('.i_resize_20').parent().width();
	jQuery('.i_resize_20').css('width', i_resize_20 - 22);
	var i_resize_40 = jQuery('.i_resize_40').parent().width();
	jQuery('.i_resize_40').css('width', i_resize_40 - 22);
	var i_resize_41 = jQuery('#body_boxr').width();
	jQuery('.i_resize_41').css('width', i_resize_41 - 260);
	var i_resize_memo = jQuery('.i_group_ea_memo').width();
	jQuery('.i_group_ea_memo dd .i_resize_memo').css('width', i_resize_memo - 85);
	var i_resize_prog = jQuery('.i_group_ea_prog').width();
	jQuery('.i_group_ea_prog dd .i_resize_prog').css('width', i_resize_prog - 73);

	//LNB On-Off
	jQuery("#menu_control").click(function () {
		jQuery("body").toggleClass("menu_min");
	});

	//Checkbox All Control
	jQuery('.chk_all_top, .chk_all_bottom').click(function () {
		jQuery('.chk').prop('checked', this.checked);
		jQuery('.chk_all_top, .chk_all_bottom').prop('checked', this.checked);
	});

	//LNB Menu On-Off
	var smenuid = jQuery("#menu_id").attr('class');
	var sm1 = smenuid.substring(6, 7);
	var sm2 = smenuid.substring(smenuid.length - 2);
	jQuery('#leftNavigation li').removeClass("active");
	if (smenuid == 'smenu_' + sm1 + sm2) {
		jQuery('#menu_' + sm1).addClass("active");
		jQuery('#menu_' + sm1 + ' #' + smenuid).addClass("active");
		jQuery('#menu_' + sm1 + ' ul li:nth-child(' + sm2 + ')').addClass("active");
	}

	//Schedule datepicker
	jQuery(function () {
		var ndate = new Date();
		var nyear = ndate.getFullYear();
		var nmonth = (ndate.getMonth() + 0);
		(++nmonth < 10) ? nmonth = "0" + nmonth : nmonth;
		var nday = ndate.getDate();
		(nday < 10) ? nday = "0" + nday : nday;
		jQuery(".sche_year").text(nyear);
		jQuery(".sche_month").text(nmonth);
		jQuery(".sche_day").text(nday);
		//Current Date

		jQuery(".datepicker").datepicker();
		jQuery(".datepicker").change(function () {
			var dpyear = jQuery(this).datepicker("getDate").getFullYear();
			var dpmonth = jQuery(this).datepicker("getDate").getMonth();
			(++dpmonth < 10) ? dpmonth = "0" + dpmonth : dpmonth;
			var dpdate = jQuery(this).datepicker("getDate").getDate();
			(dpdate < 10) ? dpdate = "0" + dpdate : dpdate;
			jQuery(".sche_year").text(dpyear);
			jQuery(".sche_month").text(dpmonth);
			jQuery(".sche_day").text(dpdate);
			//Select Date
		});

	});

});




//Artwork Image Expend
function addArtworkEvent() {
	jQuery('.artwork_ul li img, .thumb_td img.thumb').each(function(index) {
		$(this).on("click", function(){
			var imgsrc = $(this).attr('src');
			jQuery('.modal_body img').attr('src',imgsrc);
			jQuery('#modal_wrap').show();
			jQuery('html, body').css({'overflow': 'hidden', 'height': '100%'}); // 모달팝업 중 html,body의 scroll을 hidden시킴 
			jQuery('#modal_wrap').on('scroll touchmove mousewheel', function(event) { // 터치무브와 마우스휠 스크롤 방지     
				event.preventDefault();     
				event.stopPropagation();     
				return false; 
			});
		});
	});	
	
	
	jQuery('.modal_body a, .modal_bg').on("click", function(){
		jQuery('#modal_wrap').hide();
		jQuery('html, body').css({'overflow': 'auto', 'height': '100%'}); //scroll hidden 해제 
		jQuery('#modal_wrap').off('scroll touchmove mousewheel'); // 터치무브 및 마우스휠 스크롤 가능
	});
}










//Resize Script
jQuery(window).resize(function () {

	//input 가로 사이즈 제어
	var i_resize_20 = jQuery('.i_resize_20').parent().width();
	jQuery('.i_resize_20').css('width', i_resize_20 - 22);
	var i_resize_40 = jQuery('.i_resize_40').parent().width();
	jQuery('.i_resize_40').css('width', i_resize_40 - 22);
	var i_resize_memo = jQuery('.i_group_ea_memo').width();
	jQuery('.i_group_ea_memo dd .i_resize_memo').css('width', i_resize_memo - 90);
	var i_resize_prog = jQuery('.i_group_ea_prog').width();
	jQuery('.i_group_ea_prog dd .i_resize_prog').css('width', i_resize_prog - 73);

	//modal
	var winHeight = jQuery(window).height();
	jQuery('#modal_wrap, .modal_bg').css('height', winHeight);
	jQuery('.modal_body img').load(function(){ 
		var imgWidth = this.naturalWidth; 
		var imgHeight = this.naturalHeight;
		jQuery('.modal_body img').css('width', imgWidth);
		jQuery('.modal_body img').css('height', imgHeight);
		jQuery('.modal_body').css('margin-left', -imgWidth/2);
		jQuery('.modal_body').css('margin-top', -imgHeight/1.85);
	});	

});

//offsetTop Scroll
function offsetTop() {
	var offsetT = jQuery(window).scrollTop();
	if (offsetT <= 10) jQuery("#scrolltop").fadeOut();
	else if (offsetT > 10) jQuery("#scrolltop").fadeIn();
}

//Datepicker
jQuery(function () {
	addDatepickerEvent();
});



jQuery(function () {
	jQuery(".from").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		onClose: function (selectedDate) {
			jQuery(".to").datepicker("option", "minDate", selectedDate);
		}
	});
	jQuery(".to").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		onClose: function (selectedDate) {
			jQuery(".from").datepicker("option", "maxDate", selectedDate);
		}
	});
});



function addTabClickEvent() {
	jQuery('.tab_btn li').click(function(){
		idx = jQuery(this).index();
		changeTab(idx);
	});
}



function addDatepickerEvent() {
	jQuery(".datepicker").datepicker();
}

//Tab Change
function changeTab(idx) {
	jQuery('.tab_btn li[class=selected]').removeClass('selected');
	jQuery('.tab_btn li').eq(idx).addClass('selected');
	jQuery('.tab_con .cbox').hide();
	jQuery('#c'+(idx+1)).show();
	jQuery('.tab_con').css('height',$('#c'+(idx+1)).height()+1+'px');
}
