BomCleaner
==========

[Who am I? Check my blog...](http://emrahgunduz.com)

This is a single php file designed for removing the UTF 8 byte mark order from files on a hosted web site. Tool search the files and directory of files for BOM and tries to remove it. Checking starts from the script's location, and moves downward, but not upwards. So simply put it to the top folder of your site and run.

Script has the ability to detect if it is called from web or terminal and echoes related information for the medium. Color codes are used to define folders, files with bom and clean, cleaned files and errors. If you want this to work as good as possible, give the script write/read permissions.

Both web and terminal versions let you select file types for checking. Web version has php and html files defines as defaults but terminal has none, so you must push a Y button for each asked extension. It is possible to define custom extensions if you need to.

##Here are some screenshots. 
####When you are running from the linux terminal:
<img src="http://emrahgunduz.com/wp-content/uploads/2013/04/c.jpg">

####Startup screen when called from web:
<img src="http://emrahgunduz.com/wp-content/uploads/2013/04/a.jpg">

####After searching for files:
<img src="http://emrahgunduz.com/wp-content/uploads/2013/04/b.jpg">


The program is distributed in the hope that it will be useful, but without any warranty. The entire risk as to the quality and performance of the program is with you. In no event the author will be liable to you for damages, including any general, special, incidental or consequential damages arising out of the use or inability to use the program (including but not limited to loss of data or data being rendered inaccurate or losses sustained by you or third parties or a failure of the program to operate with any other programs), even if the author has been advised of the possibility of such damages.
