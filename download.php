<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}

if(isset($_SESSION['download_path'])) {
    error_log("about to download".$_SESSION['download_path']);
    //rtirm is necessary as there are there are null characters at the
    //end of the password
    $ftp_pwd_string = $_SESSION['username'].":".rtrim(get_user_pass())."";
    $file_path = $_SESSION['download_path'];
    unset($_SESSION['download_path']);
    $host ="";
    //if connection is set to localhost this might work fine on our
    //end, but they client needs the full path
    if($_SESSION['host'] == 'localhost') {
        $host = "pacificminecraft.com";
    } else {
        $host = $_SESSION['host'];
    }
    $ftp_host = "ftp://".$ftp_pwd_string."@".$host.":".$_SESSION['ftp_port'].$file_path."";
    
    header('Location: '.$ftp_host."");
} else {
    error_log("Failed to download file");
    echo "Something is bad";
}

?>