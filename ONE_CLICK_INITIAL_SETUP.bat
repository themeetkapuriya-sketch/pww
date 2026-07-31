@echo off
title Praful Welding Works ERP - First Time Initial Setup
echo ============================================================
echo   Praful Welding Works ERP - First Time Automatic Setup
echo ============================================================
echo.

:: 1. Create .env configuration file if missing
if not exist ".env" (
    echo [1/6] Creating environment configuration file (.env)...
    copy .env.example .env
) else (
    echo [1/6] Environment configuration file (.env) already exists.
)

:: 2. Install PHP Composer Dependencies
echo [2/6] Installing PHP Backend Dependencies (composer install)...
call composer install --no-interaction --prefer-dist

:: 3. Install NPM Frontend Dependencies
echo [3/6] Installing Frontend Packages & Asset Dependencies (npm install)...
call npm install

:: 4. Generate Application Security Encryption Key
echo [4/6] Generating Application Security Key...
call php artisan key:generate

:: 5. Run Database Migrations & Seed Default Data
echo [5/6] Migrating Database & Seeding System Master Data...
call php artisan migrate:fresh --seed --force

:: 6. Build Local Frontend Assets & Clear Caches
echo [6/6] Building Production Assets & Optimizing System Caches...
call npm run build
call php artisan config:clear
call php artisan view:clear

echo.
echo ============================================================
echo   INITIAL SETUP COMPLETED SUCCESSFULLY!
echo   
echo   Default Admin Email: pww@gmail.com
echo   Default Password:    password
echo
echo   You can now double-click START_ERP.bat to launch ERP!
echo ============================================================
echo.
pause
