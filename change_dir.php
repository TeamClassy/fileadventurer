<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

$_SESSION['ftp'] = ftp_connect('localhost', 7821);
ftp_login($_SESSION['ftp'], $_SESSION['username'], $_SESSION['password']);

if(isset($_POST['dir'])) {
	if(strpos($_POST['dir'], '/') !== FALSE)
		$dir = realpath($_POST['dir']);
	else
		$dir = $_POST['dir'];
	@ftp_chdir($_SESSION['ftp'], $dir);
	if($dir === ftp_pwd($_SESSION['ftp'])) {
		echo json_dir('dirChange','true');
		exit(0);
	}
}
echo json_dir('dirChange','false');

?>