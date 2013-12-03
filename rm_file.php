<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
ftp_login($ftp, $_SESSION['username'], get_user_pass());

if(isset($_POST['file'])) {
	$file = filter_var(trim($_POST['file']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$cur  = dirname($file);
	ftp_chdir($ftp, $cur);
	$type = ftp_file_info($ftp, $file);
	if($type === 'dir') {
		//check recursion
		if(isset($_POST['recursive']) && $_POST['recursive'] === 'true') {
			if(ftp_rm_recurse($ftp, $file)) {
				echo json_dir('rmFile','true');
				ftp_close($ftp);
				exit(0);
			}
		}
	} elseif($type) {	//file OR link
		//just a file
		if(ftp_delete($ftp, $file)) {
			echo json_dir('rmFile','true');
			ftp_close($ftp);
			exit(0);
		}
	}
}

echo json_dir($ftp,'rmFile','false');
ftp_close($ftp);
exit(0);

?>