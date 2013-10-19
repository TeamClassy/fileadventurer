<!doctype html>
<html lang="en">
<html>
	<head>
		<script type="text/javascript" src="jquery.min.js"></script>
		<script type="text/javascript" src="FAJavascript.js"></script>
		
		<meta charset="utf-8">
		
		<title>File Adventurer</title>
		<meta name="Into the Adventure"
			content="Your solution for graphically exploring the files on your server."
			author="Lukas Rickard, edited by Joseph Grant and Nicholas Howes">
			
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	
	<body>
		<div id="ToolBar">
			<div id="FileDropdown">
				<img src="svgs/FileDropdown.svg" height="30">
			</div>
			
			<div id="DirectoryBox">
					Dir:
					<input type="text" id="dirInput" value="~/" placeholder="Enter Directory Here" data-curDir="~/">
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

		<div id="SSH">
			SSH window: <br />
  			<textarea cols="80" name="SSH Input">%</textarea>
		</div>
	</body>
</html>
