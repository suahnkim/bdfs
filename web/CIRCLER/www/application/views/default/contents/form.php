
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
        <form class="contact100-form validate-form" action="/contents/form_submit" method="POST" >
            <!--<input type="text" name="file_size">
            <input type="text" name="file_path">
            <input type="text" name="file_name">-->
            <input type="hidden" name="folderpath">
				<span class="contact100-form-title">
					Circler Upload
				</span>
            <div class="wrap-input100" data-validate="제목을 입력해주세요">
                <span class="label-input100">제목 *</span>
                <input class="input100" type="text" name="cont_name" placeholder="제목을 입력해주세요">
            </div>

            <!--<div class="wrap-input100 rs1-wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
                <span class="label-input100">Enter your email *</span>
                <input class="input100" type="text" name="email" placeholder="Enter your email">
            </div>
            <div class="wrap-input100 rs1-wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
                <span class="label-input100">Enter your email *</span>
                <input class="input100" type="text" name="email" placeholder="Enter your email">
            </div>-->

            <div class="wrap-input100">
                <span class="label-input100">파일업로드</span>
                <div class="col-md-12 text-center" onload="GetFileInfo()">
                    <div id="file"  class="form-control" readonly style="margin-right:10px;min-height:100px;width:300px;overflow: auto;" >
                        <ul class="filelist">

                        </ul>
                    </div>
                    <span class="btn btn-lg btn-danger btn-upload" upload-type="F">폴더선택</span><span class="btn btn-lg btn-danger btn-upload" upload-type="C" style="margin-left:10px;">파일선택</span>
                </div>
                <!--<input type="file" name="file[]" id="fileInput" style="display:none;" onchange="GetFileInfo()" multiple />-->
            </div>
            <!--data-validate = "Message is required"-->
            <div class="wrap-input100"  >
                <span class="label-input100">내용</span>
                <textarea class="input100" name="contents" placeholder="내용을 입력해주세요."></textarea>
            </div>

            <div class="container-contact100-form-btn">
                <div class="wrap-contact100-form-btn">
                    <div class="contact100-form-bgbtn"></div>
                    <button class="contact100-form-btn btn-submit" type="submit">
                        Submit
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
<script>
    function GetFileInfo () {

        var wrapper = $('<div/>').css({height:0,width:0,'overflow':'hidden'});
        var fileInput =  document.getElementById("fileInput");
        var message = "";
        var file_size = [];
        var file_name = [];
        if ('files' in fileInput) {
            if (fileInput.files.length == 0) {
                message = "Please browse for one or more files.";
            } else {

                for (var i = 0; i < fileInput.files.length; i++) {
                    //console.log(fileInput.files[i]);
                    var file = fileInput.files[i];

                    message += "<option value='"+fileInput.files[i].name+"'>" + fileInput.files[i].name + "</option>";
                    file_name.push(fileInput.files[i].name);
                    file_size.push(fileInput.files[i].size);



                   /* if ('name' in file) {
                        message += "<option value='"+file.name+"'>" + file.name + "</option>";
                    }
                    else {
                        message +=  "<option value='"+file.fileName+"'>" + file.fileName + "</option>";
                    }

                    if('size' in file){
                        file_size.push(file.size);
                    }else{
                        file_size.push(file.size);
                    }*/
                }
            }

        }
        else {
            if (fileInput.value == "") {
                message += "Please browse for one or more files.";
                message += "<br />Use the Control or Shift key for multiple selection.";
            }
            else {
                message += "Your browser doesn't support the files property!";
                message += "<br />The path of the selected file: " + fileInput.value;
            }
        }

        var info = document.getElementById ("file");
        info.innerHTML = message;
        $('input[name=file_path]').val(fileInput.value);
        $('input[name=file_name]').val(file_name);
        $('input[name=file_size]').val(file_size);
    }
    /*$('#file').click(function(){
        fileInput.click();
    }).show();*/

    /*$('.btn').click(function(){
        var fileInput =  document.getElementById("fileInput");
        fileInput.click();
    }).show();*/

</script>

<script type="text/javascript">
/*    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#foo').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#fileInput").change(function() {
        readURL(this);
    });*/
    </script>

</body>
</html>
