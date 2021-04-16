<?php
require_once("config.php");

//----------------------------------------------------------------------------
//
//
$tempfile = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];

$type = substr($filename, strrpos($filename, ".")+1);
$found = false;
switch ($type) {
	case "jpg":
	case "jpeg":
	case "gif":
	case "png":
		$found = true;
}

if ($found != true) {
	exit;
}


// ���� ���� �̸�: ����Ͻú���_��������8��
// 20140327125959_abcdefghi.jpg
// ���� ���� �̸�: $_POST["origname"]+
$ftp_make_dir  = date('Y').'/'.date('m').'/'. date('d');
$filename = time() . '_' . md5($_FILES['file']['name']) .'.'. $type;
ftpUpload(SAVE_DIR , $ftp_make_dir ,$filename , $tempfile);

$savefile = SAVE_DIR . '/' .'/'. $ftp_make_dir .'/'. $filename;

/*move_uploaded_file($tempfile, $savefile);
$imgsize = getimagesize($savefile);
$filesize = filesize($savefile);*/

/*if (!$imgsize) {
	$filesize = 0;
	$random_name = '-ERR';
	unlink($savefile);
};*/

$file_path_name = $ftp_make_dir ."/" . $filename;
$rdata = sprintf('{"fileUrl": "%s/%s", "filePath": "%s", "fileName": "%s", "fileSize": "%d" }',
	SAVE_URL,
    $file_path_name,
	$savefile,
	$filename,
    $_FILES['file']['size'] );

echo $rdata;


function ftpUpload($absolute_path ,  $ftp_make_dir , $filename , $origin_file){
    $result = array();

    $conn_id = ftp_connect('15.164.129.253', 7788);
    $conn_login = ftp_login($conn_id, 'ftp_circler', 'Tjzmffj@!ftp');

    $result['result'] = false;
    $result['msg'] = '';
    if(!$conn_id || !$conn_login){
        $result['result'] = true;
        $result['msg'] = 'FTP 연결에 실패하였습니다.';
    }else{
        ftp_pasv($conn_id, true);
    }
    //$absolute_path = "/data/upload/circler";
    //$ftp_make_dir = date('Y').'/'.date('m').'/'. date('d');
    //$filename = time() .'_' . $_FILES['filename']['name'][0];

    $exp_path = explode('/' , $ftp_make_dir);

    $make_dir = '';
    foreach($exp_path as $key=>$dir){
        $make_dir .= "/" . $dir;
        if(@!ftp_chdir($conn_id ,$make_dir)){
            @ftp_mkdir($conn_id, $make_dir);
        }
    }
    $upload_path = $absolute_path .'/'.$ftp_make_dir ;
    if(!ftp_put($conn_id , $filename , $origin_file , FTP_BINARY)){
        $result['result'] = true;
        $result['msg'] = 'FTP UPLOAD FAIL.';
    }else{
        $result['result'] = true;
    }

}
?>
