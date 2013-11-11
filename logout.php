<?php
require_once 'php/functions.php';

session_start();
//close SSH process
if(file_exists('/tmp/'.$_SESSION['username'].'.shin')) {
	$sckt  = socket_create(AF_UNIX, SOCK_STREAM, 0);
	socket_connect($sckt, '/tmp/'.$_SESSION['username'].'.shin');
	socket_write($sckt, chr(3).'\n');	//stop anything running for safety
	socket_write($sckt, 'exit');
	socket_close($sckt);
}
//destroy session
session_destroy();
$cookieParams = session_get_cookie_params();
setcookie(session_name(), '', time()-604800, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
session_unset();
echo json_bad();
exit(0);

?>