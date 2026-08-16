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

#### Step 2.1: Add Raw Materials & Set Centralized Master Rate (Page: `Raw Materials`)
1. Click **Raw Materials** in the sidebar.
2. Click **+ Add Raw Material**.
3. Fill in:
   - **Material Name**: e.g., *MS Angle 50x50x5mm*
   - **Unit**: Select `KG`, `NOS`, `MTR`, or `LTR`.
   - **Initial Stock**: Quantity currently in stock (e.g., `2500 KG`).
   - **Safety Threshold Alert Limit**: Minimum limit before warning (e.g., `500 KG`).
   - **Purchase Rate (₹ / unit)**: Set a fixed master standard rate or leave blank/click **`🔄 Auto Avg`** to automatically compute the weighted average rate from all purchase entries.
4. Click **Create Raw Material**.
   > 💡 **Horizontal Scroll Support**: The Raw Materials ledger table includes horizontal scroll support (`style="min-width: 1100px;"`) ensuring all badges (`🔄 Auto Avg`, `🔒 Master Rate`), stock quantities, and action buttons remain clear and easily accessible on any screen resolution.

#### Step 2.2: Add Finished Products (Page: `Products`)
1. Click **Products** in the sidebar.
2. Click **+ Add Product**.
3. Fill in:
   - **Product Model Name**: e.g., *Heavy Duty Industrial Storage Rack (2000x1000x500mm)*
   - **SKU Code**: *(Optional)* Unique Stock Keeping Unit (e.g. `WR-3T-BALAJI` or leave blank).
   - **HSN Code**: GST HSN classification (e.g., `73089090`).
   - **UOM (Unit)**: Measurement unit (`piece`, `nos`, `set`, `kg`, etc.).
   - **Selling Price / Piece**: Price per rack (e.g., `₹4500.00`).
   - **Price / Kg**: *(Optional)* Dynamic per-kg rate.
   - **GST Rate**: Select `18%`, `12%`, `5%`, `28%`, or `0%`.
   - **Min Stock Alert**: Low stock alert warning threshold (e.g., `10`). Set `0` to disable alerts.
4. Click **Save Product**.

#### Step 2.3: Define Product BOM Formula & Cost Simulator (Page: `Bill of Materials (BOM)`)
*This links finished products to raw materials so stock auto-deducts when production is logged and computes live manufacturing costs.*
1. Click **Bill of Materials (BOM)** in the sidebar.
2. Click **+ Add BOM Formula** or click **Edit Formula** on any product card.
3. Add raw material ingredients, required quantities per unit, and waste scrap allowance percentages.
4. **📦 Universal Master Rate Inheritance**:
   - Selecting a raw material automatically displays its centralized live **Master Material Rate (₹)**.
   - When new purchase entries are logged or material rates change in Raw Materials Inventory, **all product recipes using that material update instantly across the entire factory**.
5. **📊 Manufacturing Cost & Profit Margin Simulator**:
   - The system automatically calculates **Line Cost**, **Est. Material Unit Cost (₹)**, **Waste Scrap Allowance (₹)**, **List Price (₹)**, and **Gross Profit (₹)** in real-time as you type: `Line Cost = Required Qty × (1 + Waste %) × Master Material Rate`.
   - Live color margin health indicator:
     - 🟢 **Green** ($\ge 25\%$): Strong / High Profit (Safe and healthy).
     - 🟡 **Yellow** ($10\% - 24.9\%$): Normal / Standard (Acceptable for volume manufacturing).
     - 🔴 **Red** ($< 10\%$): Low / Risk (Tight margin; review material costs or selling price).

#### ⚡ Low-Stock Smart Auto-Purchase Reorder Assistant
1. When any raw material falls below its safety alert limit, a **Smart Reorder Assistant** banner appears at the top of **Raw Materials Audit** (`/rawmaterial`) and on the **Dashboard Overview** (`/dashboard`).
2. Click **⚡ Reorder**: The system automatically calculates the replenishment deficit, launches the **Purchase Ledger**, and pre-fills the Category, Material, Quantity, and Rate!

---

### 3. Daily Factory Production Logging & Active Order Pipeline

#### Quick 1-Click `+ Produce` from Active Orders Header
1. Click **Active Orders** in the top navigation header bar to view your open order pipeline.
2. If finished goods warehouse stock is insufficient to fulfill an order item, a blue **`+ Produce`** button appears next to that item.
3. Clicking **`+ Produce`** instantly redirects to `/production`, auto-expands the batch logging form, pre-selects the product, and focuses the **Manufactured Qty** field for immediate entry.

