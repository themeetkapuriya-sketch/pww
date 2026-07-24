<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Gated ERP routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('overview');
    });

    // 1. Dashboard Overview
    Route::get('/overview', [ErpController::class, 'overview'])->name('overview');

    // 2. Inventory Management
    Route::get('/inventory', [ErpController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/materials', [ErpController::class, 'storeRawMaterial'])->name('inventory.materials.store');
    Route::put('/inventory/materials/{id}', [ErpController::class, 'updateRawMaterial'])->name('inventory.materials.update');
    Route::delete('/inventory/materials/{id}', [ErpController::class, 'deleteRawMaterial'])->name('inventory.materials.delete');
    Route::post('/inventory/goods', [ErpController::class, 'storeFinishedGood'])->name('inventory.goods.store');
    Route::put('/inventory/goods/{id}', [ErpController::class, 'updateFinishedGood'])->name('inventory.goods.update');
    Route::delete('/inventory/goods/{id}', [ErpController::class, 'deleteFinishedGood'])->name('inventory.goods.delete');

    // 3. Bill of Materials
    Route::get('/bom', [ErpController::class, 'bom'])->name('bom');
    Route::post('/bom', [ErpController::class, 'storeBom'])->name('bom.store');

    // 4. Production Logs
    Route::get('/production', [ErpController::class, 'production'])->name('production');
    Route::post('/production', [ErpController::class, 'logProduction'])->name('production.store');

    // 5. Clients & Plants
    Route::get('/clients', [ErpController::class, 'clients'])->name('clients');
    Route::post('/clients', [ErpController::class, 'storeClient'])->name('clients.store');
    Route::put('/clients/{id}', [ErpController::class, 'updateClient'])->name('clients.update');
    Route::delete('/clients/{id}', [ErpController::class, 'deleteClient'])->name('clients.delete');
    Route::post('/clients/plants', [ErpController::class, 'storePlant'])->name('clients.plants.store');
    Route::put('/clients/plants/{id}', [ErpController::class, 'updatePlant'])->name('clients.plants.update');
    Route::delete('/clients/plants/{id}', [ErpController::class, 'deletePlant'])->name('clients.plants.delete');
    Route::get('/clients/{id}/ledger', [ErpController::class, 'clientLedger'])->name('clients.ledger');
    // 5.5 Sales Orders / Order Management
    Route::get('/orders', [ErpController::class, 'orders'])->name('orders');
    Route::post('/orders', [ErpController::class, 'storeOrder'])->name('orders.store');
    Route::patch('/orders/{id}/status', [ErpController::class, 'updateOrderStatus'])->name('orders.updateStatus');
    Route::post('/orders/{id}/convert-to-challan', [ErpController::class, 'convertOrderToChallan'])->name('orders.convertToChallan');
    Route::delete('/orders/{id}', [ErpController::class, 'deleteOrder'])->name('orders.delete');

    // 6. Invoices & Billing Page
    Route::get('/invoices', [ErpController::class, 'invoices'])->name('invoices');
    Route::post('/invoices/generate', [ErpController::class, 'generateCustomInvoice'])->name('invoice.generate');
    Route::post('/invoices/{id}/pay', [ErpController::class, 'payInvoice'])->name('invoice.pay');
    Route::post('/invoices/{id}/record-payment', [ErpController::class, 'recordInvoicePayment'])->name('invoice.record-payment');
    Route::get('/invoices/{id}/print', [ErpController::class, 'printInvoice'])->name('invoice.print');
    Route::get('/invoices/{id}/preview', [ErpController::class, 'previewInvoice'])->name('invoice.preview');
    Route::get('/invoices/{id}/download', [ErpController::class, 'downloadInvoicePdf'])->name('invoice.download');
    Route::post('/invoices/{id}/send-email', [ErpController::class, 'sendInvoiceEmail'])->name('invoice.send-email');
    Route::delete('/invoices/{id}', [ErpController::class, 'deleteInvoice'])->name('invoice.delete');

    // 7. Purchase Ledger (Raw Materials, Machinery, Tools)
    Route::get('/purchases', [ErpController::class, 'purchases'])->name('purchases');
    Route::post('/purchases', [ErpController::class, 'storePurchase'])->name('purchases.store');
    Route::post('/purchases/{id}/record-payment', [ErpController::class, 'recordPurchasePayment'])->name('purchases.record-payment');

    // 8. Employees Directory
    Route::get('/employees', [ErpController::class, 'employees'])->name('employees');
    Route::post('/employees', [ErpController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{id}', [ErpController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [ErpController::class, 'deleteEmployee'])->name('employees.delete');

    // 9. Operational Expenses
    Route::get('/expenses', [ErpController::class, 'expenses'])->name('expenses');
    Route::post('/expenses', [ErpController::class, 'logExpense'])->name('expense.store');

    // 10. Reports & Export
    Route::get('/reports', [ErpController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [ErpController::class, 'exportCsv'])->name('reports.export');

    // Reset demonstration utility
    Route::post('/reset-data', [ErpController::class, 'resetData'])->name('reset-data');

    // 11. Profile Management
    Route::get('/profile', [ErpController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ErpController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ErpController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/business', [ErpController::class, 'updateBusinessSettings'])->name('profile.business');
});

