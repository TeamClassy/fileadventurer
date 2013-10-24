<?php
require_once 'php/functions.php';

$username = $_POST['user'];
//$password = $_POST['pass'];

if($username) {
	//Connect and check for errors
	$db = new mysqli('localhost','login','kafsc0merc','login');
	if($db->connect_errno > 0) {
		echo json_bad();
		$db->close();
		exit;
	}
	//Query the database
	$query = 'select * from user where username="'.$username.'"';
	$result = $db->query($query);
	if(!$result) {
		echo json_bad();
		$db->close();
		exit;
	}
	//Parse DB rows
	$rows = $result->fetch_assoc();
	$user = $rows['username'];
	//User does not exist
	if($user !== $username) {
		echo json_bad();
		$db->close();
		exit;
	}
	$salt = $rows['salt'];
	$pass = $rows['password'];
	/*if($pass === crypt($password, $salt)) {
		begin_user_session($username, $rows['directory']);
		echo json_dir()
	} else {
		echo json_bad();
	}*/
	begin_user_session($username, $rows['directory']);
	$db->close();
}

?>
