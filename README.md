# Praful Welding Works (PWW) - Enterprise ERP System

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css)
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

---

## 🛠️ Technology Stack

- **Backend Framework**: Laravel 10.x (PHP 8.2+)
- **Database Architecture**: MySQL / SQLite
- **Frontend Architecture**: Blade Templates, Vanilla CSS, Tailwind CSS, Alpine.js / jQuery DataTables, TomSelect
- **PDF Engine**: Dompdf / Custom HTML Canvas PDF rendering
- **Testing Suite**: PHPUnit / Laravel Feature & Unit Tests (25 Test Suites, 186 Assertions)

---

## 🔧 Installation & Local Setup

### 1. Prerequisites
Ensure you have the following installed on your machine:
- PHP >= 8.2 with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json` extensions
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 2. Setup Environment
```bash
# Clone the repository
git clone https://github.com/themeetkapuriya-sketch/pww.git
cd pww

# Install PHP dependencies
composer install

# Environment configuration
cp .env.example .env

# Generate Application Security Key
php artisan key:generate
```

### 3. Database Migration & Seeding
Configure your database connection in `.env` (e.g., MySQL or SQLite):
```env
DB_CONNECTION=sqlite
# or MySQL settings:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pww_erp
# DB_USERNAME=root
# DB_PASSWORD=
```

Run database migrations and seed sample master data:
```bash
php artisan migrate --seed
```

### 4. Running the Application
```bash
# Clear application caches
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Start local server
php artisan serve
```
Access the application at `http://127.0.0.1:8000`.

---

## 🧪 Automated Testing

Execute the test suite to verify all business rules, GST logic, and inventory deduction engines:
```bash
php artisan test
```

---

## 📄 Documentation Directory

For complete deep-dive documentation:
- 📊 **[DATABASE_SCHEMA.md](file:///c:/laravel%20project/pww/DATABASE_SCHEMA.md)**: Full database table schemas, foreign keys, and ER diagrams.
- ⚙️ **[SYSTEM_ARCHITECTURE.md](file:///c:/laravel%20project/pww/SYSTEM_ARCHITECTURE.md)**: Business logic engines, BOM deduction, GST calculations, and security.
- 🌐 **[API_ROUTES.md](file:///c:/laravel%20project/pww/API_ROUTES.md)**: Web routes, controllers, and AJAX endpoints.
- 📖 **[USER_GUIDE.md](file:///c:/laravel%20project/pww/USER_GUIDE.md)**: Complete step-by-step user manual for factory staff, managers, and accountants.

---

## 🔒 Security & License
This project is proprietary software for **Praful Welding Works**. All rights reserved.
