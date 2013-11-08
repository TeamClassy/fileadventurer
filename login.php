<?php
require_once 'php/functions.php';

session_start();
if( isset($_POST['user']) && is_string($_POST['user'])
	&& isset($_POST['pass']) && is_string($_POST['pass'])
	&& isset($_POST['host']) && is_int($_POST['host'])
	&& isset($_POST['ssh_port']) && is_int($_POST['ssh_port'])
	&& isset($_POST['ftp_port']) && is_int($_POST['ftp_port'])) {
	//sanitize
	$username = filter_var($_POST['user'],FILTER_SANITIZE_STRING);
	$password = filter_var($_POST['pass'],FILTER_SANITIZE_STRING);
	$host =     $_POST['host'];
	$ftp_port = $_POST['ftp_port'];
	$ssh_port = $_POST['ssh_port'];
	//connect
	if($username !== FALSE && $password !== FALSE) {
		$ftp_resource = ftp_connect($host, $ftp_port);
		$ssh_resource = ssh2_connect($host, $ssh_port);
		if($ftp_resource!==FALSE && $ssh_resource!==FALSE) {
			if(ftp_login($ftp_resource, $username, $password) && ssh2_auth_password($ssh_resource, $username, $password)) {
				if(session_regenerate_id(true)) {
					//Forking will go here
					$_SESSION['username'] = $username;
					$_SESSION['password'] = $password;
	                $_SESSION['host'] = $host;
	                $_SESSION['ssh_port'] = $ssh_port;
	                $_SESSION['ftp_port'] = $ftp_port;
					//$_SESSION['ssh'] = $ssh_resource;	//check persistance of this when implemented
					$_SESSION['fingerprint'] = sha1($_SERVER['HTTP_USER_AGENT']);
					//ssh2_exec($ssh_resource, 'exit');
					echo json_dir($ftp_resource);
					ftp_close($ftp_resource);
					exit(0);
				}
			}
		}
	}
}
echo json_bad();
exit(0);

?>