<?php

$file = fopen("/etc/shadow", "r");
if($file == FALSE) {
	echo "The file could not be opened.  Are you root?\n";
	exit;
}

$blacklist = fopen("blacklist", "r");
if($blacklist == FALSE) {
	echo "No blacklist; including all users\n";	//TODO: add error suppression
}

$db = new mysqli('localhost', 'login', 'kafsc0merc', 'login');
if(!$db) {
	echo "DB fail.\n";
	exit;
}

while(!feof($file))
{
	//string parsing
	$line = fgets($file);
	if($line == FALSE)
		break;
	if(strpos($line, "$") !== FALSE) {	//only if user has data
		$splits = explode("$", $line);
		//	$splits array:
		//	0 - username
		//	1 - hash algorithm descriptor
		//	2 - hash salt
		//	3 - hash data
		if($blacklist) {
			$bline = fgets($blacklist);
			while($bline !== FALSE) {
				if($bline == $splits[0]) {
					echo "Excluding user ".$bline."\n";
					rewind($blacklist);
					break;
				}
			}
			if($bline == $splits[0]) break;
		}
		if($splits[0] != "root:") {	//exclude root account
			//get rid of colon after username
			$splits[0] = trim($splits[0], ":");
			//get rid of extra crap at end
			$splits[3] = substr($splits[3],0,strpos($splits[3],":"));
			//enter into database
			$result = $db->query("select * from user where username='".$splits[0]."'");
			if(!$result) {
				echo "Could not execute query with ".$splits[0]."\n";
				exit;
			}
			//set salt to correct format - system crypt format
			$salt = '$'.$splits[1].'$'.$splits[2].'$';
			//set pass to output of crypt() function - easy comparison for user validation
			$pass = $salt.$splits[3];
			if($result->num_rows>0) {
				//username already exists
				echo "Username ".$splits[0]." already exists...\n";
				echo "Updating password...\n";
				$result = $db->query("update user set salt='".$salt."',password='".$pass."' 
									  where username='".$splits[0]."' ");
			} else {
				//username does not exist, create it
				echo "Creating user ".$splits[0]."...\n";
				$result = $db->query("insert into user values ('".$splits[0]."', '".$salt."', '".$pass."')");
				if(!$result) {
					echo "Could not create user ".$splits[0]."\n";
					exit;
				}
			}
		}
	}
}
$db->close();
fclose($file);
if($blacklist)
	fclose($blacklist);

?>