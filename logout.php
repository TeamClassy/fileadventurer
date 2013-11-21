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
//delete password cookie
setcookie('FILEADVENTURER_KEY','',1);
//destroy session
session_destroy();
setcookie(session_name(), '', 1);
session_unset();
echo json_bad();
exit(0);

?>