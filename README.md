# Praful Welding Works (PWW) - Enterprise ERP System

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=flat-square&logo=tailwind-css)
![Test Status](https://img.shields.io/badge/Tests-31%20Passed%20(243%20Assertions)-brightgreen?style=flat-square)
![License](https://img.shields.io/badge/License-Proprietary-blue?style=flat-square)

**Praful Welding Works ERP** is a custom-engineered Enterprise Resource Planning platform built for industrial welding, metal fabrication, and rack manufacturing operations. The platform integrates real-time inventory tracking, Bill of Materials (BOM) auto-deduction engines, regional Indian GST compliance, piece-rate employee payroll, and financial audit reporting into a modern Single-Page Application (SPA) experience.

---

## 🚀 Key Modules & Capabilities

- 📊 **Dashboard & Financial Intelligence**: Real-time KPI metrics, monthly turnover, net profit margins, and low-stock alerts.
- ⚙️ **Production Logging & Auto-Stock Deduction**: Log daily rack/product manufacturing output with automated Bill of Materials (BOM) raw material deduction and 1-click `+ Produce` pipeline shortcuts.
- 🧱 **Raw Materials Inventory & Centralized Master Rate**: Live stock level tracking, direct Master Purchase Rate (`₹ / unit`) configuration with automatic fallback to live **Weighted-Average Purchase Cost** (`🔄 Auto Avg`), responsive horizontal-scroll audit ledger, and safety threshold alerts.
- 📦 **Products Catalog & Stock Adjustment**: Optional SKU management, HSN classification, dual-UOM per-piece/per-kg pricing structures, customizable GST tax brackets, minimum stock safety alert thresholds, and dedicated **⚡ Real-Time Stock Adjustment Modal** (`Set Total`, `Add (+)`, `Deduct (-)`) with zero page reloads.
- 📐 **Bill of Materials (BOM) & Real-Time Cost Simulator**: Dynamic product composition mapping connecting finished goods to exact raw material quantities, universally inheriting the centralized raw material master rates with live line cost and gross margin indicators (🟢 Healthy $\ge 25\%$, 🟡 Normal $10-24.9\%$, 🔴 Risk $<10\%$).
- 🛒 **Sales Orders & Dispatch**: Client order tracking, multi-plant shipping destinations, real-time warehouse deficit tracking, and automated order status lifecycle management (`In Production` $\rightarrow$ `Ready to Dispatch`).
- 🧾 **Dual Tax Invoicing Engine & Standardized Printing**: 1-click toggle between **Tax Invoices (With GST)** and **Invoices (Without GST / 0% Tax)**, transport vehicle logging, standardized document headers (`TAX INVOICE` vs `INVOICE`), seller business GSTIN display, single-plant client formatting, and 5% opacity anti-counterfeit watermarks.
- 🛍️ **Purchase Ledger**: Raw material procurement logging with auto-replenishment of stock levels.
- 💸 **Expense Ledger & GST Credit (ITC)**: Operational overhead tracking, vendor GST expense logging, and Eligible Input Tax Credit (ITC) reconciliation.
- 💵 **Salary Advances & Payroll Deductions**: Worker salary advance payouts with payment methods (Cash, Bank, UPI) and automatic deduction against monthly salary disbursals.
- 🔍 **Stock Audit & Physical Variance Adjustments**: Physical inventory stock reconciliation, variance logging (+/-), and audit trail tracking for raw materials.
- 🔔 **Header Alert Widgets**: Live header badge widgets for real-time tracking of active production pipeline orders and low-stock safety threshold alerts.
- 👥 **Staff, Piece-Rate Payroll & Employee Passbooks**: Employee directory with Active/Inactive status toggling, daily/piece-rate wage calculation matrix, monthly salary disbursal tracking, and individual financial passbook statement modals with period filtering (Current Month, Last 3 Months, This Year, All Time).
- 🏢 **Clients & Multi-Plant Destinations**: Client master directory with regional GSTIN verification and multiple factory plant delivery addresses.
- 📈 **Audit & GST Tax Returns**: GSTR-1, GSTR-3B tax liability calculators, net tax payable insights, and CSV/PDF exportable reports.
- 🔒 **Financial Year Period Lock**: Tax audit & CA filing protection locking closed financial years (starting from FY 2026–27) against accidental edits or deletions.
- ⚡ **1-Click Database Optimization & Health Maintenance**: Safe defragmentation (`OPTIMIZE TABLE`), search index rebuilding, session pruning, and live space reclaiming.
- 🛡️ **Super-Admin Activity Audit Logs**: Real-time audit trail capturing all user actions with auto-cleaning retention policies and CSV exports (`/activity-logs`).
- ⚙️ **Category Management & Auto-Backup System**: Dynamic Purchase & Expense category management with mandatory system protections, auto-delete policies for old backups, and auto-downloading directly to local PC Downloads folders.
- ⚡ **1-Click Simplified Billing & Accounting Mode**: Master toggle to simplify the portal for billing-only usage (Invoices, Purchases, Expenses, Clients, Reports), automatically suppressing manufacturing tabs and locking stock deductions.
- ⌨️ **Global Keyboard Hotkeys**: Ultra-fast hotkey shortcuts (`Alt+I` Invoices, `Alt+P` Purchases, `Alt+E` Expenses, `Alt+R` Reports, `Alt+S` Settings, `Alt+H` Cheat Sheet).
- ☀️/🌙 **Visual Light & Dark Theme Switcher**: Instant theme toggle button in the header (`☀️ / 🌙`) with dark slate palette (`#0f172a` & `#1e293b`) and session persistence.
- 🔐 **Super Admin Security & Role Management**: Public registration disabled. User accounts are created strictly by Super Admin. Super Admin (`pww@gmail.com`) is permanently protected from deactivation. Deactivated users redirect to a unified `/account-deactivated` page.
- ⏳ **Seamless Session Timeout & 419 CSRF Protection**: Automatic full-browser redirection to the styled login portal upon session expiration, preventing unstyled HTML or broken stylesheets.
- ⚡ **0ms Anticipatory Modal Pre-fetching**: Instant zero-latency 360° Order Control Hub modal opening via anticipatory background hover prefetching with in-memory caching.
- 🌐 **100% Offline Capability**: All vendor assets (jQuery v3.7.1, DataTables v1.13.11, SweetAlert2 v11.17.2, TomSelect v2.4.1, Chart.js v4.4.7, Tailwind JS engine, and local Outfit fonts) are stored locally in `public/vendor/` and `public/fonts/`.

---

## 🛠️ Technology Stack & Architecture

- **Backend Framework**: Laravel 12.x (PHP 8.2+)
- **Architecture**: Thin Controllers, Form Request Validation Layer, Service Layer Abstractions (`app/Services/`)
- **Database Engine**: MySQL 8.x / SQLite
- **Frontend Architecture**: Blade Templates, Tailwind CSS v4, jQuery SPA Navigation, SweetAlert2, DataTables (with Royal Blue Pill Pagination), TomSelect
- **Offline Assets**: Pre-bundled local vendor libraries (`public/vendor/`) and Vite compiled CSS bundle
- **PDF Engine**: Dompdf / Custom HTML Canvas PDF rendering
- **Testing Suite**: Automated Test Suite (31 Test Suites, 243 Assertions 100% Green)

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
