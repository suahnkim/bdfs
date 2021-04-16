<?php
$request_auth_arr = array('P'=>'콘텐츠 생성자','CP'=>'콘텐츠 제공자','SP'=>'스토리지 제공자','D'=>'유통업자');


/*$string = "{\"ccid\":%20\"QmSEZPoCtQHhcUSGX8hw8V7UxpFCGwZ4NPzJ1sKqKzMTr6\",%20%20%20%20%20%20%20%20\"version\":%20\"\",%20%20%20%20%20%20%20%20}";

$aa = preg_replace("/\s+/", "", $string);
$aa = str_replace('%20', '' ,$aa);
$aa = str_replace(',}', '}' ,$aa);

$json = json_decode($aa);

print_r($json);

echo $aa . "<br>";

echo 'ccid = >' . $json->ccid . "<br>";*/
?>

<script src="/assets/common/script/jquery.min.js?t=<?php echo(time())?>"></script>

<div style="border:1px solid #000;width:500px;margin-bottom:20px;">
    <table >
        <tr>
            <td style="border-bottom:1px solid #ccc;font-weight:bold;font-size:13pt"># 로그인</td>
        </tr>
        <tr>
            <td>계정아이디 : <input type="text" name="accountId" value="c8da2a3d678a02b7d9a44867846d7dae4c0cdc4a"></td>
        </tr>
        <tr>
            <td>계정패스워드 : <input type="password" name="password" value="qwe123"> </td>
        </tr>
        <tr>
            <td><button type="button" class="btn-login">로그인</button><div style="border:1px solid #f2aa25;margin:10px 0;padding:20px;display:none" class="login_msg"></div></td>
        </tr>
    </table>
</div>


<div style="border:1px solid #000;width:500px;margin-bottom:20px;">
    <table>
        <tr>
            <td style="border-bottom:1px solid #ccc;font-weight:bold;font-size:13pt"># 권한요청 - 로그인 후 권한요청해야됨</td>
        </tr>
        <tr>
            <td>
            <select name="role">
                <?php
                foreach($request_auth_arr as $key=>$val){
                    echo "<option value='".$key."'>".$val."</optiom>";
                }
                ?>
            </select>
            </td>
        </tr>

        <tr>
            <td><button type="button" class="btn-auth">권한요청</button><div style="border:1px solid #f2aa25;margin:10px 0;padding:20px;display:none" class="auth_msg"></div></td>
        </tr>
    </table>
</div>

<form action="https://203.229.154.79:9800/drm/uploadinit.do" method="post" style="border:1px solid #000;padding:10px;">
    <h>콘텐츠 초기화 요청</h>
    <p>
    <input type="text" name="user_id" value="9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610">
    <input type="text" name="pwd">
    <input type="text" name="tot_count" value="3">
    <input type="text" name="tot_size" value="28894825">
    <input type="text" name="cont_name" value="1">
    <input type="text" name="cid" value="CID2019testid">
    <input type="text" name="ccid" value="testccid">
    <input type="text" name="ccid_ver" value="ver1">
    <input type="text" name="drm_yn" value="y">
    <input type="text" name="id_sign" value='{"sign":"7VeeLajUL4OqrrPMmACtxq3E3QtwZxTDeSGY7gOfqxUaYtVKHfKEfajaUpIOOHFjiGTYYFT9TLFuEC5XaFgdDCI5YmNlY2Q5MDg1ZmFlOGZhNzg3YWMzZjNiZDNjMmYyNWE5MGUwNjEwIg==","pubKey":"BR2PX29hVIbA/1cgVCeLtVR7fKXHhriUOga9UtjY48M="}'>
    <input type="submit" value="전송">
    </p>
</form>


<form action="https://203.229.154.79:9800/drm/statusInfo.do" method="post" style="border:1px solid #000;padding:10px;">
    <h>패키징 상태값 확인</h>
    <p>
        <input type="text" name="user_id" value="9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610">
        <input type="text" name="next_val" value="39efaf9b-0829-43d8-92f1-8599eec1bae0">
        <input type="submit" value="전송">
    </p>
</form>

<form action="https://203.229.154.79:9800/drm/statusInfodetail.do" method="post" style="border:1px solid #000;padding:10px;">
    <h>패키징 상태값 확인(상세)</h>
    <p>
    <input type="text" name="user_id" value="9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610">
    <input type="text" name="next_val" value="39efaf9b-0829-43d8-92f1-8599eec1bae0">
    <input type="submit" value="전송">
    </p>
</form>

<form action="/contents/getContentsJson" method="get" style="border:1px solid #000;padding:10px;">
    <h>ipfs json 리턴</h>
    <p>
        <input type="text" name="contents_no" value="53">
        <input type="text" name="save_path" value="C:\Users\ideabank\Desktop\Circler">
        <input type="submit" value="전송">
    </p>
</form>


<form action="/contents/tmp_upload" method="POST" style="border:1px solid #000;padding:10px;" enctype="multipart/form-data">
    <h>이미지업로드 </h>
    <p>
        <input type="file" name="filename">
        <input type="submit" value="전송">
    </p>
</form>


