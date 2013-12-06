<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo 'FAILURE';
	exit(0);
}

if(isset($_POST['file'])) {
    $file = filter_var(trim($_POST['file']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
    if($write = fopen('php://output', 'w')) {
        $ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
        ftp_login($ftp, $_SESSION['username'], get_user_pass());
        //set header to always download
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="'.basename($file).'"');
        if(!ftp_fget($ftp, $write, $file, FTP_BINARY))
            echo 'FAILURE';
        ftp_close($ftp);
        fclose($write);
        exit(0);
    }
}
echo 'FAILURE';
exit(0);

?>