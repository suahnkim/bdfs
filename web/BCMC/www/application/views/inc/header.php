<!doctype html>
<html class="fixed">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Mediablockchain" />
    <meta name="description" content="Mediablockchain">
    <meta name="author" content="mediablockchain.co.kr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/bootstrap.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/animate.css<?php echo CSS_JS_UPDATE_DATE; ?>">
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/all.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/magnific-popup.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/bootstrap-datepicker3.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/select2.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/bootstrap-select2.min.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/pnotify.custom.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/jquery.filepicker.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/theme.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/nprogress.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/default.css<?php echo CSS_JS_UPDATE_DATE; ?>" />
    <link rel="stylesheet" href="<?php echo COM_ASSETS_PATH; ?>/css/custom.css<?php echo CSS_JS_UPDATE_DATE; ?>">
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/modernizr.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/jquery.min.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="<?php echo COM_ASSETS_PATH; ?>/script/textbanner.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
    <script src="https://unpkg.com/vue"></script>
</head>
<body id="LoadingOverlayApi" class="loading-overlay-showing" data-loading-overlay>
<div class="loading-overlay">
    <div class="bounce-loader">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
    </div>
</div>
<section class="body">
    <!-- start: header -->
    <header class="header">
        <div class="logo-container">
            <a href="/" class="logo">
                <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/logo_new.png" width="75" height="35" alt="Mediablockchain" />
            </a>
            <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
            </div>
        </div>

        <!-- start: search & user box -->
        <div class="header-right">

            <!--<form action="pages-search-results.html" class="search nav-form">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" id="q" placeholder="Search...">
                    <span class="input-group-append">
								<button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
							</span>
                </div>
            </form>-->

            <span class="separator"></span>

            <ul class="notifications">
               <!-- <li>
                    <a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
                        <i class="fas fa-tasks"></i>
                        <span class="badge">3</span>
                    </a>

                    <div class="dropdown-menu notification-menu large">
                        <div class="notification-title">
                            <span class="float-right badge badge-default">3</span>
                            Tasks
                        </div>

                        <div class="content">
                            <ul>
                                <li>
                                    <p class="clearfix mb-1">
                                        <span class="message float-left">Generating Sales Report</span>
                                        <span class="message float-right text-dark">60%</span>
                                    </p>
                                    <div class="progress progress-xs light">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
                                    </div>
                                </li>

                                <li>
                                    <p class="clearfix mb-1">
                                        <span class="message float-left">Importing Contacts</span>
                                        <span class="message float-right text-dark">98%</span>
                                    </p>
                                    <div class="progress progress-xs light">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100" style="width: 98%;"></div>
                                    </div>
                                </li>

                                <li>
                                    <p class="clearfix mb-1">
                                        <span class="message float-left">Uploading something big</span>
                                        <span class="message float-right text-dark">33%</span>
                                    </p>
                                    <div class="progress progress-xs light mb-1">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>-->
                <!--<li>
                    <a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">4</span>
                    </a>

                    <div class="dropdown-menu notification-menu">
                        <div class="notification-title">
                            <span class="float-right badge badge-default">230</span>
                            Messages
                        </div>

                        <div class="content">
                            <ul>
                                <li>
                                    <a href="#" class="clearfix">
                                        <figure class="image">
                                            <img src="<?php /*echo COM_ASSETS_PATH; */?>/img/icon/!sample-user.jpg" alt="Joseph Doe Junior" class="rounded-circle" />
                                        </figure>
                                        <span class="title">Joseph Doe</span>
                                        <span class="message">Lorem ipsum dolor sit.</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearfix">
                                        <figure class="image">
                                            <img src="<?php /*echo COM_ASSETS_PATH; */?>/img/icon/!sample-user.jpg" alt="Joseph Junior" class="rounded-circle" />
                                        </figure>
                                        <span class="title">Joseph Junior</span>
                                        <span class="message truncate">Truncated message. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet lacinia orci. Proin vestibulum eget risus non luctus. Nunc cursus lacinia lacinia. Nulla molestie malesuada est ac tincidunt. Quisque eget convallis diam, nec venenatis risus. Vestibulum blandit faucibus est et malesuada. Sed interdum cursus dui nec venenatis. Pellentesque non nisi lobortis, rutrum eros ut, convallis nisi. Sed tellus turpis, dignissim sit amet tristique quis, pretium id est. Sed aliquam diam diam, sit amet faucibus tellus ultricies eu. Aliquam lacinia nibh a metus bibendum, eu commodo eros commodo. Sed commodo molestie elit, a molestie lacus porttitor id. Donec facilisis varius sapien, ac fringilla velit porttitor et. Nam tincidunt gravida dui, sed pharetra odio pharetra nec. Duis consectetur venenatis pharetra. Vestibulum egestas nisi quis elementum elementum.</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearfix">
                                        <figure class="image">
                                            <img src="<?php /*echo COM_ASSETS_PATH; */?>/img/icon/!sample-user.jpg" alt="Joe Junior" class="rounded-circle" />
                                        </figure>
                                        <span class="title">Joe Junior</span>
                                        <span class="message">Lorem ipsum dolor sit.</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearfix">
                                        <figure class="image">
                                            <img src="<?php /*echo COM_ASSETS_PATH; */?>/img/icon/!sample-user.jpg" alt="Joseph Junior" class="rounded-circle" />
                                        </figure>
                                        <span class="title">Joseph Junior</span>
                                        <span class="message">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet lacinia orci. Proin vestibulum eget risus non luctus. Nunc cursus lacinia lacinia. Nulla molestie malesuada est ac tincidunt. Quisque eget convallis diam.</span>
                                    </a>
                                </li>
                            </ul>

                            <hr />

                            <div class="text-right">
                                <a href="#" class="view-more">View All</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </a>

                    <div class="dropdown-menu notification-menu">
                        <div class="notification-title">
                            <span class="float-right badge badge-default">3</span>
                            Alerts
                        </div>

                        <div class="content">
                            <ul>
                                <li>
                                    <a href="#" class="clearfix">
                                        <div class="image">
                                            <i class="fas fa-thumbs-down bg-danger text-light"></i>
                                        </div>
                                        <span class="title">Server is Down!</span>
                                        <span class="message">Just now</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearfix">
                                        <div class="image">
                                            <i class="fas fa-lock bg-warning text-light"></i>
                                        </div>
                                        <span class="title">User Locked</span>
                                        <span class="message">15 minutes ago</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearfix">
                                        <div class="image">
                                            <i class="fas fa-signal bg-success text-light"></i>
                                        </div>
                                        <span class="title">Connection Restaured</span>
                                        <span class="message">10/10/2017</span>
                                    </a>
                                </li>
                            </ul>

                            <hr />

                            <div class="text-right">
                                <a href="#" class="view-more">View All</a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>-->

            <span class="separator"></span>

            <div id="userbox" class="userbox">
                <a href="#" data-toggle="dropdown">
                    <figure class="profile-picture">
                        <img src="<?php echo COM_ASSETS_PATH; ?>/img/icon/!sample-user.jpg" alt="Joseph Doe" class="rounded-circle" data-lock-picture="<?php echo COM_ASSETS_PATH; ?>/img/icon/!logged-user.jpg" />
                    </figure>
                    <div class="profile-info" data-lock-name="John Doe" data-lock-email="johndoe@okler.com">
                        <span class="name"><?php echo($user->eth_account)?></span>
                        <!--<span class="role">administrator</span>-->
                    </div>

                    <i class="fa custom-caret"></i>
                </a>

                <div class="dropdown-menu">
                    <ul class="list-unstyled mb-2">
                        <li class="divider"></li>
                       <!-- 
                        <li>
                            <a role="menuitem" tabindex="-1" href="pages-user-profile.html"><i class="fas fa-user"></i> My Profile</a>
                        </li>
                        <li>
                            <a role="menuitem" tabindex="-1" href="#" data-lock-screen="true"><i class="fas fa-lock"></i> Lock Screen</a>
                        </li>
                        -->
                        <li class="btn-logout">
                            <a role="menuitem" tabindex="-1" href="javascript:;"><i class="fas fa-power-off"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- end: search & user box -->
    </header>
    <!-- end: header -->
    <div class="inner-wrapper">
        <!-- start: sidebar -->
        <?php include_once(dirname(__FILE__) ."/menu.php");?>

        <!-- end: sidebar -->

        <section role="main" class="content-body">
            <header class="page-header">
                <h2>미디어블록체인 - <?php echo(ENUM_ACCOUNT_TYPE::_print($user->eth_role))?></h2>

                <div class="right-wrapper text-right">
                    <ol class="breadcrumbs">
                        <li>
                            <a href="index.html">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        <li><span>Layouts</span></li>
                        <li><span>Light Sidebar</span></li>
                    </ol>
                    <a style="margin-right:10px;"></a>
                    <!--<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>-->
                </div>
            </header>



