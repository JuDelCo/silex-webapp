@echo off

:confirmCode
choice /CS /C yn /N /M "WARNING - Do you really want to update vendor libraries [y/n] ? "
if %ERRORLEVEL% == 1 goto :updateCode
goto :cancelCode

:cancelCode
echo Update Cancelled
goto :endCode

:updateCode
cd "C:/WebServer/PHP/"
php composer.phar update --working-dir "C:/WebServer/HTDOCS/App/"
goto :endCode

:endCode
echo.
pause
