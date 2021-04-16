<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"  />
    <meta http-equiv="Cache-Control" content="No-Cache" />
    <meta http-equiv="Pragma" content="No-Cache"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo(SITE_NAME)?></title>
    <!--<link rel="stylesheet" type="text/css" href="<?php /*echo MC_ASSETS_PATH; */?>/css/normalize.css<?php /*echo CSS_JS_UPDATE_DATE; */?>" />-->
    <link rel="stylesheet" type="text/css" href="<?php echo MC_ASSETS_PATH; ?>/css/common.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo COM_ASSETS_PATH; ?>/css/jquery.datetimepicker.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo COM_ASSETS_PATH; ?>/css/common.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo COM_ASSETS_PATH; ?>/css/default.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo COM_ASSETS_PATH; ?>/css/custom.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" >
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery-1.11.2.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/lazyload.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <!--[if lt IE 9]>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/html5shiv.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <![endif]-->
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.form.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.cookie.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.base64.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.common.form.validation.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.datetimepicker.full.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
<!--    <script type="text/javascript" src="<?php /*echo COM_ASSETS_PATH; */?>/script/jquery-2.1.1.min.js"></script>-->
    <script type="text/javascript" src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.als-1.7.min.js"></script>
    <meta name="keywords" content="<?php echo(SITE_NAME)?>"  />
    <meta name="Subject" content="<?php echo(SITE_NAME)?>"   />
    <meta name="robots" content="ALL"   />
    <meta name="description" content="<?php echo(SITE_DESCRIPTION)?>" />
    <meta name="author" content="<?php echo(SITE_DESCRIPTION)?>" />
    <meta name="writer" content="<?php echo(SITE_WRITER)?>" />
    <meta name="copyright" content="<?php echo(SITE_COPYRIGHT)?>"    />
    <meta name="reply-to" content="<?php echo(SITE_REPLYTO)?>"   />
    <meta name="content-language" content="UTF-8" />
    <meta name="build" content="<?php echo(SITE_BUILD)?>"/>
    <!--link rel="SHORTCUT ICON" href="<?php echo COM_ASSETS_PATH; ?>/img/common/pamo_new.ico">
	<link rel="BOOKMARK ICON" href="<?php echo COM_ASSETS_PATH; ?>/img/common/pamo_new.ico"-->
    <style>
        .pic img {
            opacity: 0;
        }
        .pic img:not(.initial) {
            transition: opacity 1s;
        }
        .pic  img.initial,
        .pic  img.loaded,
        .pic  img.error {
            opacity: 1;
        }

        .pic img:not([src]) {
            visibility: hidden;
        }

    </style>

    <script>
        $(function () {
            $.account_balance_callback();
        });
    </script>
</head>
<body>
<div id="wrap">
    <div class="header">
        <div class="topgnb">
            <div class="logo"><a href="/"><img src="<?php echo(COM_ASSETS_PATH)?>/img/logo.png" alt="Circler"></a></div>
            <div class="group_srch">
                <form method="get" action="/contents/lists">
                    <label for="inp_srch" class="blind"></label>
                    <input type="hidden" autocomplete="off" placeholder="Search " name="srch_key" value="title">
                    <input type="text" autocomplete="off" placeholder="Search " name="srch_value" value="<?php echo(@$_GET['search_value'])?>">
                    <button type="submit" class="btn_srch"><span class="ico_srch">검색</span></button>
                </form>
            </div>
            <!--로그인후-->
            <ul class="Myaccout">
                <li>
                    <button  class="ic_my" onclick="myFunction()"></button>
                    <div id="myDropdown" class="dropdown-content"  >
                        <div class="arrow_box">
                            <ul class="toploginbox">
                                <li class="user_accountId"><?php echo($user->eth_account)?></li>
                                <li><?php echo($user->email)?></li>
                                <li class="logout_btn"><i class="far fa-check-circle"></i><a href="#"> 로그아웃</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li ><span class="ic_eth"></span><span class="eth_balance">0</span> ETH</li>
                <li ><span class="ic_way"></span><span class="way_balance">0</span> WEI</li>
            </ul>
        </div>
        <?php
        switch($this->uri->segment(2)){
            case 'popular' : $on = 'on';
        }
        $segment = $this->uri->segment(3);
        ?>
        <ul class="nav">
            <li class="<?php echo($segment == '')?'on':''?>"><a href="/">HOME</a></li>
            <li class="<?php echo($segment == 'popular')?'on':''?>"><a href="/contents/lists/popular">인기 콘텐츠</a></li>
            <li class="<?php echo($segment == 'recent')?'on':''?>"><a href="/contents/lists/recent">새로운 콘텐츠</a></li>
            <li class="<?php echo($segment == 'recommand')?'on':''?>"><a href="/contents/lists/recommand">추천 콘텐츠</a></li>
        </ul>
    </div>
    <script>
        /* When the user clicks on the button,
        toggle between hiding and showing the dropdown content */
        function myFunction() {
            //$('#myDropdown').toggleClass('show');
            //document.getElementById("myDropdown").classList.toggle("show");
            $('#myDropdown').slideToggle(200);
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.dropbtn')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <?php include_once(dirname(__FILE__) . "/menu.php");?>