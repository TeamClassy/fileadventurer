<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

ftp_login($_SESSION['ftp'], $_SESSION['username'], $_SESSION['password']);

if(isset($_POST['dir'])) {
	$cur_dir = ftp_pwd($_SESSION['ftp']);
	@ftp_chdir($_SESSION['ftp'], $cur_dir);
	if($cur_dir !== ftp_pwd($_SESSION['ftp'])) {
		echo json_dir('dirChange','true');
		exit(0);
	}
}
echo json_dir('dirChange','false');

?>