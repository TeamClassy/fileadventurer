<?php

//Constant Variables that define JSON fields
define("SESSION_STATUS","sessionStatus");
define("CUR_DIR","dirName");
define("FILES","files");
define("FILE_NAME","name");
define("FILE_TYPE","type");
define("FILE_SIZE","size");
define("DATE","date");
define("FOLDER_CONTENT","content");
define("PARENT_DIR","parentDir");
define("DIR_IDENTIFIER","d");

//=====================================
//	Inputs:
//		none
//	Returns:
//		TRUE  if user is logged in
//		FALSE if user not logged in OR possible attack
//	Assumptions:
//		session is already started
function is_user_valid()
{
	if(!isset($_SESSION['username'])
	|| !isset($_SESSION['password'])
	|| !isset($_SESSION['host'])
	|| !isset($_SESSION['ftp_port'])
	|| !isset($_SESSION['fingerprint'])
	|| $_SESSION['fingerprint'] !== sha1($_SERVER['HTTP_USER_AGENT']))
		return false;
	return true;
}

//====================================
//	Inputs:
//		$plain - plaintext user password
//	Returns:
//		ciphertext - success
//		false 	   - failure
//	Assumptions:
//		session is started
function set_user_pass($plain)
{
	if($rand_src = fopen('/dev/urandom','r')) {
		if($key = fread($rand_src, 32)) {
			$key = md5($key);	//TODO: change hash? outputs 128, so simpler
			fclose($rand_src);
			$cipher = @mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plain, MCRYPT_MODE_CBC);	//TODO: add IV? suppressed warning for now
			if(setcookie('FILEADVENTURER_KEY', $key))
				return rtrim($cipher,'\0');
		}
		fclose($rand_src);
	}
	return false;
}

//=====================================
//	Inputs:
//		none
//	Returns:
//		user's password
//		false - failure (cookie not set)
//	Assumptions:
//		session is already started
function get_user_pass()
{
	if(isset($_COOKIE['FILEADVENTURER_KEY']))
		return rtrim(@mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $_COOKIE['FILEADVENTURER_KEY'], $_SESSION['password'], MCRYPT_MODE_CBC),'\0');
	return false;
}
//=====================================
//	Inputs:
//		$element = string element from ftp_rawlist
//	Returns:
//		array with data element, that can be converted to json
//	Assumptions:
//		session is already started
function parse_raw_element($element)
{
    //Each element of the array ftp_rawlist returns should look like
    //"drwxr-x---  15 vincent  vincent      4096 Nov  3 21:31
    //public_html"
    $return_file = array();
    //this line splits the input into tokens based on spaces
    //it will split it into at most 9 tokens, this is so that the
    //filename is always a single token
    $tokens = preg_split("/\s+/",$element,9);
    $filename = $tokens[8];
    if($tokens[0][0] === 'd')
    {
        $return_file[FILE_TYPE] = "dir";
    }
    else
    {
        $return_file[FILE_TYPE] = trim(substr($filename,strrpos($filename,'.')),'.');
    }
    //$return_file['permissions'] = $tokens[0];
    //$return_file['id'] = $tokens[1];
    //$return_file['owner'] = $tokens[2];
    //$return_file['group'] = $tokens[3];
    if($return_file[FILE_TYPE] === "dir")
    {
        //the size info we get from rawlist is irrelevant for
        //directories, so we ignore it
        $return_file[FILE_SIZE] = false;
    }
    else
    {
        $return_file[FILE_SIZE] = $tokens[4];
    }
    //this date is currently overwritten else where
    $month = $tokens[5];
    $day = $tokens[6];
    $time = $tokens[7];
    $return_file[DATE] = $month.' '.$day.' '.$time."";

    $return_file[FILE_NAME] = $tokens[8];

    return $return_file;
}


//=====================================
//	Inputs: 
//        $ftp = ftp resource
//        $depth = level to recur to (0) if no recursion (1) is
//	      default. If you want infinite recusion pass INF
//	Returns:
//		All
//	Assumptions:
//		session is already started
//		$ftp is set with initiated FTP resource
//      ftp is set to the correct directory
function files_in_cur_dir($ftp,$depth = 1)
{   
    //error_log("Getting data from ".ftp_pwd($ftp));
    $files = array();
    foreach(ftp_rawlist($ftp, '-A') as $file) {
        $temp_file = parse_raw_element($file);

        //we get the date out here, because we want it in a different
        //format, then what we get from rawlist
         $date = ftp_mdtm($ftp,$temp_file[FILE_NAME]);
         $date = ($date===-1 ? false : date('Y-m-d\TH:i:sP',$date)."");
         $temp_file[DATE] = $date;

		if($temp_file[FILE_TYPE] === "dir" && $depth > 0  ) {
            //if current element is a directory and we have a non-zero
            //recursion level, then get recur and get subdirectory
            //info
            $child_content = array();
            if(@ftp_chdir($ftp, $temp_file[FILE_NAME]))
            {
                // if we can access the folder get the info inside
                $child_content = files_in_cur_dir($ftp,$depth - 1);
                ftp_cdup($ftp);
            }
             $temp_file[FOLDER_CONTENT] = $child_content;
		}
        // push most recent file into file array
        $files[] = $temp_file;
	}
    return $files;
}


