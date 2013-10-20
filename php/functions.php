<?php

function begin_user_session($username, $directory)
{
	session_start();
	if(!session_regenerate_id(true))
		return false;
	$_SESSION['initiated'] = true;
	$_SESSION['username'] = $username;
	$_SESSION['directory'] = $directory;
	$_SESSION['fingerprint'] = sha1($_SERVER['HTTP_USER_AGENT'] ^ $username);
	return true;
}

function is_user_valid()
{
	session_start();
	if(!isset($_SESSION['initiated'])
		|| !isset($_SESSION['username'])
		|| !isset($_SESSION['fingerprint']))
		return false;
	if($_SESSION['fingerprint'] != sha1($_SERVER['HTTP_USER_AGENT'] ^ $_SESSION['username']))
		return false;
	return true;
}

function json_dir($flag, $value)
{
	//way to go connor...
	//now fix it
}

function json_bad()
{
	return '{"sessionStatus":false}';
}

?>