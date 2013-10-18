/* jshint jquery: true, camelcase: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true */
(function () {
    "use strict";
    //This object should always contain information about the current directory
    var dirInfo = {
        "dirName" : "~/lizards/",
        
        "files" :[
        {"name":"..", "type":"folder"},
        {"name":"iguana1.png", "type":"file"},
        {"name":"iguana2.png", "type":"file"},
        {"name":"geco.png", "type":"file"}
        ]
    };
     
    //This should prepare and initialize the window for proper opperation
    $(document).ready(function () {
            
            display();
            //Brings up an SSH window for users to enter SSH commands with
            $('#SSHButton').on('click', function (event) {
                $('#SSH').toggle();
            });
    }); 

    

    /*
    ====================
    goToDir
        This function should look at the directory entered into the directory input box
        If that input is different from the current directory and a valid directory
        Then it should send a request for PHP to change dirInfo to match that file
        Else it shoulde change the input contents to the current dirName
        function goToDir()
    ====================
    */
    function goToDir() {

    }


    /*
    ====================
    display
        Creates objects from the current state of the dirInfo JSON object
        Should put a ".." file in any directory that is not home so the parent can be accessed
    ====================
    */
    function display() {
        for(var i in dirInfo.files)
        {
            if(dirInfo.files[i].type === 'folder'){
                $('#FileView').append('<div class="FolderGraphic" id="' + dirInfo.files[i].name + '"><img src="svgs/FolderGraphic.svg" ><div class="fileText">'+ dirInfo.files[i].name+ '</div></div>');
            }else{
                $('#FileView').append('<div class="FileGraphic" id="' +dirInfo.files[i].name + '"><img src="svgs/FileGraphic.svg" ><div class="fileText">'+ dirInfo.files[i].name+ '</div></div>');
            }
        }
    }
})();