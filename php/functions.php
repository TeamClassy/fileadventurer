<?php

//=====================================
//	Inputs:
//		none
//	Outputs:
//		TRUE  if user is logged in
//		FALSE if user not logged in OR possible attack
//	Assumptions:
//		session is already started
function is_user_valid()
{
	if(!isset($_SESSION['username'])
	|| !isset($_SESSION['password'])
	|| !isset($_SESSION['fingerprint'])
	|| !isset($_SESSION['ssh']))
		return false;
	if($_SESSION['fingerprint'] !== sha1($_SERVER['HTTP_USER_AGENT']))
		return false;
	return true;
}

//=====================================
//	Inputs:
//		$flag  - flag name to enter into JSON
//		$value - value of flag to assign
//	Outputs:
//		JSON directory plus supplied flags
//	Assumptions:
//		session is already started
//		$_SESSION['ftp'] is set with resource
function json_dir($flag = FALSE, $value = FALSE)
{
	//flags
	$output = '{"sessionStatus":true,';
	if($flag !== FALSE)
		$output.= '"'.$flag.'":'.$value.',';
	//save current directory
	$curdir = ftp_pwd($_SESSION['ftp']);
	//current dir JSON
	$output.= '"dirName":"'.$curdir.'",';
	$output.= '"files":[';
	foreach(ftp_nlist($_SESSION['ftp'], '-A') as $file) {
		$output.= json_file_info($file);
		//child dir JSON
		@ftp_chdir($_SESSION['ftp'], $file);
		if($curdir !== ftp_pwd($_SESSION['ftp'])) {
			$output = rtrim($output, '},');
			$output.= ',"content":[';	//notice comma prefix
			foreach(@ftp_nlist($_SESSION['ftp'], '-A') as $child_file)
				$output.= json_file_info($child_file);
			ftp_cdup($_SESSION['ftp']);
			$output = rtrim($output, ',');
			$output.= ']';
			$output.= '},';
		}
	}
	//ftp_cdup($_SESSION['ftp']);
	$output = rtrim($output, ',');
	$output.= ']';
	//parent dir JSON
	ftp_cdup($_SESSION['ftp']);
	if($curdir !== ftp_pwd($_SESSION['ftp'])) {
		$output.= ',"parentDir":[';	//notice comma prefix
		foreach(ftp_nlist($_SESSION['ftp'], '-A') as $parent_file)
			$output.= json_file_info($parent_file);
		ftp_chdir($_SESSION['ftp'], $curdir);
		$output = rtrim($output, ',');
		$output.=']';
	}
	$output.= '}';
	return $output;
}

//===================================
//	Inputs:
//		none
//	Outpus:
//		JSON flag for bad function
function json_bad()
{
	return '{"sessionStatus":false}';
}






//========================================
//	Not to be used outside of this file
//========================================

//========================================
//	Inputs:
//		$file_path - absolute path to file
//	Outputs:
//		JSON format of file data
function json_file_info($file_name)
{
	//type check
	@ftp_chdir($_SESSION['ftp'], $file_name);
	if($file_name === basename(ftp_pwd($_SESSION['ftp']))) {
		$type = '"dir"';
		ftp_cdup($_SESSION['ftp']);
	} else {
		$type = strrpos($file_name, '.');
		$type = ($type == 0 ? 'false' : '"'.trim(substr($file_name, $type), '.').'"');	//implicit conversion
	}
	//date check
	$date = ftp_mdtm($_SESSION['ftp'],$file_name);
	$date = ($date===-1 ? 'false' : '"'.date('Y-m-d\TH:i:sP',$date).'"');
	//size check
	$size = ftp_size($_SESSION['ftp'],$file_name);
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