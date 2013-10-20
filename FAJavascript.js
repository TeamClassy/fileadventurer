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
            
            displayFiles();
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
    function goToDir(newDir) {
        var curDir = $('#dirInput').attr('data-curDir');

        if(newDir !== curDir) {
            $.ajax({
                url: 'change_dir.php',
                type: 'POST',
                data: { dir: newDir },
                dataType: 'json',
                success: function (json) {
                    if(json.dirChange) {
                        $('#dirInput').attr('data-curDir', newDir).val(newDir);
                        dirInfo = json;
                        displayFiles();
                    } else {
                        //TODO: insert failure code
                    }
                },
                error: function(xhr, status) {
                    //TODO: insert error code
                }
            });
        }
    }


    /*
    ====================
    navToDir
        This function is called when a directory is double clicked on.
        It changes the view to that folder, then sends a request for PHP to change dirInfo to the target folder
        function navToDir()
    ====================
    */
    function navToDir(newDir) {
        var curDir = $('#dirInput').attr('data-curDir'),
            dirInfoTmp = dirInfo;

        $.ajax({
            url: 'change_dir.php',
            type: 'POST',
            data: { dir: newDir },
            dataType: 'json',
            success: function (json) {
                if(json.dirChange) {
                    $('#dirInput').attr('data-curDir', newDir).val(newDir);
                    dirInfo = json;
                    displayFiles();
                } else {
                    //TODO: insert failure code
                }
            },
            error: function(xhr, status) {
                //TODO: insert error code
            }
        });

        if(newDir === '..'){
            dirInfoTmp = dirInfoTmp.parentDir;
        } else {
            for (var i = dirInfo.files.length - 1; i >= 0; i--) {
                if(dirInfo.files[i].name === newDir) {
                    dirInfoTmp = dirInfoTmp.files[i].content;
                    break;
                }
            }
        }
        displayFiles(dirInfoTmp);
        
    }

    /*
    ====================
    displayFiles
        Creates objects from the current state of the dirInfo JSON object, or the passed JSON object if it exists
        Should put a ".." file in any directory that is not home so the parent can be accessed
    ====================
    */
    function displayFiles(dirInfoAlt) {
        var dirs = dirInfoAlt || dirInfo;
        for(var i in dirs.files)
        {
            if(dirs.files[i].type === 'folder'){
                $('#FileView').append('<div class="FolderGraphic" id="' + dirs.files[i].name + '"><img src="svgs/FolderGraphic.svg" ><div class="fileText">'+ dirs.files[i].name+ '</div></div>');
            }else{
                $('#FileView').append('<div class="FileGraphic" id="' + dirs.files[i].name + '"><img src="svgs/FileGraphic.svg" ><div class="fileText">'+ dirs.files[i].name+ '</div></div>');
            }
        }
    }
})();