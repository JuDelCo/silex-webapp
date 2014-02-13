@echo off
cd "C:/WebServer/PHP/"
@echo on

start cmd.exe /k "@echo off & title Silex Console & echo Usage: & echo     run           Shows help screen & echo     run command   Execute a server command & echo. & doskey run = php C:\WebServer\HTDOCS\App\bin\console $1"
