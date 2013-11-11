<?php

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

//=====================================
//	Inputs:
//		none
//	Returns:
//		user's password
//	Assumptions:
//		session is already started
function get_user_pass()
{
	//TODO:
	//implement user password encryption
	//store AES key in a cookie
	//store ciphertext in session var
	return $_SESSION['password'];
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
	$output = '{"sessionStatus":true,';
	if($flag !== FALSE)
		$output.= '"'.$flag.'":'.$value.',';
	//save current directory
	$curdir = ftp_pwd($ftp);
	//current dir JSON
	$output.= '"dirName":"'.$curdir.'",';
	$output.= '"files":[';
	foreach(ftp_nlist($ftp, '-A') as $file) {
		$output.= json_file_info($ftp, $file);
		//child dir JSON
		@ftp_chdir($ftp, $file);
		if($curdir !== ftp_pwd($ftp)) {
			$output = rtrim($output, '},');
			$output.= ',"content":[';	//notice comma prefix
			foreach(@ftp_nlist($ftp, '-A') as $child_file)
				$output.= json_file_info($ftp, $child_file);
			ftp_cdup($ftp);
			$output = rtrim($output, ',');
			$output.= ']';
			$output.= '},';
		}
	}
	$output = rtrim($output, ',');
	$output.= ']';
	//parent dir JSON
	ftp_cdup($ftp);
	if($curdir !== ftp_pwd($ftp)) {
		$output.= ',"parentDir":[';	//notice comma prefix
		foreach(ftp_nlist($ftp, '-A') as $parent_file)
			$output.= json_file_info($ftp, $parent_file);
		ftp_chdir($ftp, $curdir);
		$output = rtrim($output, ',');
		$output.=']';
	}
	$output.= '}';
	return $output;
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
	//Bill, this is what you want to do to escape chars for JSON
	// $filename = filter_var($filename, FILTER_SANITIZE_STRING, array(FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_HIGH));

	//type check
	@ftp_chdir($ftp, $file_name);
	if($file_name === basename(ftp_pwd($ftp))) {
		$type = '"dir"';
		ftp_cdup($ftp);
	} else {
		$type = strrpos($file_name, '.');
		$type = ($type == 0 ? 'false' : '"'.trim(substr($file_name, $type), '.').'"');	//implicit conversion
	}
	//date check
	$date = ftp_mdtm($ftp,$file_name);
	$date = ($date===-1 ? 'false' : '"'.date('Y-m-d\TH:i:sP',$date).'"');
	//size check
	$size = ftp_size($ftp,$file_name);
	$size = ($size===-1 ? 'false' : '"'.$size.'"');
	//set output
	$output = '{';
	$output.= '"type":'.$type.',';
	$output.= '"name":"'.$file_name.'",';
	$output.= '"date":'.$date.',';
	$output.= '"size":'.$size;
	$output.= '},';
	return $output;
}

?>