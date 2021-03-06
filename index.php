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
		<div id="ToolBar" style="display:none;">
			<div id="FileDropdown">
				<img src="svgs/FileDropdown.svg" height="30">
			</div>
			
			<div id="DirectoryBox">
					Dir:
					<input type="text" id="dirInput" value="/" placeholder="Enter Directory Here">
					<input id="goToDir" type="button" value=">">
			</div>
			
			<div class="DownloadButton"><img src="svgs/DownloadButton.svg" height="30"></div>
			
			<div class="UploadButton"><img src="svgs/UploadButton.svg" height="30"></div>
			
			<div class="LogOutButton"><img src="svgs/LogOutButton.svg" height="30"></div>
						
		</div>
		<form enctype="multipart/form-data" id="UploadDialog" style="display:none;">
			<img src="svgs/UploadDialog.svg" height="40">
		    <input name="file" type="file" />
		    <input type="button" value="Upload" />
		</form>
	
		<div id="LoginTitle"><img src="svgs/LoginBar.svg" height="40"></div>

		<div class="FileMenu" style ="display:none;"><img src="svgs/FileDropdownDropped.svg" height="120">
			<div class="Delete"></div>
			<div class="DownloadButton"></div>
			<div class="Rename"></div>
			<div class="UploadButton"></div>
		</div>
		
		<div id="FileView">
		</div>
		<div id="LoginDiv">
			<form>
				<input type="text" id="userInput" placeholder="Username">
				<input type="password" id="passInput" placeholder="Password">
				<input type="text" id="hostInput" placeholder="Hostname">
				<input type="text" id="ftpInput" placeholder="FTP Port">
				<input id="loginBtn" type="submit" value="Login">
				<img id="loginGif" style="display:none;" src="ajax-loader.gif">
			</form>
		</div>
		<iframe class="download-frame" name="frame"></iframe>
	</body>
</html>
