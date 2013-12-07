<?php
require_once '../php/functions.php';

session_start();
if(!is_user_valid()) {
	echo json_bad();
	exit(0);
}
error_log($_SESSION['current_dir']."");

$ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
if(ftp_login($ftp, $_SESSION['username'], get_user_pass())) {
        //echo "ftp login succeded"."<br>";
} else {
        //echo "ftp login failed"."<br>";
}

if(!ftp_chdir($ftp,$_SESSION['current_dir']))
{
    error_log("ftp failed to change dirs:".$_SESSION['currentDir']."");
    //TODO better error correcting here, it is not immediately clear
    //what should be done here
    //TODO remove profanity
    echo "{'SomethingSeriouslyFuckedUp': true}";
    
} else {

if ($_FILES["file"]["error"] > 0)
  {
    // there was an error during the upload print information about it
    //echo "Error: " . $_FILES["file"]["error"] . "<br>";
    echo json_dir($ftp,"uploadSuccess",false);
  }
else
  {
    //the upload was successful, but the uploaded file was only stored
    //in our temp directory we still need to get it to the target ftp
    //server
    $upload_file_name = $_FILES["file"]["name"];
    $temp_file = $_FILES["file"]["tmp_name"];
    //echo "hello: ".$_SESSION['username']."<br>";
    //echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    //echo "Type: " . $_FILES["file"]["type"] . "<br>";
    //echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    //echo "Stored in: " . $_FILES["file"]["tmp_name"]."<br>";
    
   

    $temp_file_handle = fopen($temp_file,'r');
    //echo "Storing in the directory: ".ftp_pwd($ftp)."<br>";

    //We transfer all files in binary all this means is that new os's
    //won't immediately identity text files as such
    $ftp_transfer_mode = FTP_BINARY;
    if(ftp_fput($ftp,$upload_file_name,$temp_file_handle,$ftp_transfer_mode)) {
        error_log("upload succeded");
        //echo "Successfully uploaded file to ftp server"."<br>";
        echo json_dir($ftp,"uploadSuccess",true);
    } else {
        error_log("upload failed");
        //echo "Error file upload failed"."<br>";
        echo json_dir($ftp,"uploadSuccess",false);
    }
    
  }
}
?>