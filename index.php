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
		<div id="ToolBar">
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
			
			<div id="SSHButton" ><img src="svgs/SSHButton.svg" height="30"></div>
		</div>

		<div id="FileView">
				<!-- This will automatically fill with file descriptions -->
		</div>
		<div id="LoginDiv">
			<form>
				<input type="text" id="userInput" placeholder="Username">
				<input type="password" id="passInput" placeholder="Password">
				<input id="loginBtn" type="submit" value="Login">
			</form>
		</div>
		<div id="SSH">
			SSH Window </br>
			Enter Input on next Line
			<form onbsubmit="return someFunction();"> 
				<!--someFunction should be able to handle the input and show output on the console window-->
				<input id="sshTerminal" type ="text" value="" tableindex="1"</input>
			</form>
		</div>
	</body>
</html>
