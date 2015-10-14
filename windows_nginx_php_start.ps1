# NGINX + PHP-CGI Helperscript 
# It runs invisible in backgroud or verbose in a powershell console window
# Note: you need to uncomment the php_cgi block in nginx.conf
# Requirements: Powershell Version 4.x
# 5/24.2015 by Davez

# WARNING: You need to enable Powershell scripts before you can use this script for example: 
# 1) Open a Powershell Window in Admin mode
# 2) run this command: Set-ExecutionPolicy RemoteSigned 

# Settings - change it to your needs
$php_path = 'C:\nginx\1.9.2\php\'
$nginx_path = 'C:\nginx\1.9.2\'

# Clear the console screen and start
Clear-Host;
Write-Host 'Starting NGINX + PHP FCGI'

# Get the script folder position
function Get-CurrentDirectory {
    Split-Path -Parent $PSCommandPath
}

# Script position in folder
Write-Host 'Run at location:' (Get-CurrentDirectory)

# Stopping running instances of nginx gracefully
$stop_proc = 'nginx.exe'
$stop_proc_args = '-s stop'

# Putting php-cgi launch params together
$cgi_path = $php_path;
$cgi_proc = 'php-cgi'
$cgi_proc_args = ' -b 127.0.0.1:9000 -c ' + $cgi_path + 'php.ini'
$cgi_command = $cgi_path + $cgi_proc

# printing the params to console
Write-Host 'CGI-Command: ' $cgi_command
Write-Host 'CGI-Proc-Args: ' $cgi_proc_args

# ok, now lets start the php-cgi process in invisible mode
$Running = Get-Process $cgi_proc -ErrorAction SilentlyContinue
	if (!$Running) { Start-Process -WindowStyle hidden $cgi_command $cgi_proc_args -passthru }
         Start-Process $stop_proc $stop_proc_args

# now its time for nginx to start 
$process = 'nginx.exe' 
$arguments = ' -c conf\nginx.conf'

# if everything is ok, print process id 
$app = Start-Process $process $arguments -passthru
Write-Host $app.Id

# 1) Open your favorite editor 
# 2) Create a php test file for example test.php
# 2) Put this single line in test.php  <?Php phpinfo(); ?> and save it

# Now you can access the NGINX+PHP configuration with your webbrowser at: http://localhost/test.php

# Script end.