<form action="/contents/setContentsCcid" method="POST" style="border:1px solid #000;padding:10px;" enctype="multipart/form-data">
    <h>setContentsCcid 테스트 </h>
    <p>
        <input type="text" name="contents_no" value="180">
        <input type="text" name="result" value='{
    "result": {
        "result": "0",
        "result_message": "OK",
        "ccid": "QmUyg7kmyDB3dKPxisnMvxQWqu2gX5RHnEL2AhgkosHjEF",
        "version": "QmZ6pjmUNKUtUPhyV14UCzcxFMksu18CgBfrmBDq8KQQtJ",
        "chunk_size": 262144
        },
        "files": [
        {
        "path": "basicMeta/basicMeta0.json",
        "file_size": 803,
        "cid": "QmUrc2saST6xbyg6VNqfvkguU4nibkfpjyyWW7QagXiihZ"
        },
        {
        "path": "basicMeta/toystory.mp4.jpg",
        "file_size{
        "path": "basicMeta/basicMeta0.json",
        "file_size": 803,
        "cid": "QmUrc2saST6xbyg6VNqfvkguU4nibkfpjyyWW7QagXiihZ"
        },
        {
        "path": "basicMeta/toystory.mp4.jpg",
        "file_size": 254252,
        "cid": "QmXpCd6izaoMiPvuYLfmMVigmi3ibP3HwZxh2v3rK1QP8t"
        },
        {
        "path": "contents/toystory.mp4",
        "file_size": 33505948,
        "cid": "QmaKDdZtoGNSmwFmQT5jvBDJ5eHr1vLCXYJgyBU6tTdshJ"
        {
        "path": "contents/toystory.mp4",
        "file_size": 33505948,
        "cid": "QmaKDdZtoGNSmwFmQT5jvBDJ5eHr1vLCXYJgyBU6tTdshJ"
        }
        ],
        "tx_result": {}
        }
        mXpCd6izaoMiPvuYLfmMVigmi3ibP3HwZxh2v3rK1QP8t"
        },
        {
        "path": "contents/toystory.mp4",
        "file_size": 33505948,
        "cid": "QmaKDdZtoGNSmwFmQT5jvBDJ5eHr1vLCXYJgyBU6tTdshJ"
        {}
        }'>
        <input type="submit" value="전송">
    </p>
</form>


<form action="https://203.229.154.79:9800/drm/downloadReq.do" method="POST" style="border:1px solid #000;padding:10px;" enctype="multipart/form-data">
    <h>파일다운로드!!!! </h>
    <p>
        게시물상세 : http://www.mediablockchain.co.kr/contents/view/p/473<br>
        파일명 : 13GB_Pirates of the Caribbean.mp4<br>
        user_id : <input type="text" name="user_id" value="9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610"><br>
        next_val : <input type="text" name="next_val" value="cde947f0-7ace-4c16-9061-6803a78a29da"><br>
        file_no : <input type="text" name="file_no" value="1"><br>
        del_yn : <input type="text" name="del_yn" value="N"><br>
        <input type="submit" value="전송">
    </p>
</form>


<script>
    var http_api_url = "http://127.0.0.1:55442";
    $.runsync = function (url , data ,type , debug) {

        var returnData = null;
        $url = url;
        $postData  = data;

            jQuery.ajax({
            type:'POST',
            url:$url,
            data:$postData,
            dataType:type,
            timeout: 1000,
            cache: false,
            async:false,
            beforeSend: function(xhr) {
                xhr.withCredentials = true;
            },success:function(obj){
                returnData = obj;
            },error:function(xhr,textStatus,errorThrown){
                //alert('An error occurred! '+(errorThrown ? errorThrown : xhr.status));
            },complete: function(jqXHR,textStatus){
                //alert(jqXHR.statusText);
            }
        });
        if(debug) console.log(returnData)
        return returnData;
    }


    $(document).ready(function () {

        $('button.btn-login').on('click' , function (event) {
            event.stopPropagation();
            event.preventDefault();

            var params = {accountId : $('input[name=accountId]').val() , password : $('input[name=password]').val()}
            var json = $.runsync(http_api_url + '/account/login', params, 'json', true);
            if(json.resultCode == 0) $('.login_msg').html('로그인 완료<button type="button" class="btn-logout">로그아웃</button>').fadeIn();
            else $('.login_msg').html(json.resultMessage).fadeIn();
        });
        $(document).on('click' , 'button.btn-logout' ,function (event) {
            event.stopPropagation();
            event.preventDefault();

            var params = {role : $('select[name=role] option:selected').val()}
            var json = $.runsync(http_api_url + '/msp/authRequest', params, 'json', true);
            if(json.resultCode == 0) document.location.reload();
            else alert(json.resultMessage)
        });

        $(document).on('click' , 'button.btn-auth' ,function (event) {
            event.stopPropagation();
            event.preventDefault();

            var params = {role : $('select[name=role] option:selected').val()}
            var json = $.runsync(http_api_url + '/msp/authRequest', params, 'json', true);
            if(json.resultCode == 0 && json.result == 'succeed') $('.auth_msg').html('정상적으로 사용권한요청 완료').fadeIn();
            else alert(json.resultMessage)
        });

    });


</script>
