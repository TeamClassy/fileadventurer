/* jshint jquery: true, camelcase: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true, newcap: false */
(function () {
    "use strict";
    //This object should always contain information about the current directory
    var dirInfo = {dirName: '', files : []},
        files = [];
     
    //This should prepare and initialize the window for proper opperation
    $(document).ready(function () {
            
        //Brings up an SSH window for users to enter SSH commands with
        $('#SSHButton').on('click', function (event) {
            $('#SSH').toggle();
        });
        $('#goToDir').on('click', function (event) {
            goToDir($('#dirInput').val());
        });
        $('#loginBtn').on('click', function (eventObject) {
            eventObject.preventDefault();
            $.ajax({
                url: 'login.php',
                type: 'POST',
                data: { user: $('#userInput').val(), pass: $('#passInput').val() },
                dataType: 'json',
                success: function (json) {
                    if(json.sessionStatus) {
                        $('#LoginDiv').toggleClass('hidden');
                        displayFiles(json);
                    } else {
                        alert('Login Failed');
                    }
                },
                error: function (xhr, status) {
                    alert('Request Failed');
                    console.log(xhr);
                }
            });
        });
        $('#LogOutButton').on('click', function (eventObject) {
            eventObject.preventDefault();
            $.ajax({
                url: 'logout.php',
                type: 'POST',
                dataType:'json',
                success: function (json) {
                    if(!json.sessionStatus) {
                        $('#LoginDiv').toggleClass('hidden');
                    } else {
                        alert('Logout Failed')
                    }
                },
                error: function (xhr, status) {
                    alert('Request Failed');
                    console.log(xhr);
                }
            })
        });
        $('#FileView').click(function (event) {
            for (var i = dirInfo.files.length - 1; i >= 0; i--) {
                dirInfo.files[i].element.removeClass('highlighted');
            }
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
        var curDir = dirInfo.dirname;

        //if(newDir !== curDir) {
            $.ajax({
                url: 'change_dir.php',
                type: 'POST',
                data: { dir: newDir },
                dataType: 'json',
                success: function (json) {
                    if(json.dirChange) {
                        displayFiles(json);
                    } else {
                        alert(newDir + ' does not exist');
                    }
                },
                error: function(xhr, status) {
                    alert('error: ' + status);
                    console.log(xhr);
                }
            });
        //}
    }


    /*
    ====================
    File
        
    ====================
    */
    function File(that) {

        //private
        /*
        ====================
        navToDir
            This function is called when a directory is double clicked on.
            It changes the view to that folder, then sends a request for PHP to change dirInfo to the target folder
        ====================
        */
        function navToDir() {
            var curDir = dirInfo.dirName;
            $.ajax({
                url: 'change_dir.php',
                type: 'POST',
                data: { dir: that.path },
                dataType: 'json',
                success: function (json) {
                    if(json.dirChange) {
                        displayFiles(json);
                    } else {
                        alert(that.path + 'does not exist');
                        goToDir(that.parent);
                    }
                },
                error: function (xhr, status) {
                    alert('error: ' + status);
                    console.log(xhr);
                }
            });
            displayFiles({dirName: that.path, files: that.content});   
        }

        //public
        that.parent = dirInfo.dirName;
        that.path = (that.name !== '..') ? that.parent + '/' + that.name : that.parent.slice(0, that.parent.lastIndexOf('/') + 1);
        that.date = new Date(that.date);
        that.invis = (that.name[0] === '.');
        that.element = $('<div>', {
            'class': that.type === 'dir' ? 'folder' : 'file',
            id: that.name,
            html: '<img src="svgs/' + (that.type === 'dir' ? 'Folder' : 'File') + 'Graphic.svg" ><div class="fileText">'+ that.name+ '</div>'
        });
        that.element.click(function (event) {
            event.stopPropagation();
            that.element.toggleClass('highlighted');
        });
        if(that.type === 'dir') {
            that.element.dblclick(function (event) {
                event.stopPropagation();
                navToDir();
            });
        }
        

        if (that.name === '..') {
            that.content = dirInfo.parentDir;
        }
        $('#FileView').append(that.element);
        return that;
    }

    /*
    ====================
    displayFiles
        Creates objects from the the passed JSON object
        Should put a ".." file in any directory that is not home so the parent can be accessed
    ====================
    */
    function displayFiles(json) {
        var dirs = json || dirInfo;
        $('#dirInput').attr('data-curDir', dirs.dirName + '/').val(dirs.dirName + '/');
        for (var i = dirInfo.files.length - 1; i >= 0; i--) {
            dirInfo.files[i].element.remove();
        }
        dirInfo = dirs;
        if('parentDir' in dirs) {
            dirs.files.unshift({type: 'dir', name: '..'});
        }
        for(var j in dirs.files)
        {
            dirs.files[j] = File(dirs.files[j]);
        }
    }
})();
