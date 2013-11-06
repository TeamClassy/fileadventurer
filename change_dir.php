<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

$ftp = ftp_connect('localhost', 7821);
ftp_login($ftp, $_SESSION['username'], get_user_pass());

if(isset($_POST['dir'])) {
	$req_dir = $_POST['dir'];
	$cur_dir = ftp_pwd($ftp);
	if(strpos($req_dir, '/') === 0) $req_dir = $req_dir;	//absolute path
	else							$req_dir = $cur_dir.'/'.$req_dir;	//relative path
	@ftp_chdir($ftp, $req_dir);
	if(ftp_pwd($ftp) !== $req_dir) {
		//failed
		@ftp_chdir($ftp, $cur_dir);
		echo json_dir('dirChange','false');
	} else {
		echo json_dir('dirChange','true');
	}
} else {
	echo json_dir('dirChange','false');
}

ftp_close($ftp);
exit(0);

?>