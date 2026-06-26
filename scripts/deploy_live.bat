@echo off
echo =======================================================
echo       MAGAIES - Deploy to elsajana.com Live Server     
echo =======================================================
echo.
echo Server: 147.182.214.194
echo Domain: http://elsajana.com
echo.

REM Ensure no-passphrase key exists
if not exist "ssh_key_nopass.pem" (
    copy /Y ssh_key.pem ssh_key_nopass.pem
    icacls "ssh_key_nopass.pem" /inheritance:r /grant:r "%USERNAME%:F" >nul 2>&1
    ssh-keygen -p -P "1q2w3e4r5t" -N "" -f "ssh_key_nopass.pem"
)

echo Step 1: Uploading files to server...
scp -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no -r C:\xampp\htdocs\magaees\*.html root@147.182.214.194:/var/www/magaees/
scp -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no -r C:\xampp\htdocs\magaees\*.php root@147.182.214.194:/var/www/magaees/
scp -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no -r C:\xampp\htdocs\magaees\css root@147.182.214.194:/var/www/magaees/
scp -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no -r C:\xampp\htdocs\magaees\js root@147.182.214.194:/var/www/magaees/
scp -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no -r C:\xampp\htdocs\magaees\api root@147.182.214.194:/var/www/magaees/

echo.
echo Step 2: Fixing permissions and restarting Nginx...
ssh -i "ssh_key_nopass.pem" -o StrictHostKeyChecking=no root@147.182.214.194 "chown -R www-data:www-data /var/www/magaees && chmod -R 755 /var/www/magaees && systemctl reload nginx && echo DONE"

echo.
echo =======================================================
echo   Deploy Complete! Your site is live at:
echo   http://elsajana.com
echo   http://www.elsajana.com
echo   http://147.182.214.194
echo =======================================================
