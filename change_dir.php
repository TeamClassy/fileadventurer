<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit;
}

if(isset($_POST['dir'])) {
	//clean up path
	$path = realpath($_SESSION['rootdir'].'/'.$_POST['dir']);
	if(is_dir($path)) {
		//split paths
		$root_elements = explode($_SESSION['rootdir'],'/');
		$root_count = count($root_elements);
		$path_elements = explode($path, '/');
		$path_count = count($path_elements);
		//check if path is in bounds
		if($path_elements < $root_elements) {
			echo json_bad();
			exit;
		}
		//check if home path is user's
		for($i=0; $i<$root_count; $i++) {
			if($path_elements[$i] !== $root_elements[$i]) {
				echo json_bad();
				exit;
			}
		}
		//victory
		$_SESSION['curdir'] = substr($path,strlen($_SESSION['rootdir'].'/'));
		echo json_dir("dirChange","true");
	} else {
		//loss
		echo json_dir("dirChange","false");
	}
} else {
	//fail...thanks for not POSTing
	echo json_dir("dirChange","false");
}

?>