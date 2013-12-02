<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
ftp_login($ftp, $_SESSION['username'], get_user_pass());

if(isset($_POST['from']) && isset($_POST['to'])) {
	$from = filter_var(trim($_POST['from']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$to   = filter_var(trim($_POST['to']),	FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$cur  = dirname($from);
	ftp_chdir($ftp, $cur);
	if(ftp_file_info($ftp, $to)) {
		//check overwrite
		if(isset($_POST['overwrite']) && $_POST['overwrite'] === 'true') {
			if(ftp_rename($ftp, $from, $to)) {
				echo json_dir($ftp,'mvFile','true');
				ftp_close($ftp);
				exit(0);
			}
		}
	} else {
		//all clear
		if(ftp_rename($ftp, $from, $to)) {
			echo json_dir($ftp,'mvFile','true');
			ftp_close($ftp);
			exit(0);
		}
	}
}

echo json_dir($ftp,'mvFile','false');
ftp_close($ftp);
exit(0);

?>