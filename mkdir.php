<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
ftp_login($ftp, $_SESSION['username'], get_user_pass());

if(isset($_POST['dir'])) {
	$dir = filter_var(trim($_POST['dir']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$cur = dirname($dir);
	ftp_chdir($ftp, $cur);
	if(!ftp_file_info($ftp, $to)) {
		//does not exist, create
		if(ftp_mkdir($ftp, $dir)) {
			echo json_dir($ftp, 'mkDir','true');
			ftp_close($ftp);
			exit(0);
		}
	}
}

echo json_dir($ftp,'mkDir','false');
ftp_close($ftp);
exit(0);

?>