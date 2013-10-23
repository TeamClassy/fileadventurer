<?php
require_once 'php/functions.php';
session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit;
}

$new_dir = $_POST['dir'];
if(is_dir($_SESSION['rootdir'].'/'.$new_dir)) {
	$_SESSION['curdir'] = $new_dir;
	echo json_dir("dirChange","true");
} else {
	echo json_dir("dirChange","false");
}

?>