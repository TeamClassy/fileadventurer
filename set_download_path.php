<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

if(isset($_POST['path'])) {
    error_log("setting path".$_POST['path']."");
    $_SESSION['download_path'] = $_POST['path'];
} else {
    error_log("failed to set path");
}

?>