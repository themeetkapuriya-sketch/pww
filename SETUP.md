# 🛠️ Praful Welding Works (PWW) ERP - Local Machine Setup Guide

This document is the official setup and user guide for running the **Praful Welding Works ERP System** locally on your personal computer / local workstation (Windows / macOS / Linux).

> 📌 **Note:** This application is configured strictly for **local machine use**. No cloud hosting or remote server deployment is required.

---

## ⚡ 1-CLICK SYSTEM LAUNCHER (Simplest Way)

For everyday usage, **no technical commands or terminal windows are needed**:

1. **Daily Startup**:
   Double-click **`START_ERP.bat`** in the project folder.
   * Starts the local MySQL database server automatically if it isn't running.
   * Launches the ERP server locally on **`http://127.0.0.1:8000`**.
   * Automatically opens your default web browser to the login page.

2. **First-Time Setup**:
   Double-click **`ONE_CLICK_INITIAL_SETUP.bat`** to generate your local `.env`, create database tables, seed sample data, link storage, and prepare local frontend assets automatically.

---

## 💻 Local Prerequisites

Before starting, ensure your local computer has:

* **PHP**: 8.2 or higher (XAMPP / Laragon / standalone PHP)
* **MySQL Database**: MySQL 8.0+ or MariaDB (via XAMPP / WampServer)
* **Composer**: 2.x+ ([Download Composer](https://getcomposer.org/))
* **Node.js & NPM**: 18.x+ ([Download Node.js](https://nodejs.org/))

---

## 🔧 Step-by-Step Manual Local Setup

If you prefer setting up the local environment manually via terminal:

### Step 1: Open Terminal in Project Folder
```powershell
cd "C:\laravel project\pww"
```

### Step 2: Configure Local Environment (`.env`)
Ensure `.env` exists and contains local configuration defaults:
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

### Step 3: Install Local Dependencies
```powershell
# Install PHP packages
composer install

# Install Frontend packages
npm install

# Generate local security key
php artisan key:generate
```

### Step 4: Setup Database & Storage Symlink
1. Start **MySQL** in XAMPP / WampServer.
2. Create local database `pww`:
   ```sql
   CREATE DATABASE pww CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Run local database migrations & default master data seeders:
   ```powershell
   php artisan migrate:fresh --seed
   ```
4. Create local public storage link (for avatars, logos, documents):
   ```powershell
   php artisan storage:link
   ```

### Step 5: Build Local Assets & Start Application
```powershell
# Build frontend assets
npm run build

# Start local server
php artisan serve
```

Access the portal in your browser at: **`http://127.0.0.1:8000`**

---

## 🔑 Default Local Login Credentials

- 📧 **Super Admin Email**: `pww@gmail.com`
- 🔒 **Password**: `password`
- 🛡️ **Role**: Super Admin (Full Local ERP Access)

---

## 💾 Local Data Safety & Backups

- All business data is stored 100% locally on your machine in the MySQL database.
- Automatic database snapshots are generated upon login and stored locally at:
  `C:\laravel project\pww\storage\app\backups\`
- You can also navigate to **Backup & Restore Hub** (`/backup`) inside the app to download `.sql` backups directly to your local computer.
