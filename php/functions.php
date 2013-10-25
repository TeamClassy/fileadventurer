<?php

//=====================================
//	Inputs:
//		$username  - username to start session for
//		$directory - user's home directory
//	Outputs:
//		TRUE  on success
//		FALSE on failure
function begin_user_session($username, $directory)
{
	session_start();
	if(!session_regenerate_id(true))
		return false;
	$_SESSION['username'] = $username;
	$_SESSION['rootdir'] = $directory;
	$_SESSION['curdir'] = '';
	$_SESSION['fingerprint'] = sha1($_SERVER['HTTP_USER_AGENT'] ^ $username);
	return true;
}

//=====================================
//	Inputs:
//		none
//	Outputs:
//		TRUE  if user is logged in
//		FALSE if user not logged in OR possible attack
function is_user_valid()
{
	session_start();
	if(!isset($_SESSION['username'])
	|| !isset($_SESSION['fingerprint']))
		return false;
	if($_SESSION['fingerprint'] != sha1($_SERVER['HTTP_USER_AGENT'] ^ $_SESSION['username']))
		return false;
	return true;
}

//=====================================
//	Inputs:
//		$flag  - flag name to enter into JSON
//		$value - value of flag to assign
//	Outputs:
//		JSON directory plus supplied flags
function json_dir($flag = FALSE, $value = FALSE)
{
	session_start();
	//construct current dir name
	$dir_name = realpath($_SESSION['rootdir'].'/'.$_SESSION['curdir']);
	//flags
	$output = '{"sessionStatus":true,';
	if($flag !== FALSE)
		$output.= '"'.$flag.'":'.$value.',';
	//current dir JSON
	$output.= '"dirName":"'.$dir_name.'",';
	$output.= '"dirName":"'.$_SESSION['curdir'].'",';
	$output.= '"dirName":"'.$_SESSION['curdir'].'",';
	$output.= '"files":[';
	$dir_handle = opendir($dir_name);
	while($file = readdir($dir_handle)) {
		$cur_file= $dir_name.'/'.$file;
		$output.= json_file_info($cur_file);
		//child dir JSON
		if(is_dir($dir_name.'/'.$file)) {
			$output = rtrim($output, '},');
			$output.= ',"content":[';	//notice comma
			$file = opendir($cur_file);
			while($child_file = readdir($file))
				$output.= json_file_info($cur_file.'/'.$child_file);
			closedir($file);
			$output = rtrim($output, ',');
			$output.= ']';
			$output.= '},';
		}
	}
	$output = rtrim($output, ',');
	$output.= ']';
	//parent dir JSON
	if($dir_name !== $_SESSION['rootdir']) {	//only if not in root
		$output.= ',"parentDir":[';	//notice comma
		$parent_name = dirname($dir_name);
		$parent_dir  = opendir($parent_name);
		while($file = readdir($parent_dir))
			$output.= json_file_info($parent_name.'/'.$file);
		closedir($parent_dir);
		$output = rtrim($output, ',');
		$output.=']';
	}
	closedir($dir_handle);
	$output.= '}';
	return $output;
}

//===================================
//	Inputs:
//		none
//	Outpus:
//		JSON flag for bad login
function json_bad()
{
	return '{"sessionStatus":false}';
}






//========================================
//	Not to be used outside of this file
//========================================

//	Inputs:
//		$file_path - absolute path to file
//	Outputs:
//		JSON format of file data
function json_file_info($file_path)
{
	if(basename($file_path) === '.' || basename($file_path) === '..')
		return '';
	$output = '{';
	$output.= '"type":"'.filetype($file_path).'",';
	$output.= '"name":"'.basename($file_path).'",';
	$output.= '"date":"'.date("Y-m-d\TH:i:sP",filemtime($file_path)).'",';
	$output.= '"size":"'.filesize($file_path).'"';
	$output.= '},';
	return $output;
}

?>
