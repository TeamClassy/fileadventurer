<?php
require_once 'php/functions.php';

session_start();
if(isset($_POST['user']) && isset($_POST['pass'])) {
	$username = $_POST['user'];
	$password = $_POST['pass'];
	$ftp_resource = ftp_connect('localhost', 7821);
	$ssh_resource = ssh2_connect('localhost',7822);
	if($ftp_resource!==FALSE && $ssh_resource!==FALSE) {
		if(ftp_login($ftp_resource, $username, $password) && ssh2_auth_password($ssh_resource, $username, $password)) {
			if(session_regenerate_id(true)) {
				$_SESSION['username'] = $username;
				$_SESSION['password'] = $password;
				$_SESSION['ftp'] = $ftp_resource;
				$_SESSION['ssh'] = $ssh_resource;	//check persistance of this when implemented
				$_SESSION['fingerprint'] = sha1($_SERVER['HTTP_USER_AGENT']);
				//ssh2_exec($ssh_resource, 'exit');
				echo json_dir();
				exit(0);
			}
		}
	}
}
echo json_bad();

?>