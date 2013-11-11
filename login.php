<?php
require_once 'php/functions.php';

session_start();
if( isset($_POST['user'])
	&& isset($_POST['pass'])
	&& isset($_POST['host'])
	&& isset($_POST['ssh_port'])
	&& isset($_POST['ftp_port']) ) {
	//sanitize
	$username =	filter_var($_POST['user'],FILTER_SANITIZE_STRING);
	$password =	filter_var($_POST['pass'],FILTER_SANITIZE_STRING);
	$host     = trim($_POST['host']);
	if($host !== 'localhost') {
		if(!$host=filter_var($host,FILTER_VALIDATE_IP)) {
			if(!$host=filter_var($host,FILTER_VALIDATE_URL)) {
				$host = 'localhost';	//should this default to something else?
			}
		}
	}
	$ftp_port =	intval($_POST['ftp_port']);
	$ssh_port =	intval($_POST['ssh_port']);
	//connect
	if($username !== FALSE && $password !== FALSE) {
		$ftp_resource = ftp_connect($host, $ftp_port);
		$ssh_resource = ssh2_connect($host, $ssh_port);
		if($ftp_resource!==FALSE && $ssh_resource!==FALSE) {
			if(ftp_login($ftp_resource, $username, $password) && ssh2_auth_password($ssh_resource, $username, $password)) {
				if(session_regenerate_id(true)) {
					switch(pcntl_fork())
					{
						case -1:
							//error, couldn't fork
							error_log($username.': Couldn\'t fork');
							echo json_bad();
							break;
						case 0:
							//child
							child_ssh($username, $password, $host, $ssh_port);
							posix_kill(posix_getpid(), SIGHUP);
							break;
						default:
							//parent
							$_SESSION['username'] = $username;
							$_SESSION['password'] = $password;
							$_SESSION['host']     = $host;
							$_SESSION['ftp_port'] = $ftp_port;
							$_SESSION['fingerprint'] = sha1($_SERVER['HTTP_USER_AGENT']);
							echo json_dir($ftp_resource);
							ssh2_exec($ssh_resource, 'exit');
							ftp_close($ftp_resource);
							break;
					}
					exit(0);
				}
			}
		}
	}
}
echo json_bad();
exit(0);

?>