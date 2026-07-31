@echo off
title Praful Welding Works ERP Launcher
echo ============================================================
echo      Praful Welding Works ERP - Starting System...
echo ============================================================
echo.

:: 1. Verify PHP availability
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not found in system PATH.
    echo Please make sure PHP or XAMPP is installed.
    pause
    exit /b
)

:: 2. Check and Auto-Start MySQL Database Server if not running
netstat -o -n -a | findstr /R /C:":3306 " >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] MySQL is not running. Attempting to start MySQL Server...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start /b "MySQL_XAMPP" "C:\xampp\mysql\bin\mysqld.exe" --already-detached >nul 2>&1
        timeout /t 3 /nobreak >nul
    ) else (
        net start mysql >nul 2>&1
        net start mysql80 >nul 2>&1
        net start mariadb >nul 2>&1
    )
) else (
    echo [OK] MySQL Database Server is active on port 3306.
)

:: 3. Clear application caches on start
call php artisan config:clear >nul 2>&1
call php artisan view:clear >nul 2>&1

:: 4. Start local Laravel server in a minimized background window
echo [1/2] Launching Local ERP Server (http://127.0.0.1:8000)...
start /min "PWW_ERP_Server" php artisan serve --host=127.0.0.1 --port=8000

:: 5. Wait 2 seconds for server initialization
timeout /t 2 /nobreak >nul

:: 6. Automatically launch default web browser
echo [2/2] Opening ERP Login Portal in Default Web Browser...
start http://127.0.0.1:8000

echo.
echo ============================================================
echo   SUCCESS! PWW ERP is now running on your computer.
echo   
echo   Login Email: pww@gmail.com
echo   Password:    password
echo
echo   NOTE: Keep this window minimized while working in ERP.
echo ============================================================
echo.
