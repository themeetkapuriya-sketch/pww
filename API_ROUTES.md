# Praful Welding Works ERP - API & Web Routes Reference

This document maps all web routes, HTTP methods, controller handlers, middleware security, and route names in **Praful Welding Works ERP**.

---

## 🔐 Route Authentication
All ERP routes are protected behind the `auth` middleware authentication gate (`Route::middleware(['auth'])`). Unauthenticated requests are redirected to `/login`.

---

## 📌 Route Mapping Table

### 1. Main Navigation Routes

| URI | Method | Controller & Action | Route Name | Description |
| :--- | :--- | :--- | :--- | :--- |
| `/` | `GET` | Redirect -> `/overview` | - | Root Redirection |
| `/overview` | `GET` | `OverviewController@overview` | `overview` | Main KPI Dashboard |
| `/production` | `GET` | `ProductionController@production` | `production` | Production Logs Page |
| `/orders` | `GET` | `OrderController@orders` | `orders` | Sales Orders Directory |
| `/invoices` | `GET` | `InvoiceController@invoices` | `invoices` | Invoice Ledger |
| `/purchases` | `GET` | `PurchaseController@purchases` | `purchases` | Purchase Ledger |
| `/expenses` | `GET` | `ExpenseController@expenses` | `expenses` | Expense Ledger |
| `/rawmaterial` | `GET` | `RawMaterialController@index` | `rawmaterial` | Raw Materials Audit Page |
| `/bom` | `GET` | `BomController@bom` | `bom` | Bill of Materials Page |
| `/product` | `GET` | `ProductController@index` | `product` | Products Catalog Page |
| `/inventory` | `GET` | Closure Redirect -> `/product` or `/rawmaterial` | `inventory` | Backward-Compatibility Redirect |
| `/clients` | `GET` | `ClientController@clients` | `clients` | Clients & Plants Directory |
| `/employees` | `GET` | `EmployeeController@employees` | `employees` | Staff Directory & Payroll |
| `/reports` | `GET` | `ReportController@reports` | `reports` | Financial & Tax Reports |
| `/profile` | `GET` | `ProfileController@profile` | `profile` | User Profile & Settings |

---

### 2. Form Actions & AJAX Endpoints

#### 🧱 Raw Materials (`RawMaterialController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/inventory/materials` | `POST` | `RawMaterialController@store` | `inventory.materials.store` | JSON (200 / 422) |
| `/inventory/materials/{id}` | `PUT` | `RawMaterialController@update` | `inventory.materials.update` | JSON (200 / 422) |
| `/inventory/materials/{id}` | `DELETE` | `RawMaterialController@destroy` | `inventory.materials.delete` | JSON (200) |

#### 📦 Products Catalog (`ProductController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/inventory/goods` | `POST` | `ProductController@store` | `inventory.goods.store` | JSON (200 / 422) |
| `/inventory/goods/{id}` | `PUT` | `ProductController@update` | `inventory.goods.update` | JSON (200 / 422) |
| `/inventory/goods/{id}` | `DELETE` | `ProductController@destroy` | `inventory.goods.delete` | JSON (200) |

#### 📐 Bill of Materials (`BomController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/bom` | `POST` | `BomController@storeBom` | `bom.store` | JSON (200 / 422) |
| `/bom/{id}` | `DELETE` | `BomController@deleteBom` | `bom.delete` | JSON (200) |

#### ⚙️ Production & Labor Logs (`ProductionController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/production` | `POST` | `ProductionController@storeProduction` | `production.store` | JSON (200 / 422) |
| `/production/{id}` | `DELETE` | `ProductionController@deleteProduction` | `production.delete` | JSON (200) |

#### 🏢 Clients & Plants (`ClientController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/clients` | `POST` | `ClientController@storeClient` | `clients.store` | JSON (200 / 422) |
| `/clients/plants` | `POST` | `ClientController@storePlant` | `clients.plants.store` | JSON (200 / 422) |
| `/clients/ledger/{clientId}` | `GET` | `ClientController@clientLedger` | `clients.ledger` | HTML View Render |

#### 🛒 Sales Orders (`OrderController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/orders` | `POST` | `OrderController@storeOrder` | `orders.store` | JSON (200 / 422) |

#### 🧾 Invoices & Payments (`InvoiceController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/invoices/itemized` | `POST` | `InvoiceController@generateItemizedInvoice` | `invoices.itemized.store` | JSON (200 / 422) |
| `/invoices/{id}/pay` | `POST` | `InvoiceController@payInvoice` | `invoices.pay` | JSON (200 / 422) |
| `/invoices/{id}/preview` | `GET` | `InvoiceController@previewInvoice` | `invoices.preview` | HTML View / PDF |
| `/invoices/{id}` | `DELETE` | `InvoiceController@deleteInvoice` | `invoices.delete` | JSON (200) |

#### 🛍️ Purchases (`PurchaseController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/purchases` | `POST` | `PurchaseController@storePurchase` | `purchases.store` | JSON (200 / 422) |

#### 💸 Expenses (`ExpenseController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/expenses` | `POST` | `ExpenseController@storeExpense` | `expenses.store` | JSON (200 / 422) |

#### 👥 Employees (`EmployeeController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/employees` | `POST` | `EmployeeController@storeEmployee` | `employees.store` | JSON (200 / 422) |
| `/employees/{id}` | `PUT` | `EmployeeController@updateEmployee` | `employees.update` | JSON (200 / 422) |
| `/employees/{id}` | `DELETE` | `EmployeeController@deleteEmployee` | `employees.delete` | JSON (200) |

#### 👤 Profile & Settings (`ProfileController`)
| URI | Method | Handler | Route Name | Response |
| :--- | :--- | :--- | :--- | :--- |
| `/profile` | `POST` | `ProfileController@updateProfile` | `profile.update` | JSON (200 / 422) |
| `/profile/password` | `POST` | `ProfileController@updatePassword` | `profile.password` | JSON (200 / 422) |
| `/profile/settings` | `POST` | `ProfileController@updateSettings` | `profile.settings` | JSON (200 / 422) |
