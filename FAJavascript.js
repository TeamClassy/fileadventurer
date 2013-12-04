// jshint jquery: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true, unused: vars, devel: true, browser: true, newcap: false

(function(){
/*
====================
jQuery plugin courtesy of StackOverflow user lonesomeday: http://stackoverflow.com/users/417562/lonesomeday
http://stackoverflow.com/a/7619765/1968930
====================
*/
/*$.fn.appendText = function(text) {
    'use strict';
    this.each(function() {
        var textNode = document.createTextNode(text);
        $(this).append(textNode);
    });
};*/


    'use strict';
    //This object should always contain information about the current directory
    var dirInfo = {dirName: '', files : []},
        highlighted = [],
        downx,
        downy,
        mousex,
        mousey,
        dragging = 0,
        dropping = 0;
        
    //This should prepare and initialize the window for proper opperation
    $(document).ready(function () {
        $('#goToDir').on('click', function (event) {
            goToDir($('#dirInput').val());
        });
        
        //shows working order of file dropdown buttons
        $('#FileDropdown').on('click',function (event) {
            $('#FileMenu').toggle();
        });
        $('#Delete').on('click',function (event) {
            var toDelete;
            
            $('#FileMenu').toggle();
            if(highlighted.length > 1) {
                if(confirm('Are you sure you want to permanently delete these ' + highlighted.length + ' items?')) {
                    multipleFileDelete();
                }
            } else if(highlighted.length > 0) {
                toDelete = highlighted[0];
                if(confirm('Are you sure you want to permanently delete ' + toDelete.name + (toDelete.type === 'dir' ? ' and all of its contents?' : '?'))) {
                    $.ajax({
                        url: 'rm_file.php',
                        type: 'POST',
                        data: { file: toDelete.path },
                        dataType: 'json',
                        success: function (json) {
                            if(json.rmFile) {
                                displayFiles(json);
                            } else {
                                alert('Error: ' + json.rmFail + ' was not deleted');
                            }
                        },
                        error: function (xhr, status) {
                            alert('Request Failed');
                            console.log(xhr);
                        }
                    });
                }
            }
        });
        $('#Download').on('click',function (event) {
            alert('Clicked download');
        });
        $('#Rename').on('click',function (event) {
            renameButton();
        });
        $('#Upload').on('click',function (event) {
            $('#UploadDialog').toggleClass('hidden');
        });

    
        $('#UploadButton').on('click',function (event) {
           $('#UploadDialog').toggleClass('hidden');
        });


        $('#loginBtn').on('click', function (eventObject) {
            var hostDefault = 'localhost',
                sshDefault = '7822',
                ftpDefault = '7821';
            eventObject.preventDefault();
            if($('#hostInput').val() !== '') {
                hostDefault = $('#hostInput').val();
            }
            if($('#ftpInput').val() !== '') {
                ftpDefault = $('#ftpInput').val();
            }
            $.ajax({
                url: 'login.php',
                type: 'POST',
                async: false,
                timeout: 30000,
                data: { user: $('#userInput').val(), pass: $('#passInput').val(), host : hostDefault, ssh_port: sshDefault, ftp_port: ftpDefault },
                dataType: 'json',
                success: function (json) {
                    if(json.sessionStatus) {
                        $('#LoginDiv').toggleClass('hidden');
                        displayFiles(json);
                        $('#ToolBar').toggleClass('hidden');
                        $('#LoginTitle').toggleClass('hidden');
            
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
                        $('#ToolBar').toggleClass('hidden');
                        $('#LoginTitle').toggleClass('hidden');
                        displayFiles({dirname:''});
                    } else {
                        alert('Logout Failed');
                    }
                },
                error: function (xhr, status) {
                    alert('Request Failed');
                    console.log(xhr);
                }
            });
        });
        
        $('#FileView').click(function (event) {
            for (var i = dirInfo.files.length - 1; i >= 0; i--) {
                dirInfo.files[i].el.removeClass('highlighted');
            }
        });

        $('#FileView').mousemove(function (event) {
            
            mousex = (event.clientX.toString())-30;
            mousey = (event.clientY.toString())-75;
            
            //Making the element dragging 
            if((dragging !== 0)&&(Math.abs(mousex - downx)) > 10){
                dragging.el.attr('class', 'dragging');
            }
            $('.dragging').css({ 'top': mousey+'px', 'left': mousex+'px'});
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
        //var curDir = dirInfo.dirname;

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
        renameFile
            This function is called when the user presses enter while editing a file name
            Should change the name of the file on the server and return the file's name to uneditable state
            TODO: Input checking
        ====================
        */
        function renameFile(){
            that.el.find('.fileText').attr('contenteditable','false');
            $.ajax({
                    url: 'mv_file.php',
                    type: 'POST',
                    data: {from: that.path, to: that.parent + ((that.parent[that.parent.length - 1] !== '/') ? '/' : '') + that.el.find('.fileText').html() },
                    dataType: 'json',
                    success: function (json) {
                    if(!json.mvFile) {
                       alert('Could not rename file.');
                       displayFiles(json);
                        }
                },
                    error: function (xhr, status) {
                        alert('error: ' + status);
                        console.log(xhr);
                    }
                });
        }
  
        /*
        ====================
        navToDir
            This function is called when a directory is double clicked on.
            It changes the view to that folder, then sends a request for PHP to change dirInfo to the target folder
        ====================
        */
        function navToDir() {
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
        that.path = (that.name !== '..') ? that.parent + '/' + that.name : that.parent.slice(0, (that.parent.lastIndexOf('/') || 1));
        that.path = that.path.replace('//','/');
        that.date = new Date(that.date);
        that.invis = (that.name[0] === '.');
        that.el = $('<div>', {
            'class': that.type === 'dir' ? 'folder' : 'file',
            id: that.name,
            html: '<img id="' + that.path + '" src="svgs/' + (that.type === 'dir' ? 'Folder' : 'File') + 'Graphic.svg" ><div class="fileText">'+ that.name+ '</div>'
        });
        if (that.name === '..') {
            that.content = dirInfo.parentDir;
        } else {
            that.el.click(function (event) {
                event.stopPropagation();
                if(!that.el.hasClass('highlighted')) {
                    highlighted.push(that);
                } else {
                    highlighted.splice($.inArray(that, highlighted), 1);
                }
                that.el.toggleClass('highlighted');
            });
        }
        
        if(that.type === 'dir') {
            that.el.dblclick(function (event) {
                event.stopPropagation();
                navToDir();
            });
        }
        
        that.el.mousedown(function (event) {
            dragging = that;
            downx = (event.clientX.toString())-30;
            downy = (event.clientY.toString())-75;
        });
       
        /*that.el.mouseover(function (event) {
        //  if(dragging !== that){
        //      dropping = that;
        //  }
            //console.log(that.path);
            console.log('Currently over ' + that.name);
        });

        that.el.mouseenter(function (event) {
            console.log('Currently over ' + that.name);
            if(dragging !== that){
                dropping = that;    
            }
        });*/

        that.el.mouseup(function (event) {
            
            $('.dragging').css({ 'display': 'none'});

                        

            dropping = $(document.elementFromPoint(event.clientX, event.clientY));
            //if(dragging !== that){
            //  dropping = that;
            //}
            //if(dragging !== that){
            //  dropping = that;    
            //}

            console.log(dropping.attr('id') + ' === ' + dragging.path);

            if((dragging !== 0)&&(dragging.path !== dropping.attr('id'))&&(dropping.parent().hasClass('folder'))){
                $.ajax({
                    url: 'mv_file.php',
                    type: 'POST',
                    data: {from: dragging.path, to: dropping.attr('id') + '/' + dragging.name },
                    dataType: 'json',
                    success: function (json) {
                        if(json.mvFile) {
                            displayFiles(json);
                        }
                    }
                });
            }

            $('.dragging').css({ 'display':'block'});
            
            if(dragging !== 0){
                dragging.el.removeClass('dragging');
                dragging.el.addClass('file');
                dragging = 0;
            }
            if(dropping !== 0){
                dropping = 0;
            }
        });
       
        that.el.find('.fileText').keydown(function (event){
           if(event.which===13) {
                event.preventDefault();
                renameFile();
           }
        });

         $('#FileView').append(that.el);
        return that;
    }
     /*
    =====================
    rmButton
        Changes the contenteditable attr to true of the div holding the file or folders name
        Should focus user on the selected file/folder's name and allow them to edit it.
        TODO: Add functionality for folders and allow the user to rename only one thing at a time
    =====================
    */
    function renameButton (){
        var toRename = highlighted[0];
        if(highlighted.length > 1){
            alert('Cannot rename more than one file or folder.');
        }else{
            if(toRename.name !== '..'){
                $('#FileMenu').toggle();
                toRename.el.find('.fileText').attr('contenteditable','true');
                toRename.el.find('.fileText').focus();
            }
        }
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
        highlighted.length = 0;
        for (var i = dirInfo.files.length - 1; i >= 0; i--) {
            dirInfo.files[i].el.remove();
        }
        dirInfo = dirs;
        if('parentDir' in dirs && dirs.parentDir.length !== 0) {
            dirs.files.unshift({type: 'dir', name: '..'});
        }
        for(var j in dirs.files)
        {
            dirs.files[j] = File(dirs.files[j]);
        }
    }

    /*
    ====================
    progressDialog
        creates a dialog which displays the progress of a task
        that = {
            dialogTitle : 'MyDialog',
            tasks: 3,                   //number of things that need doing
            finish: function(){}        //a function to run after the dialog closes
        }
    ====================
    */
    function progressDialog(that) {
        var tasks,
            dialog,
            progressBar,
            val = 0;

        that = that || {};
        tasks = that.tasks || 0;
        dialog = $('<div>', {
            id: 'ProgressDialog',
            html: '<div><h3>' + (that.dialogTitle || '') + '</h3><div id="progressMsg"></div><progress val="0" max="' + tasks * 10 + '"></progress></div>'
        });

        $('body').append(dialog);
        progressBar = dialog.find('progress');

        that.close = function () {
            dialog.remove();
            if('finish' in that) {
                that.finish();
            }
        };

        that.advance = function(num) {
            num = num || 1;
            $({value: val * 10}).animate({value: (val + num) * 10}, {
                duration: 500,
                step: function () {
                    progressBar.attr('value', this.value);
                }
            });
            val = val + num;
            if(val >= tasks) {
                setTimeout(that.close, 700);
            }
        };

        that.message = function (newmsg) {
            dialog.find('#progressMsg').html(newmsg);
        };

        return that;
    }

    function multipleFileDelete() {
        var count = highlighted.length - 1,
            deleteDlg = progressDialog({
                dialogTitle: 'Deleting Multiple Files',
                tasks: highlighted.length,
                finish: function () {
                    if(this.failedFiles.length) {
                        alert('The folowing files were not deleted: \n' + this.failedFiles.toString().replace(/,/g, '\n'));
                    }
                    displayFiles(this.dirinfo);
                },
                failedFiles: [],
                dirInfo: {}
            });

        function onSuccess (json) {
            deleteDlg.message('Deleting ' + highlighted[count].name);
            count--;
            deleteDlg.advance();
            if(json.rmFile) {
                deleteDlg.dirInfo = json;
            } else {
                deleteDlg.failedFiles.push(json.rmFail);
            }
        }

        function onError  (xhr, status) {
            alert('Request Failed');
            console.log(xhr);
        }
        deleteDlg.message('Deleting... ');
        for (var i = count; i >= 0; i--) {
            $.ajax({
                url: 'rm_file.php',
                type: 'POST',
                data: { file: highlighted[i].path },
                dataType: 'json',
                success: onSuccess,
                error: onError
            });
        }
    }
}) ();
