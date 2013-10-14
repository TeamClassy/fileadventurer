var dirInfo ={
	"dirName" : "~/ladybutts/bigLadyButts/",
	
	"files" :[
	{"name":"..", "type":"folder"},
	{"name":"iguana1.png", "type":"file"},
	{"name":"iguana2.png", "type":"file"},
	{"name":"geco.png", "type":"file"}
	]
}
 
 $(document).ready(function(){
		//This Bringing up the SSH Window IF you click it
		$("#SSH").toggle();
		$("#SSHButton").click(function(){
			 $("#SSH").toggle();
		});
		display();
}); 

function goToDir()
{
	document.write("Imagine it going to the directory");
}

function display()
{
	for(i in dirInfo.files)
	{
		if(dirInfo.files[i].type == "folder"){
			$("#FileView").append('<div class="FolderGraphic" id="' + dirInfo.files[i].name + '"><img src="svgs/FolderGraphic.svg" >'+ dirInfo.files[i].name+ '</div>');
		}
		else{
			$("#FileView").append('<div class="FileGraphic" id="' +dirInfo.files[i].name + '"><img src="svgs/FileGraphic.svg" >'+ dirInfo.files[i].name+ '</div>');
		}
	}
}
