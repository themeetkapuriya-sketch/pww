@echo off
title Praful Welding Works ERP - System Launcher
color 0A

echo ============================================================
echo      PRAFUL WELDING WORKS ERP - SYSTEM LAUNCHER
echo ============================================================
echo.

:: 1. Auto-Start MySQL Database Server in Background (No extra terminal window)
netstat -o -n -a | findstr /R /C:":3306 " >nul 2>&1
if %errorlevel% equ 0 goto MYSQL_READY

echo [INFO] Starting MySQL Database Server in background...
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    start /b "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone >nul 2>&1
    powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
) else (
    net start mysql >nul 2>&1
    net start mysql80 >nul 2>&1
    net start mariadb >nul 2>&1
)

:MYSQL_READY
echo [OK] MySQL Database Server active.

:: 2. Refresh Application Caches
echo [INFO] Refreshing application caches...
cd /d "C:\laravel project\pww"
call php artisan config:clear >nul 2>&1
call php artisan view:clear >nul 2>&1
call php artisan route:clear >nul 2>&1

:: 3. Open Web Browser
echo [INFO] Opening ERP Portal in Default Web Browser...
start http://127.0.0.1:8000

:: 4. Start Laravel Server directly in THIS SINGLE terminal window
echo.
echo ============================================================
echo   SUCCESS! PWW ERP is now running on your computer.
echo   
echo   Login Portal: http://127.0.0.1:8000
echo   
echo   NOTE: Keep this single terminal window open while working.
echo ============================================================
echo.

php artisan serve --host=127.0.0.1 --port=8000

pause
