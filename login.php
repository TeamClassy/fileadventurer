<?php
require_once 'php/functions.php';

session_start();
if(isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['host']) && isset($_POST['ftp_port']) && isset($_POST['ssh_port'])) {
	$username = $_POST['user'];
	$password = $_POST['pass'];
    $ftp_port = $_POST['ftp_port'];
    $ssh_port = $_POST['ssh_port'];
    $host = $_POST['host'];
	$ftp_resource = ftp_connect($host, intval($ftp_port));
	$ssh_resource = ssh2_connect($host,intval($ssh_port));
	if($ftp_resource!==FALSE && $ssh_resource!==FALSE) {
		if(ftp_login($ftp_resource, $username, $password) && ssh2_auth_password($ssh_resource, $username, $password)) {
			if(session_regenerate_id(true)) {
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
echo json_bad();

?>