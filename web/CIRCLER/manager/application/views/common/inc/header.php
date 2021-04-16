<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php include_once(dirname(__FILE__) . "/top.php"); ?>
</head>
<body>
<div class="header">
    <div class="topbox">
        <div class="logo">
            <a href="/"><img src="<?php echo COM_ASSETS_PATH; ?>/img/logo.png" alt=""></a>
        </div>
        <ul class="Myinfo">
            <li>
                <div class="myname">
                    <?php echo($user->manager_name)?>
                </div>
            </li>
            <li onclick="location.href='/user/logout'">
                <button type="button" class="btn_logout">로그아웃</button>
            </li>
        </ul>
    </div>
</div>
<div id="wrap">
    <?php include_once(dirname(__FILE__). "/leftmenu.php"); ?>
    <div class="containbox">
        <div class="contents">