#### How to Log Finished Rack Output (Page: `Production Logs`)
1. Click **Production Logs** in the sidebar.
2. Click **Log Production Run** (or arrive via the `+ Produce` button).
3. Select **Production Date** and the **Product Model** produced.
4. Enter **Quantity Produced** (e.g., `20 pieces`).
5. *(Optional)* Assign **Employees / Welders** and enter pieces completed by each worker to calculate their wage payout.
6. Click **Log Production Run**.
   > 💡 **Automated Stock & Order Lifecycle**: The system automatically subtracts raw materials from stock, increments finished product inventory, and automatically promotes eligible sales orders from `In Production` $\rightarrow$ `Ready to Dispatch`!

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
   - **Preferred Execution Time**: Select exact time of day when automatic backups generate.
   - **Auto-Delete Old Backups**: Choose when to remove old backup files (`After 3 Months (Recommended)`, `After 6 Months`, `After 1 Year`, or `Never`).
   - **Email Backup Attachment**: Enable to automatically send a copy of the `.sql` backup file to your email inbox for off-site data safety.
3. **Automated Catch-Up & Auto-Download**:
   - The moment the scheduled time arrives, the system generates the backup, emails it to your inbox, and **automatically downloads the `.sql` file directly into your PC `Downloads` folder** with a green toast notification!

#### 🧹 Auto-Clean Old Activity Logs & Free Up Storage
1. Go to **Settings Hub** (`/settings`) → **Security & System Backups** tab.
2. Select **Keep Logs For** (`30 Days`, `90 Days - Recommended`, `180 Days`, or `1 Year`).
3. Click **Clean Old Logs Now** to remove historical activity records and keep system search queries lightning-fast.

#### 🔒 Financial Year Period Lock (Audit Protection)
1. Go to **Settings Hub** (`/settings`) → **Tax & Financial** tab.
2. View active and closed financial periods starting from **FY 2026–27** onwards.
3. Click **🔒 Lock Period** next to any audited or closed financial year to prevent accidental edits, deletions, or retroactive back-dating of invoices, purchases, payroll, or expenses.

#### 🏷️ Dynamic Purchase, Expense & Raw Material Categories
1. Go to **Settings Hub** (`/settings`) → **Other Settings** → **Categories** tab.
2. Manage categories across three dedicated managers:
   - **Purchase Categories**: Add/edit purchase bill types (e.g. machinery, factory consumables, office assets).
   - **Expense Categories**: Add/edit daily expense voucher types with system protection for core accounting entries.
   - **Raw Material Categories**: Add custom raw material groups with custom icons (e.g. `🎨 Powder Coating`, `🔩 Pipes & Tubes`, `📐 Sheet Metal`, `⚡ Welding & Gas`, `🔧 Hardware`, `📦 Other Consumables`, `Packaging & Pallets`).
3. Added categories instantly update the **Raw Materials Ledger**, top filter pills, and drop-down selectors across the ERP!

---

### 7. Backup & Database Maintenance (Page: `Backup & System Restore`)

#### ⚡ 1-Click Database Optimization (`Optimize Database`)
* **What it does**: 100% safe maintenance tool that defragments MySQL tables, rebuilds search indexes, prunes expired login sessions, and frees up wasted disk space without affecting any business records.

#### 🛡️ Danger Zone: Factory Reset / Fresh System Start (`Reset to Fresh System`)
* **What it does**: Clears all test invoices, sample sales orders, production logs, attendance records, and expenses to start completely fresh for live business operations.
* **Master Data Kept 100% Intact**: Preserves your Products catalog, Raw Materials, BOM formulas, Clients, Plants, Staff profiles, User accounts, and Company settings.
* **Triple-Layer Safety**: Requires Admin Password, typing `"RESET"`, and **automatically saves a full emergency backup snapshot (`pre_reset_safety_...sql`)** before performing the reset.

---

### 🌐 100% Offline Capability Guide
- **Zero Internet Requirement**: All styling (Tailwind CSS), popups (SweetAlert2), DataTables, and JavaScript run from local files stored on your hard drive (`public/vendor/`).
- **Local Data Safety**: Your business data stays 100% private on your local computer.
- **Offline & Email Backups**: Automated backups save `.sql` files directly to `storage/app/backups/`, download to your local PC Downloads folder, and email attachments when online.
