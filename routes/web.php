<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Production\BomController;
use App\Http\Controllers\Production\ProductionController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Purchases\PurchaseController;
use App\Http\Controllers\Payroll\EmployeeController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Profile\ProfileController;

// Include authentication routes
require __DIR__ . '/auth.php';

// Gated ERP routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('overview');
    });

    // 1. Dashboard Overview
    Route::get('/overview', [OverviewController::class, 'overview'])->name('overview');

    // 2. Inventory Management
    Route::get('/inventory', [InventoryController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/materials', [InventoryController::class, 'storeRawMaterial'])->name('inventory.materials.store');
    Route::put('/inventory/materials/{id}', [InventoryController::class, 'updateRawMaterial'])->name('inventory.materials.update');
    Route::delete('/inventory/materials/{id}', [InventoryController::class, 'deleteRawMaterial'])->name('inventory.materials.delete');
    Route::post('/inventory/goods', [InventoryController::class, 'storeFinishedGood'])->name('inventory.goods.store');
    Route::put('/inventory/goods/{id}', [InventoryController::class, 'updateFinishedGood'])->name('inventory.goods.update');
    Route::delete('/inventory/goods/{id}', [InventoryController::class, 'deleteFinishedGood'])->name('inventory.goods.delete');

    // 3. Bill of Materials
    Route::get('/bom', [BomController::class, 'bom'])->name('bom');
    Route::post('/bom', [BomController::class, 'storeBom'])->name('bom.store');
    Route::put('/bom/{id}', [BomController::class, 'updateBom'])->name('bom.update');
    Route::delete('/bom/{id}', [BomController::class, 'deleteBom'])->name('bom.delete');

    // 4. Production Logs
    Route::get('/production', [ProductionController::class, 'production'])->name('production');
    Route::post('/production', [ProductionController::class, 'logProduction'])->name('production.store');
    Route::put('/production/{id}', [ProductionController::class, 'updateProductionLog'])->name('production.update');
    Route::delete('/production/{id}', [ProductionController::class, 'deleteProductionLog'])->name('production.delete');

    // 5. Clients & Plants
    Route::get('/clients', [ClientController::class, 'clients'])->name('clients');
    Route::post('/clients', [ClientController::class, 'storeClient'])->name('clients.store');
    Route::put('/clients/{id}', [ClientController::class, 'updateClient'])->name('clients.update');
    Route::delete('/clients/{id}', [ClientController::class, 'deleteClient'])->name('clients.delete');
    Route::post('/clients/plants', [ClientController::class, 'storePlant'])->name('clients.plants.store');
    Route::put('/clients/plants/{id}', [ClientController::class, 'updatePlant'])->name('clients.plants.update');
    Route::delete('/clients/plants/{id}', [ClientController::class, 'deletePlant'])->name('clients.plants.delete');
    Route::get('/clients/{id}/ledger', [ClientController::class, 'clientLedger'])->name('clients.ledger');
    Route::get('/clients/{id}/ledger/pdf', [ClientController::class, 'downloadClientLedgerPdf'])->name('clients.ledger.pdf');

    // 5.5 Sales Orders / Order Management
    Route::get('/orders', [OrderController::class, 'orders'])->name('orders');
    Route::post('/orders', [OrderController::class, 'storeOrder'])->name('orders.store');
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder'])->name('orders.update');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateOrderStatus'])->name('orders.updateStatus');
    Route::post('/orders/{id}/convert-to-challan', [OrderController::class, 'convertOrderToChallan'])->name('orders.convertToChallan');
    Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder'])->name('orders.delete');

    // 6. Invoices & Billing Page
    Route::get('/invoices', [InvoiceController::class, 'invoices'])->name('invoices');
    Route::post('/invoices/generate', [InvoiceController::class, 'generateCustomInvoice'])->name('invoice.generate');
    Route::post('/invoices/{id}/pay', [InvoiceController::class, 'payInvoice'])->name('invoice.pay');
    Route::post('/invoices/{id}/record-payment', [InvoiceController::class, 'recordInvoicePayment'])->name('invoice.record-payment');
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'printInvoice'])->name('invoice.print');
    Route::get('/invoices/{id}/preview', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadInvoicePdf'])->name('invoice.download');
    Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendInvoiceEmail'])->name('invoice.send-email');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'deleteInvoice'])->name('invoice.delete');

    // 7. Purchase Ledger (Raw Materials, Machinery, Tools)
    Route::get('/purchases', [PurchaseController::class, 'purchases'])->name('purchases');
    Route::post('/purchases', [PurchaseController::class, 'storePurchase'])->name('purchases.store');
    Route::put('/purchases/{id}', [PurchaseController::class, 'updatePurchase'])->name('purchases.update');
    Route::delete('/purchases/{id}', [PurchaseController::class, 'deletePurchase'])->name('purchases.delete');
    Route::post('/purchases/{id}/record-payment', [PurchaseController::class, 'recordPurchasePayment'])->name('purchases.record-payment');

    // 8. Employees Directory
    Route::get('/employees', [EmployeeController::class, 'employees'])->name('employees');
    Route::post('/employees', [EmployeeController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{id}', [EmployeeController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'deleteEmployee'])->name('employees.delete');
    Route::post('/employees/payroll/pay', [EmployeeController::class, 'payPayroll'])->name('payroll.pay');

    // 9. Operational Expenses
    Route::get('/expenses', [ExpenseController::class, 'expenses'])->name('expenses');
    Route::post('/expenses', [ExpenseController::class, 'logExpense'])->name('expense.store');
    Route::put('/expenses/{id}', [ExpenseController::class, 'updateExpense'])->name('expense.update');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'deleteExpense'])->name('expense.delete');

    // 10. Reports & Export
    Route::get('/reports', [ReportController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Reset demonstration utility
    Route::post('/reset-data', [ProfileController::class, 'resetData'])->name('reset-data');

    // 11. Profile Management
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/business', [ProfileController::class, 'updateBusinessSettings'])->name('profile.business');
});
