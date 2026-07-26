@echo off
title Install AutoStart - Magaiees ERP
echo Setting up automatic startup for Magaiees ERP Server...

set "STARTUP_FOLDER=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "TARGET_VBS=c:\xampp\htdocs\ELSAJANA2\magaies\start_background_server.vbs"
set "SHORTCUT_PATH=%STARTUP_FOLDER%\MagaieesERP_AutoStart.lnk"

powershell -Command "$s=(New-Object -COM WScript.Shell).CreateShortcut('%SHORTCUT_PATH%'); $s.TargetPath='%TARGET_VBS%'; $s.WorkingDirectory='c:\xampp\htdocs\ELSAJANA2\magaies'; $s.Save()"

echo.
echo =========================================================
echo ✅ SUCCESS: Magaiees ERP Server configured to start automatically on Windows boot!
echo Shortcut created at: %SHORTCUT_PATH%
echo =========================================================
pause
