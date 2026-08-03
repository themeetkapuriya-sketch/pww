# 🛠️ Praful Welding Works (PWW) ERP - Client Device Local Setup Guide

This document provides a comprehensive, step-by-step installation and configuration guide for setting up the **Praful Welding Works ERP System** on a client's local computer or local factory server (Windows / Linux / macOS).

## ⚡ 1-CLICK LAUNCHER FOR NON-TECHNICAL CLIENTS

For non-technical users, **zero technical knowledge or terminal commands are required**:

1. **Daily 1-Click Launch**:
   Double-click **`START_ERP.bat`** in the project folder.
   * It starts the local ERP server automatically in the background.
   * It automatically opens your default browser directly to **`http://127.0.0.1:8000`**.

2. **First-Time 1-Click Setup**:
   Double-click **`ONE_CLICK_INITIAL_SETUP.bat`** to run initial environment configuration and database seeding automatically.

---

## 📋 1. System Requirements

Before starting the installation, ensure the client machine meets the following prerequisites:

### Hardware Requirements:
- **Operating System**: Windows 10 / Windows 11 (or Ubuntu Linux 20.04+)
- **RAM**: 4 GB minimum (8 GB recommended)
- **Disk Space**: 2 GB free SSD/HDD storage space

### Software Prerequisites:
- **PHP**: Version 8.2 or higher (with extensions: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)
- **Database**: MySQL Server 8.0+ or MariaDB (e.g. via XAMPP / WampServer)
- **Dependency Managers**: 
  - Composer 2.x+ ([Download Composer](https://getcomposer.org/))
  - Node.js 18.x+ & NPM ([Download Node.js](https://nodejs.org/))

---

## 💻 2. Step-by-Step Installation Guide

### Step 1: Copy Project Folder to Client Machine
Copy or clone the ERP project folder to the client's local disk (e.g., `C:\pww` or `C:\xampp\htdocs\pww`).

Open Command Prompt / PowerShell as Administrator and navigate into the project directory:
```powershell
cd C:\laravel project\pww
```

---

### Step 2: Configure Environment File
Copy `.env.example` to create the active `.env` configuration file:
```powershell
copy .env.example .env
```

Ensure `.env` contains the correct local MySQL database settings:
```env
APP_NAME="Praful Welding Works ERP"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pww
DB_USERNAME=root
DB_PASSWORD=
```

---

### Step 3: Install PHP & Node Dependencies
Run the following commands to install backend and frontend packages:

```powershell
# 1. Install PHP Composer Packages
composer install

# 2. Install NPM Frontend Dependencies
npm install

# 3. Generate Application Security Key
php artisan key:generate
```

---

### Step 4: Create MySQL Database & Run Seeders
1. Open XAMPP Control Panel or MySQL CLI and start **Apache** and **MySQL**.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) or MySQL Command Line and create a new database named **`pww`**:
   ```sql
   CREATE DATABASE pww CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Run database migrations and seed default system settings & Super Admin account:
   ```powershell
   php artisan migrate:fresh --seed --force
   ```

---

### Step 5: Build Local Offline Assets
Compile Tailwind CSS and JavaScript bundles for offline execution:
```powershell
# Clear all view and config caches
php artisan config:clear
php artisan view:clear

# Build production assets
npm run build
```

---

## 🚀 3. Quick All-In-One Automated Setup Command

Alternatively, after creating the `pww` database in MySQL, you can perform all installation steps automatically with a single command:

```powershell
composer setup
```

---

## 🔑 4. Default Admin Login Credentials

Once the database is seeded, open your browser and navigate to `http://127.0.0.1:8000/login`.

Log in using the default Super Admin credentials:

- 📧 **Login Email**: `pww@gmail.com`
- 🔒 **Password**: `password`
- 🛡️ **Role**: Super Admin (Full System Access)

> 💡 **Security Tip**: After logging in for the first time, navigate to **Profile Settings** to update your password.

---

## 🌐 5. Running the Application Locally

### Method A: Using Laravel Artisan Server (Simplest)
Open Terminal inside `C:\laravel project\pww` and run:
```powershell
php artisan serve
```
Open **`http://127.0.0.1:8000`** in Google Chrome or Microsoft Edge.

---

### Method B: Configuring Apache VirtualHost (Optional for Custom Domain)
If the client wants to open the app via `http://pww.local` without running `php artisan serve`:

1. In XAMPP `apache/conf/extra/httpd-vhosts.conf`, add:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/laravel project/pww/public"
       ServerName pww.local
       <Directory "C:/laravel project/pww/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. In Windows Hosts file (`C:\Windows\System32\drivers\etc\hosts`), add:
   ```text
   127.0.0.1   pww.local
   ```
3. Restart Apache. Open **`http://pww.local`** in browser.

---

## 🛡️ 6. Offline Operation & Security

### 100% Offline Capability:
- All core CSS styles (Tailwind), DataTables (v1.13.11 with Royal Blue Pill Pagination), SweetAlert2 (v11.17.2), TomSelect (v2.4.1), Chart.js (v4.4.7), and jQuery (v3.7.1) are pre-bundled in `public/vendor/`.
- **Zero internet connection is required** for daily operations (Invoices, Orders, Production Logs, Reports, Printing).

### Super Admin & Access Security:
- **Public registration is completely disabled**. All system users are created and managed by the Super Admin in **Settings -> User Access Matrix**.
- The primary Super Admin account (`pww@gmail.com`) is **permanently protected** and cannot be deactivated or deleted.
- Deactivated user accounts are automatically redirected to a unified `/account-deactivated` page with Super Admin contact information.

### Automated Local Backups:
- Database backup `.sql` snapshots are created automatically upon login according to your backup schedule (`monthly`/`weekly`).
- Backup files are stored locally on the client's computer at:
  `C:\laravel project\pww\storage\app\backups\`
- You can also navigate to **Backup & Restore Hub** (`/backup`) in the portal to generate on-demand backups or restore a snapshot.

---

## ❓ 7. Troubleshooting Common Setup Issues

### Issue 1: `SQLSTATE[HY000] [1049] Unknown database 'pww'`
* **Cause**: The database `pww` has not been created in MySQL yet.
* **Fix**: Open phpMyAdmin or MySQL CLI and run `CREATE DATABASE pww;`.

### Issue 2: `Port 8000 is already in use`
* **Fix**: Start the server on a different port:
  ```powershell
  php artisan serve --port=8080
  ```

### Issue 3: Missing Styling or Blank White Page
* **Fix**: Re-clear caches and re-compile frontend assets:
  ```powershell
  php artisan config:clear
  php artisan view:clear
  npm run build
  ```
