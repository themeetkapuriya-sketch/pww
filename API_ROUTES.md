# Praful Welding Works ERP - API & Web Routes Reference

This document maps all web routes, HTTP methods, controller handlers, Form Request validators, middleware security gates, and route names in **Praful Welding Works ERP**.

---

## 🔐 Route Authentication & RBAC

All ERP routes are protected behind the `auth` middleware authentication gate (`Route::middleware(['auth'])`). Unauthenticated requests are automatically redirected to `/login`.

---

## 📌 Complete Web Routes Mapping Table

### 1. Navigation & Dashboard Routes

| URI | Method | Controller & Action | Route Name | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/` | `GET` | Redirect -> `/overview` | - | `auth` | Root Redirection |
| `/overview` | `GET` | `OverviewController@overview` | `overview` | `auth` | KPI Dashboard Overview |
| `/account-deactivated` | `GET` | `AuthController@accountDeactivated` | `account.deactivated` | - | Deactivated Account Screen |
| `/production` | `GET` | `ProductionController@production` | `production` | `auth` | Production Output Logs |
| `/orders` | `GET` | `OrderController@orders` | `orders` | `auth` | Sales Orders Directory |
| `/invoices` | `GET` | `InvoiceController@invoices` | `invoices` | `auth` | Tax Invoices Ledger |
| `/purchases` | `GET` | `PurchaseController@purchases` | `purchases` | `auth` | Raw Material Purchase Ledger |
| `/expenses` | `GET` | `ExpenseController@expenses` | `expenses` | `auth` | Factory Expenses Ledger |
| `/rawmaterial` | `GET` | `RawMaterialController@index` | `rawmaterial` | `auth` | Raw Materials Inventory Audit |
| `/bom` | `GET` | `BomController@bom` | `bom` | `auth` | Bill of Materials Formulations |
| `/product` | `GET` | `ProductController@index` | `product` | `auth` | Finished Goods Products Catalog |
| `/clients` | `GET` | `ClientController@clients` | `clients` | `auth` | Clients & Plants Directory |
| `/employees` | `GET` | `EmployeeController@employees` | `employees` | `auth` | Staff Directory & Payroll Hub |
| `/reports` | `GET` | `ReportController@reports` | `reports` | `auth` | Financial Audit & GST Reports |
| `/profile` | `GET` | `ProfileController@profile` | `profile` | `auth` | User Profile Settings |
| `/settings` | `GET` | `SettingsController@index` | `settings.index` | `auth` | Settings Hub & User Access Matrix |
| `/backup` | `GET` | `BackupController@index` | `backup.index` | `auth` | Automated System Backup & Restore Hub |

> ℹ️ **Note on Registration Routes**: Public registration (`/register`) is completely removed from the system. User account creation is handled strictly by the Super Admin in the Settings Hub.

---

### 2. Form Actions & AJAX Endpoints

#### 🧱 Raw Materials (`RawMaterialController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/inventory/materials` | `POST` | `RawMaterialController@store` | Inline | `inventory.materials.store` | JSON (200 / 422) |
| `/inventory/materials/{id}` | `PUT` | `RawMaterialController@update` | Inline | `inventory.materials.update` | JSON (200 / 422) |
| `/inventory/materials/{id}` | `DELETE` | `RawMaterialController@destroy` | - | `inventory.materials.delete` | JSON (200) |

#### 📦 Products Catalog (`ProductController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/inventory/goods` | `POST` | `ProductController@store` | Inline | `inventory.goods.store` | JSON (200 / 422) |
| `/inventory/goods/{id}` | `PUT` | `ProductController@update` | Inline | `inventory.goods.update` | JSON (200 / 422) |
| `/inventory/goods/{id}` | `DELETE` | `ProductController@destroy` | - | `inventory.goods.delete` | JSON (200) |

#### 📐 Bill of Materials (`BomController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/bom` | `POST` | `BomController@storeBom` | `StoreBomRequest` | `bom.store` | JSON (200 / 422) |
| `/bom/{id}` | `DELETE` | `BomController@deleteBom` | - | `bom.delete` | JSON (200) |

#### ⚙️ Production & Labor Logs (`ProductionController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/production` | `POST` | `ProductionController@logProduction` | Inline / Service | `production.store` | JSON (200 / 422) |
| `/production/{id}` | `DELETE` | `ProductionController@deleteProduction` | - | `production.delete` | JSON (200) |

#### 🏢 Clients & Plants (`ClientController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/clients` | `POST` | `ClientController@storeClient` | `StoreClientRequest` | `clients.store` | JSON (200 / 422) |
| `/clients/plants` | `POST` | `ClientController@storePlant` | Inline | `clients.plants.store` | JSON (200 / 422) |
| `/clients/ledger/{clientId}` | `GET` | `ClientController@clientLedger` | - | `clients.ledger` | HTML View / PDF |

#### 🛒 Sales Orders (`OrderController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/orders` | `POST` | `OrderController@storeOrder` | `StoreSalesOrderRequest` | `orders.store` | JSON (200 / 422) |
| `/orders/{id}/status` | `POST` | `OrderController@updateStatus` | Inline | `orders.status.update` | JSON (200 / 422) |

#### 🧾 Invoices & Payments (`InvoiceController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/invoices/itemized` | `POST` | `InvoiceController@generateItemizedInvoice` | `StoreInvoiceRequest` | `invoices.itemized.store` | JSON (200 / 422) |
| `/invoices/{id}/pay` | `POST` | `InvoiceController@payInvoice` | Inline | `invoices.pay` | JSON (200 / 422) |
| `/invoices/{id}/preview` | `GET` | `InvoiceController@previewInvoice` | - | `invoices.preview` | HTML View / PDF |
| `/invoices/{id}` | `DELETE` | `InvoiceController@deleteInvoice` | - | `invoices.delete` | JSON (200) |

#### 🛍️ Purchases (`PurchaseController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/purchases` | `POST` | `PurchaseController@storePurchase` | Inline | `purchases.store` | JSON (200 / 422) |

#### 💸 Expenses (`ExpenseController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/expenses` | `POST` | `ExpenseController@storeExpense` | Inline | `expenses.store` | JSON (200 / 422) |

#### 👥 Employees (`EmployeeController`)
| URI | Method | Handler | Form Request Validator | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/employees` | `POST` | `EmployeeController@storeEmployee` | Inline | `employees.store` | JSON (200 / 422) |
| `/employees/{id}` | `PUT` | `EmployeeController@updateEmployee` | Inline | `employees.update` | JSON (200 / 422) |
| `/employees/{id}` | `DELETE` | `EmployeeController@deleteEmployee` | - | `employees.delete` | JSON (200) |
