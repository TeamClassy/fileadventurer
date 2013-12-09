<?php
require_once 'php/functions.php';

    session_start();
    if(!is_user_valid()) {
        echo json_bad();
        exit(0);
    }

    $ftp = ftp_connect($_SESSION['host'], $_SESSION['ftp_port']);
    if(!ftp_login($ftp, $_SESSION['username'], get_user_pass())) {
        echo json_bad();
        exit(0);
    }

    if(ftp_chdir($ftp, $_SESSION['current_dir'])) {
        //we have to check here that the array $_FILES was passed
        //And that there was no error in the file transfer
        if(isset($_FILES['file']) && !$_FILES['file']['error']) {
            //no error, woot
            $file_name = filter_var(trim($_FILES['file']['name']),FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
            $temp_name = $_FILES['file']['tmp_name'];
            $temp_hndl = fopen($temp_name,'r');

            if(ftp_fput($ftp, $file_name, $temp_hndl, FTP_BINARY)) {
                echo json_dir($ftp,'uploadSuccess','true');
                ftp_close($ftp);
                exit(0);
            }   
        }
    } else { 
        //chdir failed, defaults to root
        $_SESSION['current_dir'] = '/';	
    }

    echo json_dir($ftp,'uploadSuccess','false');
    ftp_close($ftp);
    exit(0);

?>