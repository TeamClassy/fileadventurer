<!doctype html>
<html lang="en">
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script type="text/javascript" src="jquery.min.js"></script>
		<script type="text/javascript" src="FAJavascript.js"></script>
		
		<meta charset="utf-8">
		
		<title>File Adventurer</title>
		<meta name="Into the Adventure"
			content="Your solution for graphically exploring the files on your server."
			author="Lukas Rickard, edited by Joseph Grant and Nicholas Howes">
	</head>
	
	<body>
		<div id="ToolBar" class="hidden">
			<div id="FileDropdown">
				<img src="svgs/FileDropdown.svg" height="30">
			</div>
			
			<div id="DirectoryBox">
					Dir:
					<input type="text" id="dirInput" value="/" placeholder="Enter Directory Here">
					<input id="goToDir" type="button" value=">">
			</div>
			
			<div id="DownloadButton"><img src="svgs/DownloadButton.svg" height="30"></div>
			
			<div id="UploadButton"><img src="svgs/UploadButton.svg" height="30"></div>
			
			<div id="LogOutButton"><img src="svgs/LogOutButton.svg" height="30"></div>
			
			<div id="SSHButton"><img src="svgs/SSHButton.svg" height="30"></div>

			<div id="UploadDialog"><img src="svgs/UploadDialog.svg" height="40"></div>
							
		</div>

		<div id="LoginTitle"><img src="svgs/LoginBar.svg" height="40"></div>

		<div id="FileMenu"><img src="svgs/FileDropdownDropped.svg" height="120">
			<div id='Delete'></div>
			<div id='Download'></div>
			<div id='Rename'></div>
			<div id='Upload'></div>
		</div>
		
		<div id="FileView">
				<!-- This will automatically fill with file descriptions -->
		</div>
		<div id="LoginDiv">
			<form>
				<input type="text" id="userInput" placeholder="Username">
				<input type="password" id="passInput" placeholder="Password">
				<input type="text" id="hostInput" placeholder="Hostname">
				<input type="text" id="sshInput" placeholder="SSH Port">
				<input type="text" id="ftpInput" placeholder="FTP Port">
				<input id="loginBtn" type="submit" value="Login">
			</form>
		</div>
		<div id="SSH" contenteditable="true">
			<span id="sshStatic" contenteditable="false"><span class="sshUser">user@server</span><span>:</span><span class="sshPath">~</span><span>$</span></span> 
		</div>
	</body>
</html>
