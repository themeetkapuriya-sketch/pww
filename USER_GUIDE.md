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

#### Step 4.2: Generate a GST Tax Invoice (Page: `Invoice Ledger`)
1. Click **Invoice Ledger** in the sidebar.
2. Click **+ Direct Invoice Itemizer**.
3. Select **Client** and **Shipping Plant Site**.
4. Enter **Transport Vehicle Number** (e.g., `GJ06AB1234`).
5. Add line items: Select product, enter quantity and unit price.
6. The system automatically computes:
   - **Intra-State (Gujarat `24`)**: 9% CGST + 9% SGST.
   - **Inter-State (Outside Gujarat)**: 18% IGST.
7. Click **Generate & Save Invoice**.

#### Step 4.3: Collect Payment & Print Invoice
- Click **Record Payment** (green button) on any unpaid invoice row to enter payment mode (`NEFT/RTGS`, `UPI`, `Cheque`, `Cash`) and received amount.
- Click **Print / PDF** (blue button) to view, download, or print the formatted GST Tax Invoice.

---

### 5. Financial Audit & GST Tax Reports

#### Checking Monthly Tax Returns (Page: `Reports`)
1. Click **Reports** in the sidebar.
2. The **GST Tax Report** card displays:
   - 🟢 **Output GST Liability (Green)**: Tax collected from clients on sales invoices.
   - 🔴 **Eligible Input Tax Credit - ITC (Red)**: Tax paid on vendor purchases & expenses.
   - 🔵 **Net Tax Payable**: Exact remaining tax amount to be paid to the government for the selected month/financial year.
3. Click **Export CSV** or **Export PDF** to download complete audit spreadsheets.

---

### 🌐 100% Offline Capability Guide
- **Zero Internet Requirement**: All styling (Tailwind CSS), popups (SweetAlert2), DataTables, and JavaScript run from local files stored on your hard drive (`public/vendor/`).
- **Local Data Safety**: Your business data stays 100% private on your local computer.
- **Offline Backups**: Automated backups save `.sql` files directly to `storage/app/backups/`.
