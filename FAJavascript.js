/* jshint jquery: true, curly: true, bitwise: true, eqeqeq: true, immed: true, strict: true, newcap: false */

(function (){
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
        files = [],
        ssh,
        dragging,
        dropping,
		downx,
		downy, 
		mousex, 
		mousey;
		
    //This should prepare and initialize the window for proper opperation
    $(document).ready(function () {
        ssh = SSH();
        $('#goToDir').on('click', function (event) {
            goToDir($('#dirInput').val());
        });
        
        //shows working order of file dropdown buttons
        $('#FileDropdown').on('click',function (event) {
            $('#FileMenu').toggle();
        });
        $('#Delete').on('click',function (event) {
            alert('Clicked delete');
        });
        $('#Download').on('click',function (event) {
            alert('Clicked download');
        });
        $('#Rename').on('click',function (event) {
            rmButton();
        });
        $('#Upload').on('click',function (event) {
            alert('Clicked upload');
        });

	
	$('#UploadButton').on('click',function (event) {
	   $('#UploadDialog').toggleClass('hidden'); 
	});


        $('#loginBtn').on('click', function (eventObject) {
            var hostDefault = 'localhost',
                sshDefault = '7822',
                ftpDefault = '7821';
            eventObject.preventDefault();
            if($('#hostInput').val() !== "") {
                hostDefault = $('#hostInput').val();
            }
            if($('#sshInput').val() !== "") {
                sshDefault = $('#sshInput').val();
            }
            if($('#ftpInput').val() !== "") {
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
                        displayFiles({dirname:""});
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
			mousey = (event.clientY.toString())-50;
			//Making the element dragging 
			if((dragging !== 0)&&(Math.abs(mousex - downx)) > 10){
				dragging.el.attr('class', 'dragging');	
			}
			$('.dragging').css({ "top": mousey+'px', "left": mousex+'px'});
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
    SSH
        
    ====================
    */
    function SSH(obj) {
        var that = obj || {},
            cmdHist = [],
            cmdHistIndex = 0;

        function sendCmd(inputNode, input) {
            /*$.ajax({
                url: 'ssh.php',
                type: 'POST',
                data: { cmd: input },
                dataType: 'text',
                success: function (text) {

                },
                error: function (xhr, status) {
                    alert('Request Failed');
                    console.log(xhr);
                }
            });*/
            cmdHist.push(cmdHist.length - 1, 0, input);
            cmdHistIndex = cmdHist.length - 1;
            inputNode.remove();
            that.el.find('div').remove();
            that.el.append('<div></div>');
            $('#sshStatic').append('<span">' + input + '</span><br>');
        }

        /*
        ====================
        function courtesy of StackOverflow user Tim down: http://stackoverflow.com/users/96100/tim-down
        http://stackoverflow.com/a/3976125/1968930
        ====================
        */
        function getCaretPosition(editableDiv) {
            var caretPos = 0, sel, range;
            sel = window.getSelection();
            if (sel.rangeCount) {
                range = sel.getRangeAt(0);
                if (range.commonAncestorContainer.parentNode === that.el['0'] || range.commonAncestorContainer.parentNode === that.el.find('div')['0']) {
                    caretPos = range.endOffset;
                }
            }
            return caretPos;
        }

        function setInput(s, inputNode) {
            if(inputNode.nodeValue) {
                inputNode.nodeValue = s;
            } else if(inputNode.localName === 'div' && inputNode.id === '') {
                inputNode.innerText = s;
            } else {
                inputNode.lastChild.nodeValue = s;
            }
        }

        that.el = $("#SSH");
        $('#SSHButton').on('click', function (event) {
            that.el.toggle();
            if(!that.el.is(":hidden")) {
                $(that.el['0'].lastChild).focus();
            }
        });
        if(that.el['0'].firstChild.nodeName === '#text') {
            that.el['0'].firstChild.remove();
        }
        that.el.keydown(function (event) {
            var inputNode = window.getSelection().focusNode,
                input = inputNode.nodeValue || ((inputNode.localName === 'div' && inputNode.id === '') ? inputNode.innerText : inputNode.lastChild.nodeValue);
            if(that.el['0'].firstChild.nodeName === '#text') {
                that.el.appendText(that.el['0'].firstChild.nodeValue);
                that.el['0'].firstChild.remove();
            }
            if(event.which === 13) {
                sendCmd(inputNode, input);
            } else if(event.which === 8 && getCaretPosition(this) < 1) {
                event.preventDefault();
            } else if(event.which === 38) {
                event.preventDefault();
                setInput(cmdHist[cmdHistIndex], inputNode);
                cmdHistIndex = cmdHistIndex > 0 ? cmdHistIndex - 1 : 0;
            } else if(event.which === 40) {
                event.preventDefault();
                cmdHistIndex = cmdHistIndex < (cmdHist.length - 1) ? cmdHistIndex + 1 : cmdHist.length - 1;
                setInput(cmdHist[cmdHistIndex], inputNode);
            }
        });
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
        rmFile
            This function is called when the user presses enter while editing a file name
            Should change the name of the file on the server and return the file's name to uneditable state
            TODO: Input checking
        ====================
        */
        function rmFile(){
        that.el.find('.fileText').attr('contenteditable','false');
        /*$.ajax({
                url: 'rm_file.php',
                type: 'POST',
                data: { file: that.path },
                dataType: 'json',
                success: function (json) {
                    if(!json.rmFile) {
                        alert('Could not rename file.');
                        displayFiles(json);
                    }
                },
                error: function (xhr, status) {
                    alert('error: ' + status);
                    console.log(xhr);
                }
            });
        */
        }
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
        that.path = (that.name !== '..') ? that.parent + '/' + that.name : that.parent.slice(0, (that.parent.lastIndexOf('/') || 1));
        that.path = that.path.replace('//','/');
        that.date = new Date(that.date);
        that.invis = (that.name[0] === '.');
        that.el = $('<div>', {
            'class': that.type === 'dir' ? 'folder' : 'file',
            id: that.name,
            html: '<img src="svgs/' + (that.type === 'dir' ? 'Folder' : 'File') + 'Graphic.svg" ><div class="fileText" contenteditable="false">'+ that.name+ '</div>'
        });
        that.el.click(function (event) {
            event.stopPropagation();
            that.el.toggleClass('highlighted');
        });
        if(that.type === 'dir') {
            that.el.dblclick(function (event) {
                event.stopPropagation();
                navToDir();
            });
        }
        that.el.find('.fileText').keydown(function (event){
           if(event.which===13) {
                event.preventDefault();
                rmFile();
           }
       });
        
		that.el.mousedown(function (event) {
            dragging = that;
			downx = (event.clientX.toString())-30;
			downy = (event.clientY.toString())-30;
           // that.el.attr('class', 'dragging');
	    	//that.el.addClass('dragging');
        });

        that.el.mouseup(function (event) {
            if(!that.el.hasClass('dragging')){
				dropping = that;
                /*$.ajax({
                    url: 'mv_file.php',
                    type: 'POST',
                    data: {from: dragging, to: dropping },
                    dataType: 'json',
                    success: function (json) {
                        if(json.mvFile) {
                            displayFiles(json);
                        }
                    }
                });*/
            }
			if(dragging !== 0){
				dragging.el.attr('class', 'file');
				dragging = 0;
			}
        });

        if (that.name === '..') {
            that.content = dirInfo.parentDir;
        }
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
   function rmButton (){
        var filArray = document.getElementsByClassName('file highlighted');
        if(filArray.length > 1){
            alert('Cannot rename more than one file or folder.')
        else{
            var elem = document.getElementById(filArray[0].id);
            if(elem.lastChild.innerHtml !== '..'){
                $('#FileMenu').toggle();
                elem.lastChild.setAttribute('contenteditable','true');
                $(elem.lastChild).focus();
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
}) ();
