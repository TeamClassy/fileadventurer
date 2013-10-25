<?php

$shadow = @fopen("/etc/shadow", "r");
if($shadow === FALSE)
	exit('Error opening shadow file.  Are you root?\n');
$passwd = @fopen("/etc/passwd", "r");
if($passwd === FALSE)
	exit('Error opening passwd file.\n');

if(is_readable('blacklist')) {
	$blacklist = fopen("blacklist", "r");
	if($blacklist === FALSE) {
		fclose($shadow);
		exit('Error opening blacklist.  Ceasing operation.\n');
	}
} else {
	$blacklist = FALSE;
	echo 'No blacklist. Including all users.\n';
}

$db = new mysqli('localhost', 'login', 'kafsc0merc', 'login');
if(!$db) {
	fclose($shadow);
	if($blacklist !== FALSE)
		fclose($blacklist);
	exit('Could not connect to MySQL database.\n');
}

while(!feof($shadow))
{
	//string parsing
	$line = fgets($shadow);
	if($line === FALSE)
		break;
	if(strpos($line, "$") !== FALSE) {	//only if user has data
		$shadow_data = explode("$", $line);
		//	$shadow_data array:
		//	0 - username
		//	1 - hash algorithm descriptor
		//	2 - hash salt
		//	3 - hash data
		if($shadow_data[0] != "root:") {	//exclude root account
			//check blacklist
			if($blacklist) {
				do {
					$bline = fgets($blacklist);
					if($bline === $shadow_data[0]) {
						echo "Excluding user ".$bline."\n";
						break;
					}
				} while($bline !== FALSE);
				rewind($blacklist);
				if($bline === $shadow_data[0]) continue;
			}
			//get rid of colon after username
			$shadow_data[0] = trim($shadow_data[0], ":");
			//get rid of extra crap at end
			$shadow_data[3] = substr($shadow_data[3],0,strpos($shadow_data[3],":"));
			//set salt to correct format - system crypt format
			$salt = '$'.$shadow_data[1].'$'.$shadow_data[2].'$';
			//set pass to output of crypt() function - easy comparison for user validation
			$pass = $salt.$shadow_data[3];
			//enter into database
			$result = $db->query("select * from user where username='".$shadow_data[0]."'");
			if(!$result) {
				echo "Could not execute query with ".$shadow_data[0]."\n";
				continue;
			}
			//get directory
			$directory = FALSE;
			do {
				$diectory = fgets($passwd);
				if($directory === $passwd_data[0]) {
					$dir_data = explode(":", $directory);
					$directory = $dir_data[5];
					break;
				}
			} while($directory !== FALSE);
			rewind($passwd);
			//username already exists
			if($result->num_rows>0) {
				echo "Username ".$shadow_data[0]." already exists. Updating password...\n";
				$result = $db->query("update user set 
									  salt='".$salt."',password='".$pass."',directory='".$directory."' 
									  where username='".$shadow_data[0]."' ");
				if(!$result)
					echo "Could not update user ".$shadow_data[0]."\n";
			} else {	//username does not exist, create it
				echo "Creating user ".$shadow_data[0]."...\n";
				$result = $db->query("insert into user values 
									('".$shadow_data[0]."', '".$salt."', '".$pass."', '".$directory."')");
				if(!$result)
					echo "Could not create user ".$shadow_data[0]."\n";
			}
		}
	}
}
$db->close();
fclose($shadow);
fclose($passwd);
if($blacklist)
	fclose($blacklist);

?>