
[![UTD powered-by-electricity](http://ForTheBadge.com/images/badges/powered-by-electricity.svg)](https://github.com/gigamaster/xelfinder)
[![UTD](https://forthebadge.com/images/badges/built-with-love.svg)](https://github.com/gigamaster/xelfinder)

[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/2.0.0/active.svg)](https://github.com/xoopscube/xcl)
![License GPL](https://img.shields.io/badge/License-GPL-green)
![X-Updare Store](https://img.shields.io/badge/X--Update%20Store-Pending-red)

## ///// — CKEditor4 :: WYSIWYG editor + Web-based File manager

![alt text](https://repository-images.githubusercontent.com/469831419/6032bf18-5c1e-4f27-aa2f-2b8e60f4e5)

MODULE |  CKEditor
------------ | -------------
Description  | Battle-tested WYSIWYG editor with elFinder web-based file manager running on JavaScript + PHP.
Render Engine| Smarty v2 and XCube Layout
Version      | 4.19
Author       | @nao-pon Naoki Sawada
Maintainer   | Update @gigamaster Nuno Luciano (XCL7)
Copyright    | 2005-2022 Authors
License      | XCL module is distributed under a GPL 2.3 License.
License      | elFinder is distributed under a 3-Clause BSD License.


##### :computer: The Minimum Requirements



          Apache, Nginx, etc. PHP 7.2.x
          MySQL 5.6, MariaDB  InnoDB utf8 / utf8mb4
          XCL version 2.3.+



-----

## Features

The CKEditor4 module is released by default with the package bundle XCL 2.3.x and provides out of the box:  

Frontend template - Single File Component.    
Automatic change of HTML editor and BBCode editor depending on modules and user group permissions.    
Automatic ToolBar switching based on modules and user group permissions.   

- BBCode editor extends the CKEditor standard bbcode plugin    
- HTML editor with custom Toolbar for each user group    



## Control Panel Preferences

CKEditor's settings :   

- Toolbar UI Color
- "config.toolbar" for administrators (i.e webmaster)
- "config.toolbar" for special group (i.e. moderators)
- "config.toolbar" for registered users
- "config.toolbar" for guests (comments, forum)
- "config.extraPlugins"
- "config.customConfig"
- "config.enterMode"
- "config.shiftEnterMode"
- "config.allowedContent"
- "config.autoParagraph"

X-elFinder

- Set X-elFinder directory name for server browser.
- Upload Target of Drag & Drop (X-elFinder)
- Init image size of Drag & Drop(X-elFinder)


## Extra Plugins

[CKEditor-CodeMirror-Plugin](https://github.com/w8tcha/CKEditor-CodeMirror-Plugin) by @w8tcha  
Syntax Highlighting for the CKEditor (Source View and Source Dialog) with the CodeMirror Plugin

![Plugin CodeMirror](https://raw.githubusercontent.com/xoopscube/ckeditor4/b9c72dc150ecb490bf835222468a38d9d5249eb6/codemirror.png)

## Parameters
 
 If you're using the default  ``dhtmltarea `` you don't need to change the value.
 
 Otherwise you need to escape the value as follows:    

 ``<{ck4dhtmltarea value=VALUE|escape editor=html}>``
 
 ``<{ck4dhtmltarea value=VALUE|escape editor=bbcode}>``

| Option | Description |
| ------------- | ------------- |
| `editor` | The editor to use (html or bbcode) |
| `toolbar` | You can specify the toolbar to display in JSON format |



## Support

For topics, questions, and requests about CKEditor, please refer to [CKEditor](https://ckeditor.com/docs/index.html).    
For topics, questions, and requests about elFinder, please refer to [elFinder](https://github.com/Studio-42/elFinder). 

* Author : [nao-pon/xelfinder - GitHub](https://github.com/nao-pon/ckeditor4)    
* Maintainer : [gigamaster - XCL ^2.3.x](https://github.com/xoopscube/ckeditor4)
