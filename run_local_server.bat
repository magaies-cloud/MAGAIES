@echo off
title Magaiees ERP 2026 - Local Server

pushd "%CD%"
CD /D "%~dp0"
set "PATH=C:\xampp\php;C:\Program Files\nodejs;%PATH%"
cd /d "%~dp0"

echo =========================================================
echo Starting Local Server for Magaiees ERP 2026
echo =========================================================
echo.

echo [0/3] Closing hanging processes...
taskkill /F /IM node.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1
timeout /t 1 /nobreak > nul

rem 0. Start MySQL automatically
echo [1/3] Starting XAMPP MySQL Database...
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    start "MySQL" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
)
timeout /t 1 /nobreak > nul

rem Database setup if needed
if exist "C:\xampp\php\php.exe" (
    if exist setup_db.php "C:\xampp\php\php.exe" setup_db.php
    if exist create_admin.php "C:\xampp\php\php.exe" create_admin.php
)

rem 1. Start PHP server
echo [2/3] Starting PHP server on 127.0.0.1:8080...
if exist "C:\xampp\php\php.exe" (
    start "PHP-8080" /D "%~dp0" "C:\xampp\php\php.exe" -S 127.0.0.1:8080 router.php
) else (
    start "PHP-8080" /D "%~dp0" php -S 127.0.0.1:8080 router.php
)

timeout /t 1 /nobreak > nul

rem 2. Start Node.js server
echo [3/3] Starting Node.js server on 3000...

echo =========================================================
echo Server started successfully!
echo Opening browser...
echo =========================================================

start http://127.0.0.1:8080/login.php

if exist "C:\Program Files\nodejs\node.exe" (
    "C:\Program Files\nodejs\node.exe" server.js
) else (
    node server.js
)

echo.
echo Server stopped. Press any key to close...
pause
