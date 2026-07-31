# Praful Welding Works (PWW) - Enterprise ERP System

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=flat-square&logo=tailwind-css)
![Test Status](https://img.shields.io/badge/Tests-28%20Passed%20(202%20Assertions)-brightgreen?style=flat-square)
![License](https://img.shields.io/badge/License-Proprietary-blue?style=flat-square)

**Praful Welding Works ERP** is a custom-engineered Enterprise Resource Planning platform built for industrial welding, metal fabrication, and rack manufacturing operations. The platform integrates real-time inventory tracking, Bill of Materials (BOM) auto-deduction engines, regional Indian GST compliance, piece-rate employee payroll, and financial audit reporting into a modern Single-Page Application (SPA) experience.

---

## 🚀 Key Modules & Capabilities

- 📊 **Dashboard & Financial Intelligence**: Real-time KPI metrics, monthly turnover, net profit margins, and low-stock alerts.
- ⚙️ **Production Logging & Auto-Stock Deduction**: Log daily rack/product manufacturing output with automated Bill of Materials (BOM) raw material deduction.
- 🧱 **Raw Material Audit**: Live stock level tracking, purchase cost averaging, and automated safety threshold alert limits.
- 📦 **Products Catalog**: SKU management, HSN classification, per-piece/per-kg pricing structures, and customizable GST tax brackets.
- 📐 **Bill of Materials (BOM) Engine**: Dynamic product composition mapping connecting finished goods to exact raw material quantities (e.g. wire coils, gas, welding rods).
- 🛒 **Sales Orders & Dispatch**: Client order tracking, multi-plant shipping destinations, and status lifecycle management.
- 🧾 **Tax Invoice Ledger**: Multi-state Indian GST invoicing (CGST + SGST vs IGST), transport vehicle logging, PDF invoice generation, and thermal/A4 printing.
- 🛍️ **Purchase Ledger**: Raw material procurement logging with auto-replenishment of stock levels.
- 💸 **Expense Ledger & GST Credit (ITC)**: Operational overhead tracking, vendor GST expense logging, and Eligible Input Tax Credit (ITC) reconciliation.
- 👥 **Staff & Piece-Rate Payroll**: Employee directory, daily/piece-rate wage calculation matrix, and labor payout logs.
- 🏢 **Clients & Multi-Plant Destinations**: Client master directory with regional GSTIN verification and multiple factory plant delivery addresses.
- 📈 **Audit & GST Tax Returns**: GSTR-1, GSTR-3B tax liability calculators, net tax payable insights, and CSV/PDF exportable reports.
- 🌐 **100% Offline Capability**: Bundled local vendor assets (`public/vendor/`) and compiled local Tailwind CSS stylesheet allowing zero-internet execution on local client machines.

---

## 🛠️ Technology Stack & Architecture

- **Backend Framework**: Laravel 12.x (PHP 8.2+)
- **Architecture**: Thin Controllers, Form Request Validation Layer, Service Layer Abstractions (`app/Services/`)
- **Database Engine**: MySQL 8.x / SQLite
- **Frontend Architecture**: Blade Templates, Tailwind CSS v4, jQuery SPA Navigation, SweetAlert2, DataTables, TomSelect
- **Offline Assets**: Pre-bundled local vendor libraries (`public/vendor/`) and Vite compiled CSS bundle
- **PDF Engine**: Dompdf / Custom HTML Canvas PDF rendering
- **Testing Suite**: Automated Test Suite (28 Test Suites, 202 Assertions 100% Green)

---

## 🔧 Installation & Local Machine Setup

### 1. Prerequisites
Ensure you have the following installed on your machine:
- PHP >= 8.2 with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json` extensions
- Composer >= 2.x
- Node.js >= 18.x & NPM
- MySQL Server (e.g., XAMPP / WampServer / MySQL Service)

### 2. Setup Environment
```bash
# Clone the repository
git clone https://github.com/themeetkapuriya-sketch/pww.git
cd pww

# Install PHP & Node dependencies
composer install
npm install

# Setup environment configuration
cp .env.example .env

# Generate Application Security Key
php artisan key:generate
```

### 3. Database Migration & Seeding
Configure your MySQL database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pww
DB_USERNAME=root
DB_PASSWORD=
```

Run database migrations and seed default system settings & Super Admin account:
```bash
php artisan migrate --seed
```

Or run the all-in-one setup script:
```bash
composer setup
```

### 4. Running the Application
```bash
# Clear application caches
php artisan config:clear
php artisan view:clear

# Build frontend production assets
npm run build

# Start local server
php artisan serve
```
Access the application at `http://127.0.0.1:8000`.

---

## 🔑 Default Login Credentials

- **Email**: `pww@gmail.com`
- **Password**: `password`
- **Role**: Super Admin (Full System Access)

---

## 🧪 Automated Testing

Execute the test suite to verify all business rules, GST logic, Form Requests, and inventory deduction engines:
```bash
php artisan test
```

---

## 📄 Documentation Directory

For complete deep-dive documentation:
- 📊 **[DATABASE_SCHEMA.md](file:///c:/laravel%20project/pww/DATABASE_SCHEMA.md)**: Full database table schemas, foreign keys, and ER diagrams.
- ⚙️ **[SYSTEM_ARCHITECTURE.md](file:///c:/laravel%20project/pww/SYSTEM_ARCHITECTURE.md)**: Service layer architecture, BOM deduction, GST calculations, and offline security.
- 🌐 **[API_ROUTES.md](file:///c:/laravel%20project/pww/API_ROUTES.md)**: Web routes, controllers, Form Requests, and AJAX endpoints.
- 📖 **[USER_GUIDE.md](file:///c:/laravel%20project/pww/USER_GUIDE.md)**: Complete step-by-step user manual for factory staff, managers, and accountants.

---

## 🔒 Security & License
This project is proprietary software for **Praful Welding Works**. All rights reserved.
