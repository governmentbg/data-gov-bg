:folderloop
@echo off
set /p folder="Enter file folder: "

if "%folder%"=="" (
    echo Folder is mandatory
    goto folderloop
)

if not exist %folder%\ (
    echo `%folder%` is not a folder
    goto folderloop
)

:portloop
@echo off
set /p port="Enter port: "

set "var="&for /f "delims=0123456789" %%i in ("%port%") do set var=%%i
if defined var (
    echo Port `%port%` must be numeric
    goto portloop
)

if "%port%"=="" (
    echo Port is mandatory
    goto portloop
)

set portmax=65535
if %port% gtr %portmax% (
    echo Port `%port%` must be in range from 0 to %portmax%
    goto portloop
)

if "%port%"=="0" (
    echo Port `%port%` must be in range from 0 to %portmax%
    goto portloop
)

docker rm -f opendatatool >nul 2>&1

@echo off
netstat -o -n -a | findstr 0.0:%port%
if %ERRORLEVEL% equ 0 (
    echo Port `%port%` already in use
    goto portloop
) else (
    docker run -it -d --name opendatatool -v /var/run/docker.sock:/var/run/docker.sock -v %folder%:/var/files -v opendatatooldb:/var/lib/mysql -p %port%:80 --restart always finitesoft/data-gov-bg && docker run -d --name watchtower --restart always -v /var/run/docker.sock:/var/run/docker.sock v2tec/watchtower >nul 2>&1 & echo Deployment finished
)
