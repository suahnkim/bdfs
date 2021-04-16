
<!DOCTYPE html>
<html lang="en">
<head>
    <title>circler upload</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="<?php echo(MC_ASSETS_PATH)?>/contents/image/png" href="images/icons/favicon.ico"/>
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/css/util.css">
    <link rel="stylesheet" type="text/css" href="<?php echo(MC_ASSETS_PATH)?>/contents/css/main.css">
    <!--===============================================================================================-->
    <style>
        #file{
            position: relative;
            display: inline-block;
            width: 300px;
            resize: none;
            float: left;
        }

        .btn-upload{
            margin: 0;
            float:left;
        }

        .container-row{
            padding-top: 50px;
        }

        input:file{
            dipslay: none;
        }

        .target { display: inline-block; width: 320px; white-space: nowrap; font-size:12px;text-align:left;padding:2px;margin:0 2px;cursor:pointer;}
        .target_click {background:#007fff;color:#fff;}

    </style>
</head>
<body>
<div class="container-contact100" style="background-image: url('<?php echo(MC_ASSETS_PATH)?>/contents/images/bg-01.jpg');">
    <div class="wrap-contact100">
        <form class="contact100-form down-form" method="POST" >
            <!--<input type="text" name="file_size">
            <input type="text" name="file_path">
            <input type="text" name="file_name">-->
            <input type="hidden" name="next_val" value="<?php echo($data->init_key)?>">
            <input type="hidden" name="contents_no" value="<?php echo($data->contents_id)?>">
            <input type="hidden" name="user_id" value="<?php echo($data->userid)?>">
            <span class="contact100-form-title">
					Circler Download
				</span>
            <div class="wrap-input100" data-validate="제목을 입력해주세요">
                <span class="label-input100"><strong><?php echo($data->title)?></strong><br>next_val : <?php echo($data->init_key)?></span>
                <ul style="margin:20px 0;border-radius: 5px;border:5px solid #ccc;padding:10px;" class="file_list">
                   <?php
                   if(count($data->rows) > 0){
                       foreach($data->rows as $key=>$val){
                           ?>
                           <li file_no="<?php echo($val->sort)?>" contents_file_no="<?php echo($val->contents_file_id)?>" file_name="<?php echo($val->filename)?>" del_yn="N" file_size="<?php echo($val->realsize)?>"><?php echo($val->filename)?></li>
                            <?php
                       }
                   }
                   ?>
                </ul>
            </div>
            <div class="container-contact100-form-btn">
                <div class="wrap-contact100-form-btn">
                    <div class="contact100-form-bgbtn"></div>
                    <button class="contact100-form-btn btn-submit" type="submit">
                          다운로드
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>



<div id="dropDownSelect1"></div>

<!--===============================================================================================-->
<script src="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
<script src="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/bootstrap/js/popper.js"></script>
<script src="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
<script src="<?php echo(MC_ASSETS_PATH)?>/contents/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
<script src="<?php echo(MC_ASSETS_PATH)?>/contents/js/main.js"></script>
<script src="<?php echo MC_ASSETS_PATH; ?>/script/contents.js<?php echo CSS_JS_UPDATE_DATE; ?>"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
</body>
</html>
