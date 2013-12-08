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

if(isset($_POST['dir'])) {
	//$_SESSION['current_dir'] = $_POST['dir'];
	//^^^ bill, wtf? the filter code is RIGHT BELOW THIS, and you didnt even look at the logic
	$dir = filter_var(trim($_POST['dir']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	if(ftp_file_info($ftp, $dir) === 'dir') {
		//is a dir, change
		if(ftp_chdir($ftp, $dir)) {
			$_SESSION['current_dir'] = $dir;
			echo json_dir($ftp,'dirChange','true');
			ftp_close($ftp);
			exit(0);
		}
	}
}

echo json_dir($ftp,'dirChange','false');
ftp_close($ftp);
exit(0);

?>