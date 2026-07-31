@echo off
title Praful Welding Works ERP Launcher
echo ============================================================
echo      Praful Welding Works ERP - Starting System...
echo ============================================================
echo.

:: Verify PHP availability
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not found in system PATH.
    echo Please make sure PHP or XAMPP is installed.
    pause
    exit /b
)

:: Clear caches on start to ensure clean UI
call php artisan config:clear >nul 2>&1
call php artisan view:clear >nul 2>&1

:: Start local Laravel server in a minimized background window
echo [1/2] Launching Local ERP Server (http://127.0.0.1:8000)...
start /min "PWW_ERP_Server" php artisan serve --host=127.0.0.1 --port=8000

:: Wait 2 seconds for server initialization
timeout /t 2 /nobreak >nul

:: Automatically launch default web browser
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
