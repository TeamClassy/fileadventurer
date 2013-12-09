// jshint jquery: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true, unused: vars, devel: true, browser: true, newcap: false

(function () {
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
            event.stopPropagation();
            $('#FileMenu').toggle();
        });

        $('#FileMenu').on('click',function (event) {
            $('#UploadDialog').hide();
            $('#FileMenu').hide();
        });

        $('#Delete').on('click',function (event) {
            var toDelete;
            
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
            Download();
        });
        $('#Rename').on('click',function (event) {
            renameButton();
        });
        $('#Upload').on('click',function (event) {
            event.stopPropagation();
            $('#FileMenu').hide();
            $('#UploadDialog').toggle();
        });

    
        $('#UploadButton').on('click',function (event) {
            event.stopPropagation();
            $('#FileMenu').hide();
            $('#UploadDialog').toggle();
        });

        $('#DownloadButton').on('click',function (event) {
            Download();
        });

        $('#UploadDialog :button').click(function(){
            var formData = new FormData($('#UploadDialog')[0]),
                upProgDlg;
            $('#UploadDialog').hide();
            $.ajax({
                url: 'upload.php',  //Server script to process data
                type: 'POST',
                xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    if(myXhr.upload){ // Check if upload property exists
                        myXhr.upload.addEventListener('progress',function (e){
                            if(e.lengthComputable){
                                upProgDlg.progressSet(e.loaded - 1);
                            }
                        }, false); // For handling the progress of the upload
                    }
                    return myXhr;
                },
                //Ajax events
                beforeSend: function (e) {
                    upProgDlg = progressDialog({
                        dialogTitle: 'Uploading...',
                        tasks: e.total
                    });
                },
                dataType: 'json',
                success: function (json) {
                    upProgDlg.close();
                    if(json.uploadSuccess) {
                        displayFiles(json);
                    } else {
                        alert('Error: Upload failed');
                    }
                },
                error: function (xhr, status) {
                    alert('Request Failed');
                    console.log(xhr);
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
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
            $('#loginBtn').hide();
            $('#loginGif').show();
            $.ajax({
                url: 'login.php',
                type: 'POST',
                timeout: 30000,
                data: { user: $('#userInput').val(), pass: $('#passInput').val(), host : hostDefault, ssh_port: sshDefault, ftp_port: ftpDefault },
                dataType: 'json',
                success: function (json) {
                    $('#loginBtn').show();
                    $('#loginGif').hide();
                    if(json.sessionStatus) {
                        $('#LoginDiv').hide();
                        displayFiles(json);
                        $('#ToolBar').show();
                        $('#LoginTitle').hide();
            
                    } else {
                        alert('Login Failed');
                    }
                },
                error: function (xhr, status) {
                    $('#LoginBtn').show();
                    $('#LoginGif').hide();
                    alert('Request Failed');
                    console.log(xhr);
                }
            });
        });

        $('#LogOutButton').on('click', function (eventObject) {
            $('#UploadDialog').hide();
            $('#FileMenu').hide();
            eventObject.preventDefault();
            $.ajax({
                url: 'logout.php',
                type: 'POST',
                dataType:'json',
                success: function (json) {
                    if(!json.sessionStatus) {
                        $('#LoginDiv').show();
                        $('#ToolBar').hide();
                        $('#LoginTitle').show();
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
            $('#FileMenu').hide();
            $('#UploadDialog').hide();
            for (var i = dirInfo.files.length - 1; i >= 0; i--) {
                dirInfo.files[i].el.removeClass('highlighted');
            }
            highlighted.length = 0;
        });

        $('#ToolBar').click(function (event) {
            $('#FileMenu').hide();
            $('#UploadDialog').hide();
        });

        $('#FileView').mousemove(function (event) {
            
            mousex = (event.clientX.toString())-30;
            mousey = (event.clientY.toString())-75;
            
            //Making the element dragging 
            if((dragging !== 0)&&(Math.abs(mousex - downx)) > 10) {
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
                error: function (xhr, status) {
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
        function renameFile() {
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

        function highlight(i) {
            if(i === undefined) {
                if(!that.el.hasClass('highlighted')) {
                    highlighted.unshift(that);
                } else {
                    highlighted.splice($.inArray(that, highlighted), 1);
                }
                that.el.toggleClass('highlighted');
            } else {
               if(!dirInfo.files[i].el.hasClass('highlighted')) {
                    highlighted.push(dirInfo.files[i]);
                    dirInfo.files[i].el.addClass('highlighted');
                }
            }
            
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
            html: '<div id="' + that.path + '" class="' + (that.type === 'dir' ? 'Folder' : 'File') + '" ><div class="fileText">'+ that.name + '</div></div>'
        });
        if (that.name === '..') {
            that.content = dirInfo.parentDir;
        } else {
            that.el.click(function (event) {
                var i;
                event.stopPropagation();
                if (event.ctrlKey) {
                    highlight();
                } else if (event.shiftKey) {
                    for (i = dirInfo.files.length - 1; i > 0; i--) {
                        dirInfo.files[i].el.removeClass('highlighted');
                    }
                    highlighted.length = 1;
                    i = $.inArray(highlighted[0], dirInfo.files);
                    that.index = $.inArray(that, dirInfo.files);
                    if (i > that.index) {
                        for(; i > that.index - 1; i--) {
                            highlight(i);
                        }
                    } else if (i < that.index) {
                        for(; i < that.index + 1; i++) {
                            highlight(i);
                        }
                    }
                    highlighted.shift();
                } else {
                    for (i = dirInfo.files.length - 1; i >= 0; i--) {
                        dirInfo.files[i].el.removeClass('highlighted');
                        highlighted.length = 0;
                    }
                    highlight();
                }
            });
        }
        
        if(that.type === 'dir') {
            that.el.dblclick(function (event) {
                event.stopPropagation();
                navToDir();
            });
        } else {
          that.el.dblclick(function (event) {
            viewFile(that.path);
            });
        }
        
        that.el.mousedown(function (event) {
            dragging = that;
            downx = (event.clientX.toString())-30;
            downy = (event.clientY.toString())-75;
        });
       
        /*that.el.mouseover(function (event) {
        //  if(dragging !== that) {
        //      dropping = that;
        //  }
            //console.log(that.path);
            console.log('Currently over ' + that.name);
        });

        that.el.mouseenter(function (event) {
            console.log('Currently over ' + that.name);
            if(dragging !== that) {
                dropping = that;    
            }
        });*/

        that.el.mouseup(function (event) {
            
            $('.dragging').css({ 'display': 'none'});

                        

            dropping = $(document.elementFromPoint(event.clientX, event.clientY));
            //if(dragging !== that) {
            //  dropping = that;
            //}
            //if(dragging !== that) {
            //  dropping = that;    
            //}

            console.log(dropping.attr('id') + ' === ' + dragging.path);

            if((dragging !== 0)&&(dragging.path !== dropping.attr('id'))&&(dropping.parent().hasClass('folder'))) {
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
            
            if(dragging !== 0) {
                dragging.el.removeClass('dragging');
                dragging.el.addClass('file');
                dragging = 0;
            }
            if(dropping !== 0) {
                dropping = 0;
            }
        });
       
        that.el.find('.fileText').keydown(function (event) {
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
    function renameButton () {
        var toRename = highlighted[0];
        if(highlighted.length > 1) {
            alert('Cannot rename more than one file or folder.');
        }else{
            if(toRename.name !== '..') {
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
        $('#dirInput').attr('data-curDir', dirs.dirName).val(dirs.dirName);
        highlighted.length = 0;
        if('files' in dirInfo) {
            for (var i = dirInfo.files.length - 1; i >= 0; i--) {
                dirInfo.files[i].el.remove();
            }
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
            finish: function () {}        //a function to run after the dialog closes
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

        that.progressSet = function (progress) {
            $({value: val * 10}).animate({value: progress * 10}, {
                duration: 500,
                step: function () {
                    progressBar.attr('value', this.value);
                }
            });
            val = progress;
            if(val >= tasks) {
                setTimeout(that.close, 700);
            }
        };

        that.advance = function (num) {
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
                    displayFiles(this.dirInfo);
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

    function Download(path) {
        var frames = [],
            rand = Math.floor(Math.random() * 1000);

        function removeFrames() {
            for (var i = frames.length - 1; i >= 0; i--) {
                frames[i].remove();
            }
        }
        //removeFrames();
        if(!path && highlighted.length > 1) {
            for (var i = highlighted.length - 1; i >= 0; i--) {
                frames.push($('<iframe class="download-frame" name="frame' + (i * rand) + '"></iframe>').appendTo('body'));
                window.open('download.php?file=' + encodeURIComponent(highlighted[i].path), 'frame' + (i * rand));
            }
            setTimeout(removeFrames, 30000);
        } else if (path || highlighted.length) {
            path = path || highlighted[0].path;
            window.open('download.php?file=' + encodeURIComponent(path), 'frame');
        }
    }
    
    function viewFile(path) {
        var frames = [];

        /* (function removeFrames() {
            for (var i = frames.length - 1; i >= 0; i--) {
                frames[i].remove();
            }
        }
        removeFrames();
        if(!path && highlighted.length > 1) {
            for (var i = highlighted.length - 1; i >= 0; i--) {
                frames.push($('<iframe class="view-frame" name="view-frame' + i + '"></iframe>').appendTo('body'));
                window.open('download.php?file=' + encodeURIComponent(highlighted[i].path), 'view-frame' + i);
            }
            setTimeout(removeFrames, 30000);
        } else */ if (path || highlighted.length) {
            path = path || highlighted[0].path;
            $('<div>', { id: 'ProgressDialog'}).appendTo('body').click(function (event) {
                frames.remove();
                $(this).remove();
            });
            frames = $('<iframe class="view-frame" name="view-frame"></iframe>').load(function (event) {
                var frameDocument = this.contentWindow? this.contentWindow.document : this.contentDocument.defaultView.document,
                    frameHeight,
                    frameWidth;
                if($(frameDocument.body.children[0]).is('img')) {
                    frameHeight = $(frameDocument.body.children[0]).height();
                    frameWidth = $(frameDocument.body.children[0]).width();
                } else if ($(frameDocument.children[0]).is('svg')) {
                    frameHeight = $(frameDocument.children[0]).height.baseVal.value;
                    frameWidth = $(frameDocument.children[0]).width.baseVal.value;
                } else /*if($(frameDocument.body.children[0]).is('pre'))*/ {
                    frameHeight = $(frameDocument.body.children[0]).height;
                    frameWidth = 500;
                }
                $(this).animate({'height': frameHeight + 'px', 'width': frameWidth + 'px', 'margin': frameHeight/-2 + 'px 0 0 ' + frameWidth/-2 + 'px'});
                
            }).appendTo('body');
            window.open('view_file.php?file=' + encodeURIComponent(path), 'view-frame');
        }
    }

}) ();
