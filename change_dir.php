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
    //Todo if there is a possible error with the dir change there are
    //probably other parts of the code that need to handle this
    $_SESSION['current_dir'] = $_POST['dir'];
	$dir = filter_var(trim($_POST['dir']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
	if(ftp_file_info($ftp, $dir) === 'dir') {
		//is a dir, change
		if(ftp_chdir($ftp, $dir)) {
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