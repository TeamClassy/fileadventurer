<?php
require_once 'php/functions.php';

session_start();
if(!is_user_valid()) {
	echo 'FAILURE';
	exit(0);
}

if(isset($_GET['file'])) {
    $file = filter_var(trim($_GET['file']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
    if($write = fopen('php://output', 'w')) {
        $ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
        ftp_login($ftp, $_SESSION['username'], get_user_pass());
        //set header to always download
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="'.basename($file).'"');
        if(!ftp_fget($ftp, $write, $file, FTP_BINARY)) {
            header_remove('Content-Type');
            header_remove('Content-Transfer-Encoding');
            header_remove('Content-disposition');
            header('Content-Type: text/html');
            echo 'FAILURE';
        }
        ftp_close($ftp);
        fclose($write);
        exit(0);
    }

    $ftp_url = "ftp://".$_SESSION['host'].":".$_SESSION['ftp_port'].$file_path."";
    
    //This creates a temporary place to save the file, we use username
    //and host as those must be unique
    $temp_folder = '/tmp/file_ad_temp/'.$_SESSION['username'].".".$_SESSION['host']."/";
    if (!file_exists($temp_folder)) {
        error_log("the folder :".$temp_folder." did not exist");
        mkdir($temp_folder, 0770, true);
    } else { error_log("the folder :".$temp_folder." should exist"); }
    
    $passwd_string = $_SESSION['username'].":".rtrim(get_user_pass());
    $curl = curl_init();
    
    //$tmpname = tempnam($temp_folder);
    //error_log($tmpname."");
    $file = fopen($temp_folder."house",'w+');//tmpfile();// fopen($temp_folder.$tmpname."", 'w');
    error_log("FTP_URL : ".$ftp_url);
    $curl_stderr = fopen($temp_folder."curl_error","w");
    curl_setopt($curl, CURLOPT_URL, $ftp_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FILE, $file);
   // curl_setopt($curl, CURLOPT_VERBOSE, 1); //just for debugging
   // curl_setopt($curl, CURLOPT_STDERR, $curl_stderr);
    curl_setopt($curl, CURLOPT_USERPWD, $passwd_string);
    curl_exec($curl);
    $info = curl_getinfo($curl);
//    error_log(var_dump($info));
    curl_close($curl);
    fclose($file);

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . basename($file_path) . "\"");
    readfile($temp_folder."house");
    error_log("file transfer is done.");

    //header('Location: '.$ftp_host."");
} else {
    error_log("Failed to download file");
    echo "Something is bad";
}
echo 'FAILURE';
exit(0);

?>