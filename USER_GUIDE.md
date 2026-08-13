# Praful Welding Works (PWW) ERP - Easy User Guide & Operational Manual

Welcome to the **Praful Welding Works ERP User Guide**. This manual is designed for factory managers, billing staff, accountants, and administrators to easily operate every module of the ERP portal both online and 100% offline.

---

## 🧭 Navigation Quick-Map

| # | Sidebar Module | Primary Purpose | Key Action |
| :--- | :--- | :--- | :--- |
| **1** | **Overview** | Daily Business & Financial Dashboard | View live turnover, net profit, low stock alerts |
| **2** | **Production Logs** | Factory Output & Labor Wage Logging | Log completed welded racks & assign workers |
| **3** | **Sales Orders** | Client Order Management | Create client sales orders & track dispatches |
| **4** | **Invoice Ledger** | GST Invoicing & Payment Collection | Generate tax invoices, collect payments, print PDFs |
| **5** | **Purchase Ledger** | Vendor Purchases & Stock Restocking | Log raw material purchases & restock inventory |
| **6** | **Expenses Ledger** | Factory Overheads & GST Expenses | Record operational costs & log GST liability payments |
| **7** | **Raw Materials** | Raw Material Supply Management | Add raw materials, set safety alerts & prices |
| **8** | **Bill of Materials (BOM)** | Master Product Formulas | Define composition (how much wire/gas 1 rack needs) |
| **9** | **Products** | Finished Goods Catalog | Add welded rack models, SKUs, prices & GST rates |
| **10** | **Clients & Plants** | Client & Factory Delivery Locations | Add clients, GSTIN numbers, and shipping plant sites |
| **11** | **Employees** | Staff Profiles & Wages | Add welders/helpers & configure piece-rates |
| **12** | **Reports** | Audits, GSTR Tax Returns & P&L | Track GSTR-1, GSTR-3B tax & download CSV/PDF |
| **13** | **Settings Hub** | User Matrix & Category Manager | Control user access, category options & backup schedules |
| **14** | **Backup & Restore** | Database Safety Snapshots | Create manual SQL backups or restore snapshots |
| **15** | **Activity Audit Logs** | Security Audit Trail (Super Admin) | Monitor all system actions, price edits & export CSV |

---

## 🛠️ Step-by-Step Operating Instructions

### 1. Initial Login & User Credentials
1. Open your browser and navigate to `http://127.0.0.1:8000/login`.
2. Login with your Super Admin credentials:
   - **Email**: `pww@gmail.com`
   - **Password**: `password`

---

### 2. Setting Up Products & Raw Materials (Initial Setup)

#### Step 2.1: Add Raw Materials (Page: `Raw Materials`)
1. Click **Raw Materials** in the sidebar.
2. Click **+ Add Raw Material**.
3. Fill in:
   - **Material Name**: e.g., *MS Angle 50x50x5mm*
   - **Unit**: Select `KG`, `NOS`, `MTR`, or `LTR`.
   - **Initial Stock**: Quantity currently in stock (e.g., `2500 KG`).
   - **Safety Threshold Alert Limit**: Minimum limit before warning (e.g., `500 KG`).
   - **Purchase Price**: Average price per unit (e.g., `₹58.50`).
4. Click **Create Raw Material**.

#### Step 2.2: Add Finished Products (Page: `Products`)
1. Click **Products** in the sidebar.
2. Click **+ Add Product**.
3. Fill in:
   - **Product Model Name**: e.g., *Heavy Duty Industrial Storage Rack (2000x1000x500mm)*
   - **HSN Code**: GST HSN classification (e.g., `7308`).
   - **Selling Price / Piece**: Price per rack (e.g., `₹4500.00`).
   - **GST Rate**: Select `18%`, `12%`, `5%`, `28%`, or `0%`.
4. Click **Save Product**.

#### Step 2.3: Define Product BOM Formula (Page: `Bill of Materials (BOM)`)
*This links finished products to raw materials so stock auto-deducts when production is logged.*
1. Click **Bill of Materials (BOM)** in the sidebar.
2. Click **+ Configure New Product Composition**.
3. Select the **Finished Product** (e.g., *Heavy Duty Industrial Storage Rack*).
4. Select the **Raw Material Needed** (e.g., *MS Angle 50x50x5mm*).
5. Enter **Quantity Required Per 1 Piece** (e.g., `12.5 KG`) and **Waste Allowance %**.
6. Click **Save Composition Link**.

