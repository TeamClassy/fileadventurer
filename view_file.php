<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

if(isset($_GET['file'])) {
    $file = filter_var(trim($_GET['file']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
    if($write = fopen('php://output', 'w')) {
    	//get mime type
		$mime = get_mime_type($file);
		//login to ftp
		$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
		if(ftp_login($ftp, $_SESSION['user', get_user_pass())) {
			//set header to correct mime type
			header('Content-Type: '.$mime);
			header('Content-Transfer-Encoding: Binary');
			if(!ftp_fget($ftp, $write, $file, FTP_BINARY)) {
				header_remove('Content-Type');
				header_remove('Content-Transfer-Encoding');
				header('Content-Type: text/html');
				echo 'FAILURE';
			}
			ftp_close($ftp);
			fclose($write);
			exit(0);
		}
    }
}
echo 'FAILURE';
exit(0);

?>