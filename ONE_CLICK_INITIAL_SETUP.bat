@echo off
:: Ensure working directory is set to the folder where this batch file is located
cd /d "%~dp0"

title Praful Welding Works ERP - First Time Initial Setup
color 0B
echo ============================================================
echo   PRAFUL WELDING WORKS ERP - INITIAL SYSTEM SETUP
echo ============================================================
echo.

:: Auto-detect XAMPP PHP path and add to PATH if not already available
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        echo [INFO] PHP not found in PATH. Using XAMPP PHP from C:\xampp\php
        set "PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%"
    ) else (
        echo [ERROR] PHP is not installed or not in your system PATH!
        echo         Please install XAMPP and ensure C:\xampp\php is accessible.
        echo.
        pause
        exit /b 1
    )
)

:: Auto-detect Composer
where composer >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Composer is not installed or not in your system PATH!
    echo         Download from https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)

:: Auto-detect Node.js / NPM
where npm >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Node.js / NPM is not installed or not in your system PATH!
    echo         Download from https://nodejs.org/
    echo.
    pause
    exit /b 1
)

:: Auto-detect MySQL (XAMPP)
where mysql >nul 2>nul
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        echo [INFO] MySQL not found in PATH. Using XAMPP MySQL from C:\xampp\mysql\bin
        set "PATH=C:\xampp\mysql\bin;%PATH%"
    )
)

echo [OK] All prerequisites detected: PHP, Composer, NPM, MySQL
echo.

:: 0. Create backup directory on D: drive if it doesn't exist
if not exist "D:\pww_backups" (
    echo [0/7] Creating backup directory D:\pww_backups...
    mkdir "D:\pww_backups"
)

:: 1. Create .env configuration file if missing
if not exist ".env" (
    echo [1/7] Creating environment configuration file (.env)...
    copy .env.example .env
) else (
    echo [1/7] Environment configuration file (.env) already exists.
)

:: 2. Install PHP Composer Dependencies
echo [2/7] Installing PHP Backend Dependencies (composer install)...
call composer install --no-dev --optimize-autoloader --no-interaction
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Composer install failed! Check error messages above.
    pause
    exit /b 1
)

:: 3. Install NPM Frontend Dependencies
echo [3/7] Installing Frontend Packages (npm install)...
call npm install
if %ERRORLEVEL% neq 0 (
    echo [ERROR] NPM install failed! Check error messages above.
    pause
    exit /b 1
)

:: 4. Generate Application Security Encryption Key
echo [4/7] Generating Application Security Key...
call php artisan key:generate --force
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Key generation failed! Check .env file exists.
    pause
    exit /b 1
)

:: 5. Auto-create database if it doesn't exist
echo [5/7] Creating database 'pww' if not exists...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pww CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if %ERRORLEVEL% neq 0 (
    echo [WARNING] Could not auto-create database. Please create 'pww' database manually in phpMyAdmin.
)

:: 6. Run Database Migrations & Seed Default Data
echo [6/7] Migrating Database & Seeding System Master Data...
call php artisan migrate:fresh --seed --force
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Database migration failed! Make sure MySQL is running in XAMPP.
    pause
    exit /b 1
)

:: 7. Build Local Frontend Assets, Storage Link & Clear Caches
echo [7/7] Building Production Assets & Optimizing...
call npm run build
call php artisan storage:link 2>nul
call php artisan optimize:clear

echo.
echo ============================================================
echo   INITIAL SETUP COMPLETED SUCCESSFULLY!
echo   
echo   Super Admin Email: pww@gmail.com
echo   Super Admin Pass:  password
echo   Auto Backup Path:  D:\pww_backups
echo.
echo   Double-click START_ERP.bat to launch the application!
echo ============================================================
echo.
pause
