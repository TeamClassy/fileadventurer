<?php

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

function json_dir($flag, $value)
{
	session_start();
	$dir_name = $_SESSION['rootdir'];
	if($_SESSION['curdir'] !== '')
		$dir_name.= '/'.$_SESSION['curdir'];
	$output = '{"sessionStatus":true,';
	//custom flag
	if($flag)
		$output.= '"'.$flag.'":'.$value.',';
	$output.= '"dirName":"'.$dir_name.'",';
	$output.= '"files":[';
	$dir_handle = opendir($dir_name);
	//begin main directory
	while($file = readdir($dir_handle)) {
		if($file === '.') continue;
		if($file === '..') continue;
		$cur_file= $dir_name.'/'.$file;
		$output.= json_file_info($cur_file);
		//child directory
		if(is_dir($dir_name.'/'.$file)) {
			$output.= ',"content":[';	//notice comma
			$file = opendir($cur_file);
			while($child_file = readdir($file)) {
				if($child_file === '.') continue;
				if($child_file === '..') continue;
				$output.= json_file_info($cur_file.'/'.$child_file);
				$output.= '},';
			}
			closedir($file);
			$output = rtrim($output, ',');
			$output.= ']';
		}
		$output.= '},';
	}
	$output = rtrim($output, ',');
	$output.= ']';	//no comma - only if parent dir exists
	//parent directory
	if($_SESSION['curdir'] !== '') {	//only if not in root
		$output.= ',"parentDir":[';	//notice the comma
		$parent_name = dirname($dir_name);
		$parent_dir  = opendir($parent_name);
		while($file = readdir($parent_dir)) {
			if($file === '.') continue;
			if($file === '..') continue;
			$output.= json_file_info($parent_name.'/'.$file);
			$output.= '},';
		}
		closedir($parent_dir);
		$output = rtrim($output, ',');
		$output.=']';
	}

	$output.= '}';
	closedir($dir_handle);
	return $output;
}

function json_file_info($file_path)
{
	$output = '{';
	$output.= '"type":"'.filetype($file_path).'",';
	$output.= '"name":"'.basename($file_path).'",';
	$output.= '"date":"'.date("Y-m-d\TH:i:sP",filemtime($file_path)).'",';
	$output.= '"size":"'.filesize($file_path).'"';
	return $output;
}

function json_bad()
{
	return '{"sessionStatus":false}';
}

?>