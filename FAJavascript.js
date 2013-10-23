/* jshint jquery: true, camelcase: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true, newcap: false */
(function () {
    "use strict";
    //This object should always contain information about the current directory
    var dirInfo = {dirName: '~', files : []},
        files = [];
     
    //This should prepare and initialize the window for proper opperation
    $(document).ready(function () {
            
            displayFiles({"sessionStatus":true,"dirName":"/home/connor/www","files":[{"type":"file","name":"index.php","date":"2013-10-22T00:09:32-04:00","size":"2164"}],"parentDir":[{"type":"file","name":".bash_history","date":"2013-10-22T15:05:45-04:00","size":"6999"},{"type":"file","name":".mysql_history","date":"2013-10-18T13:14:11-04:00","size":"2199"},{"type":"file","name":"dark_magic_user.php","date":"2013-10-22T15:05:38-04:00","size":"2184"},{"type":"file","name":".bashrc","date":"2013-09-22T16:53:24-04:00","size":"3391"},{"type":"file","name":".bash_logout","date":"2013-09-22T16:53:24-04:00","size":"220"},{"type":"dir","name":"www","date":"2013-10-21T22:45:40-04:00","size":"4096"},{"type":"file","name":".profile","date":"2013-09-22T16:53:24-04:00","size":"675"},{"type":"file","name":"setup_login.sql","date":"2013-09-24T14:42:38-04:00","size":"106"}]});
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
                        //TODO: insert failure code
                    }
                },
                error: function (xhr, status) {
                    //TODO: insert error code
                }
            });
            displayFiles({dirName: that.path, files: that.content});   
        }

        //public
        that.path = dirInfo.dirName + '/' + that.name;
        that.date = new Date(that.date);
        that.invis = (that.name[0] === '.');
        that.element = $('<div>', {
            'class': that.type === 'dir' ? 'folder' : 'file',
            id: that.name,
            html: '<img src="svgs/' + (that.type === 'dir' ? 'Folder' : 'File') + 'Graphic.svg" ><div class="fileText">'+ that.name+ '</div>'
        });
        that.element.click(function (event) {
            that.element.toggleClass('highlighted');
        });
        if(that.type === 'dir') {
            that.element.dblclick(function (event) {
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