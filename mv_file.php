<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}
$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
if(!ftp_login($ftp, $_SESSION['username'], get_user_pass())) {
	echo json_bad();
	exit(0);
}

if(isset($_POST['from']) && isset($_POST['to'])) {
	$from = filter_var(trim($_POST['from']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$to   = filter_var(trim($_POST['to']),	FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$cur  = dirname($from);
	if(ftp_chdir($ftp, $cur)) {
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
	} else $_SESSION['current_dir'] = '/';	//chdir failed, defaults to root
}

echo json_dir($ftp,'mvFile','false');
ftp_close($ftp);
exit(0);

?>