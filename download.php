<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

if(isset($_SESSION['download_path'])) {
    error_log("about to download".$_SESSION['download_path']);
    $ftp_pwd_string = $_SESSION['username'].":".rtrim(get_user_pass())."";
    $file_path = $_SESSION['download_path'];
    unset($_SESSION['download_path']);
    $host ="";
    if($_SESSION['host'] == 'localhost') {
        $host = "pacificminecraft.com";
    } else {
        $host = $_SESSION['host'];
    }
    $ftp_host = "ftp://".$ftp_pwd_string."@".$host.":".$_SESSION['ftp_port'].$file_path."";
    
    header('Location: '.$ftp_host."");
} else {
    echo "Something is bad";
}

?>