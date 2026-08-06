@echo off
title Magaiees ERP 2026 - Local Server (XAMPP Apache)

pushd "%CD%"
CD /D "%~dp0"
set "PATH=C:\xampp\apache\bin;C:\xampp\php;C:\Program Files\nodejs;%PATH%"
cd /d "%~dp0"

echo =========================================================
echo Starting Multi-Threaded Local Server for Magaiees ERP 2026
echo (Powered by XAMPP Apache - Port 80)
echo =========================================================
echo.

echo [1/3] Starting XAMPP Apache Web Server & MySQL Database...
if exist "C:\xampp\apache\bin\httpd.exe" (
    start "Apache" /B "C:\xampp\apache\bin\httpd.exe"
)
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    start "MySQL" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
)
timeout /t 2 /nobreak > nul

rem Database setup if needed
if exist "C:\xampp\php\php.exe" (
    if exist setup_db.php "C:\xampp\php\php.exe" setup_db.php
    if exist create_admin.php "C:\xampp\php\php.exe" create_admin.php
)

echo [2/3] XAMPP Apache multi-threaded server running on http://localhost/magaees/

rem 2. Start Node.js server if needed
echo [3/3] Starting Node.js server...
if exist "C:\Program Files\nodejs\node.exe" (
    start "Node-Server" /B "C:\Program Files\nodejs\node.exe" server.js
) else (
    start "Node-Server" /B node server.js
)

echo =========================================================
echo Server started successfully on Apache (Port 80)!
echo Opening browser: http://localhost/magaees/login.php
echo =========================================================

start http://localhost/magaees/login.php

echo.
echo Server running in background. Press any key to close this window...
pause