//=====================================
//	Inputs:
//		$ftp   - ftp resource handle
//		$flag  - flag name to enter into JSON
//		$value - value of flag to assign
//	Returns:
//		JSON directory plus supplied flags
//	Assumptions:
//		session is already started
//		$ftp is set with initiated FTP resource
function json_dir($ftp, $flag = FALSE, $value = FALSE)
{
	//flags
    error_log("in json_dir");
    $json_data = array();
	
	//save current directory
	$curdir = ftp_pwd($ftp);

	//current dir JSON
    $files = files_in_cur_dir($ftp,1);

    $parent_dir = array();
	//parent dir JSON
	//ftp_cdup($ftp);
	if(@ftp_cdup($ftp)) {
		$parent_dir = files_in_cur_dir($ftp,0);
		ftp_chdir($ftp, $curdir);
	}
    $json_data[SESSION_STATUS] = true;
    if($flag !== FALSE)
    {
        //What is the purpose of this?
        $json_data[$flag] = $value;
    }
    $json_data[CUR_DIR] = $curdir;
    $json_data[FILES] = $files;
    $json_data[PARENT_DIR] = $parent_dir;
    error_log("returning data");
	return json_encode($json_data);
}

//===================================
//	Inputs:
//		none
//	Returns:
//		JSON flag for bad function
function json_bad()
{
	return '{"sessionStatus":false}';
}

//===================================
//	Inputs:
//		$username - login name
//		$password - login pass
//		$host     - host to connect to
//		$port     - ssh port to use
//	Outputs:
//		none
//	Assumptions
//		inputs are valid
function child_ssh($username, $password, $host, $port)
{
	$pid = posix_getpid();
	sleep(1);	//for safety
	//output to write
	$out = socket_create(AF_UNIX, SOCK_STREAM, 0);
	socket_bind($out, '/tmp/'.$username.'.shout');
	socket_listen($out);
	socket_set_nonblock($out);
	//input to read
	$in  = socket_create(AF_UNIX, SOCK_STREAM, 0);
	socket_bind($in, '/tmp/'.$username.'.shin');
	socket_listen($in);
	socket_set_nonblock($in);
	//set up shell
	$ssh = ssh2_connect($host, $port);
	ssh2_auth_password($ssh, $username, $password);
	$sh = ssh2_shell($ssh);
	stream_set_blocking($sh, false);
	//work loop
	$last_time = time();
	while(false)	//UNTIL FULLY IMPLEMENTED
	//while(true)
	{
		//shell commands
		if($tmp_in=@socket_accept($in)) {	//non-blocking, will return false if nothing
			$command = explode('\n', socket_read($tmp_in, 100));
			foreach ($command as $single) {
				fwrite($sh, $single.PHP_EOL);
				error_log($pid.' COMMAND: '.$single);
				if(trim($single) === 'exit') break;
			}
			$last_time = time();
		}
		//write shell output
		if($output=fgets($sh)) error_log($pid.' OUTPUT: '.$output);	//DEBUG LINE
		if($tmp_out=@socket_accept($out)) {	//non-blocking
			$output = fgets($sh);
			if($output !== FALSE) {
				socket_write($tmp_out, $output);
				error_log($pid.' WRITE: '.$output);
			}
			$last_time = time();
		}
		//safety time-out
		$cur_time = time()-$last_time;
		if($cur_time > 10) break;
		//don't kill system
		usleep(100000);	//0.1 seconds
	}
	//clean up
	error_log($pid.' Cleaning Up');
	socket_shutdown($in);
	socket_shutdown($out);
	socket_close($in);
	socket_close($out);
	unlink('/tmp/'.$username.'.shin');
	unlink('/tmp/'.$username.'.shout');
}




//========================================
//	Not to be used outside of this file
//========================================

//========================================
//	Inputs:
//		$ftp       - ftp resource handle
//		$file_path - absolute path to file
//	Returns:
//		JSON format of file data
function json_file_info($ftp, $file_name)
{
    $json_file_data = array();
	//type check
	@ftp_chdir($ftp, $file_name);
    $type = "";
	if($file_name === basename(ftp_pwd($ftp))) {
		$type = "dir";
		ftp_cdup($ftp);
	} else {
		$type = strrpos($file_name, '.');
		$type = ($type == 0 ? false : trim(substr($file_name, $type), '.')."");	//implicit conversion
	}
	//date check
	$date = ftp_mdtm($ftp,$file_name);
	$date = ($date===-1 ? false : date('Y-m-d\TH:i:sP',$date)."");
	//size check
	$size = ftp_size($ftp,$file_name);
	$size = ($size===-1 ? false : $size."");
	//set output
    $json_file_data[FILE_TYPE] = $type;
    $json_file_data[FILE_NAME] = $file_name;
    $json_file_data[DATE] = $date;
    $json_file_data[FILE_SIZE] = $size;
	return $json_file_data;
}

?>