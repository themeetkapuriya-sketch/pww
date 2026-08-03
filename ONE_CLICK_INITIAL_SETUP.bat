@echo off
:: Ensure working directory is set to the folder where this batch file is located
cd /d "%~dp0"

title Praful Welding Works ERP - First Time Initial Setup
color 0B
echo ============================================================
echo   PRAFUL WELDING WORKS ERP - INITIAL SYSTEM SETUP
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
call composer install --no-dev --optimize-autoloader --no-interaction

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
call php artisan optimize:clear

echo.
echo ============================================================
echo   INITIAL SETUP COMPLETED SUCCESSFULLY!
echo   
echo   Super Admin Email: pww@gmail.com
echo   Super Admin Pass:  password
echo
echo   Double-click START_ERP.bat to launch the application!
echo ============================================================
echo.
pause
