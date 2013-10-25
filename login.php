<?php
require_once 'php/functions.php';

if(isset($_POST['user']) && isset($_POST['pass'])) {
	$username = $_POST['user'];
	$password = $_POST['pass'];
	//Connect and check for errors
	$db = new mysqli('localhost','login','kafsc0merc','login');
	if($db->connect_errno > 0) {
		echo json_bad();	//this should have some other error, too
		$db->close();
		exit;
	}
	$result = $db->query('select * from user where username="'.$username.'"');
	if(!$result) {
		echo json_bad();
		$db->close();
		exit;
	}
	$rows = $result->fetch_assoc();
	//Why was this a thing?
	//$db_user = $rows['username'];
	//if($db_user !== $username) {
	//	echo json_bad();
	//	$db->close();
	//	exit;
	//}
	if($rows['password'] === crypt($password, $rows['salt'])) {
		begin_user_session($username, $rows['directory']);
		echo json_dir();
	} else {
		echo json_bad();
	}
	$db->close();
} else {
	echo json_bad();
}

?>