---

### 3. Daily Factory Production Logging

#### How to Log Finished Rack Output (Page: `Production Logs`)
1. Click **Production Logs** in the sidebar.
2. Click **+ Log New Production Shift**.
3. Select **Production Date** and the **Product Model** produced.
4. Enter **Quantity Produced** (e.g., `20 pieces`).
5. *(Optional)* Assign **Employees / Welders** and enter pieces completed by each worker to calculate their wage payout.
6. Click **Save Production Output**.
   > 💡 **Automated Stock Deduction**: The system automatically subtracts raw materials from stock and adds `20 pieces` to your finished product inventory!

---

### 4. Sales Orders & GST Invoicing

#### Step 4.1: Add Client & Delivery Plant (Page: `Clients & Plants`)
1. Click **Clients & Plants** in the sidebar.
2. Click **+ Add New Client** to enter the client company name, GSTIN (e.g., `24AAACT2727Q1ZW`), and state code.
3. Click **+ Add Plant Location** under that client to specify the delivery factory address.

#### Step 4.2: Create and Manage Sales Orders (Page: `Sales Orders`)
1. Go to **Sales Orders** in the sidebar.
2. Click **+ Create Sales Order**.
3. Select **Client**, **Delivery Plant**, and fill in the PO Number, Order Date, and Target Delivery Date.
4. Add products, select UOM, specify quantity and unit price, and click **Save Sales Order**.
5. **Print Job Card**: Click the **Job Card** button next to any order to view and print the A4 Factory Job Card / Work Order. This card displays the client, shipping destination, finished goods availability, and calculated raw material requirements (MRP) for production.
6. **Stock Check & Auto-Promote**: The system automatically checks inventory. If the required finished goods are in stock, the order status will auto-promote to `READY FOR DISPATCH`!

#### Step 4.3: Generate a GST Tax Invoice (Page: `Invoice Ledger`)
1. Click **Invoice Ledger** in the sidebar.
2. Click **+ Direct Invoice Itemizer**.
3. Select **Client** and **Shipping Plant Site**.
4. Enter **Transport Vehicle Number** (e.g., `GJ06AB1234`).
5. Add line items: Select product, enter quantity and unit price.
6. The system automatically computes:
   - **Intra-State (Gujarat `24`)**: 9% CGST + 9% SGST.
   - **Inter-State (Outside Gujarat)**: 18% IGST.
7. Click **Generate & Save Invoice**.

#### Step 4.4: Collect Payment & Print Invoice
- Click **Record Payment** (green button) on any unpaid invoice row to enter payment mode (`NEFT/RTGS`, `UPI`, `Cheque`, `Cash`) and received amount.
- Click **Print / PDF** (blue button) to view, download, or print the formatted GST Tax Invoice.


---

### 4.5. Employees Directory, Payroll Ledger & Salary Advances

#### Employee Status Toggle (Active / Inactive) (Page: `Employees`)
1. Go to **Employees Directory & Payroll Hub** in the sidebar.
2. In the **Employees Catalog** table, click the **Eye Icon** next to any employee profile to toggle between **Active (Green)** and **Inactive (Red)** status.
3. Inactive employees are automatically hidden from attendance sheets, advance issuing, and production logging while keeping historical salary records preserved.

#### View Individual Employee Ledger Passbook & Statement (Page: `Employees`)
1. Click the **Passbook Statement Icon (Blue File Button)** next to any employee.
2. The **Employee Financial Passbook Modal** opens displaying:
   - 💳 **Current Salary Rate**
   - 💸 **Pending Advance Paid**
   - 📊 **Selected Month Gross Earnings**
   - 🚨 **Net Due Amount** (`Gross Earnings - Pending Advance`)
3. Use the **Period Filter** dropdown to view **Current Month (Default)**, **Last 3 Months**, **This Year**, or **All Time Records**.
4. Click **Issue Advance** or **Pay Due Salary** directly from inside the statement modal.

#### Pay Monthly Salary & Auto-Log Expense (Page: `Employees`)
1. Go to the **Monthly Salary Ledger** tab.
2. Select the target **Month** (e.g. `2026-08`).
3. Click **Pay Salary** next to an employee.
4. Enter payment mode (`Cash`, `Bank Transfer`, `UPI`) and notes. The payment automatically reconciles pending advances and logs the net payout to the **Expenses Ledger**.

