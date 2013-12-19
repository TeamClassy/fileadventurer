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

$bad_files = false;
if(isset($_POST['file'])) {
	$file = filter_var(trim($_POST['file']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	$cur  = dirname($file);
	if(ftp_chdir($ftp, $cur)) {
		$type = ftp_file_info($ftp, $file);
		if($type === 'dir') {
			//check recursion
			if($bad_files=ftp_rm_recurse($ftp, $file)) {
				echo json_dir($ftp,'rmFile','true');
				ftp_close($ftp);
				exit(0);
			}
		} elseif($type) {
			//file OR link, just delete
			if(ftp_delete($ftp, $file)) {
				echo json_dir($ftp,'rmFile','true');
				ftp_close($ftp);
				exit(0);
			}
			$bad_files = array($file);
		}
	} else $_SESSION['current_dir'] = '/';	//chdir failed, defaults to root
}

echo json_dir($ftp,'rmFile','false',$bad_files);
ftp_close($ftp);
exit(0);

?>