#### Issue Salary Advance (Page: `Employees`)
1. Click **+ Issue Salary Advance**.
2. Select Employee, enter **Advance Amount (₹)**, Payment Method (`Cash`, `Bank Transfer`, `UPI`), and Date.
3. Click **Save Advance**. Pending advances are date-filtered and automatically deducted when paying monthly salaries.

#### Physical Stock Audit & Variance Adjustment (Page: `Raw Materials`)
1. Go to **Raw Materials** in the sidebar.
2. Click **Adjust Stock** next to any raw material item.
3. Enter the **Actual Physical Verified Stock**, select a reason (`Physical Audit`, `Waste/Damage`, `Correction`), and save. The system logs the variance (+/-) in audit history.

### 5. Financial Audit & GST Tax Reports

#### Checking Monthly Tax Returns (Page: `Reports`)
1. Click **Reports** in the sidebar.
2. The **GST Tax Report** card displays:
   - 🟢 **Output GST Liability (Green)**: Tax collected from clients on sales invoices.
   - 🔴 **Eligible Input Tax Credit - ITC (Red)**: Tax paid on vendor purchases & expenses.
   - 🔵 **Net Tax Payable**: Exact remaining tax amount to be paid to the government for the selected month/financial year.
3. Click **Export CSV** or **Export PDF** to download complete audit spreadsheets.

---

### 6. System Customization & Power Features (Page: `Settings Hub`)

#### ⚡ 1-Click Simplified Billing & Accounting Mode
* For clients who only want to manage Invoicing, Vendor Purchases, Expenses, Clients, and Financial Reports without managing raw materials, stock deductions, or factory workers:
1. Go to **Settings Hub** (`/settings`) → **Active Modules** tab.
2. Toggle ON **⚡ 1-Click Simplified Billing & Accounting Mode**.
3. **Instant Transformation**:
   - Automatically checks and shows: Invoices, Purchases, Expenses, Clients, and Reports.
   - Automatically unchecks and hides: Orders, Production Logs, BOM, Inventory, and Payroll.
   - Automatically dims and locks **Automatic Inventory Stock Deductions** to prevent stock errors.

#### ⏰ Automated Database Backups & Email Off-Site Delivery
1. Go to **Settings Hub** (`/settings`) → **Security & System Backups** tab.
2. Set your schedule preference:
   - **Schedule**: `Daily`, `Weekly`, or `Monthly`.
   - **Preferred Execution Time**: Use the **Alarm-Style Time Picker** (select exact Hour, Minute, and AM/PM).
   - **Email Backup Attachment**: Enable to automatically send a copy of the `.sql` backup file to your email inbox for off-site data safety.
3. **Automated Catch-Up & Auto-Download**:
   - The moment the scheduled time arrives, the system generates the backup, emails it to your inbox, and **automatically downloads the `.sql` file directly into your PC `Downloads` folder** with a green toast notification!

#### ⚡ Global Keyboard Hotkeys
Speed up daily operations with keyboard shortcuts from anywhere in the portal:
* **`Alt + O`** — Open Overview Dashboard
* **`Alt + I`** — Open Sales Invoice Ledger
* **`Alt + P`** — Open Purchase Ledger
* **`Alt + E`** — Open Expense Ledger
* **`Alt + R`** — Open Reports & CA Exports
* **`Alt + S`** — Open Settings Hub
* **`Alt + H`** — Show Hotkey Cheat-Sheet Notification

#### ☀️ / 🌙 Light & Dark Mode Visual Theme Switcher
* Click the theme toggle icon (`☀️ / 🌙`) in the top navigation header next to the date.
* Instantly switches between Crisp Light Mode and High-Contrast Dark Slate Mode (`#0f172a` & `#1e293b`), preserving your choice across browser sessions!

---

### 🌐 100% Offline Capability Guide
- **Zero Internet Requirement**: All styling (Tailwind CSS), popups (SweetAlert2), DataTables, and JavaScript run from local files stored on your hard drive (`public/vendor/`).
- **Local Data Safety**: Your business data stays 100% private on your local computer.
- **Offline & Email Backups**: Automated backups save `.sql` files directly to `storage/app/backups/`, download to your local PC Downloads folder, and email attachments when online.
