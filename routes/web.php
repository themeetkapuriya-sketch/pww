<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\RawMaterialController;
use App\Http\Controllers\Payroll\EmployeeController;
use App\Http\Controllers\Production\BomController;
use App\Http\Controllers\Production\ProductionController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Purchases\PurchaseController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\AutoBackupCheckMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__.'/auth.php';

// Gated ERP routes (Requires Auth & AutoBackup Check)
Route::middleware(['auth', AutoBackupCheckMiddleware::class])->group(function () {
    Route::get('/', function () {
        return redirect()->route('overview');
    });

    // 1. Dashboard Overview
    Route::get('/overview', [OverviewController::class, 'overview'])->name('overview');

    // 2. Raw Materials & Products Catalog Management
    Route::get('/rawmaterial', [RawMaterialController::class, 'index'])->name('rawmaterial');
    Route::get('/product', [ProductController::class, 'index'])->name('product');
    Route::get('/inventory', function (Request $request) {
        if ($request->input('tab') === 'materials') {
            return redirect()->route('rawmaterial');
        }

        return redirect()->route('product');
    })->name('inventory');

    Route::post('/inventory/materials', [RawMaterialController::class, 'store'])->name('inventory.materials.store');
    Route::put('/inventory/materials/{id}', [RawMaterialController::class, 'update'])->name('inventory.materials.update');
    Route::post('/inventory/materials/{id}/adjust', [RawMaterialController::class, 'adjustStock'])->name('inventory.materials.adjust');
    Route::delete('/inventory/materials/{id}', [RawMaterialController::class, 'destroy'])->name('inventory.materials.delete');
    Route::post('/inventory/goods', [ProductController::class, 'store'])->name('inventory.goods.store');
    Route::put('/inventory/goods/{id}', [ProductController::class, 'update'])->name('inventory.goods.update');
    Route::post('/inventory/goods/{id}/adjust', [ProductController::class, 'adjustStock'])->name('inventory.goods.adjust');
    Route::delete('/inventory/goods/{id}', [ProductController::class, 'destroy'])->name('inventory.goods.delete');

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
    Route::get('/orders/{id}/details', [OrderController::class, 'orderDetails'])->name('orders.details');
    Route::post('/orders', [OrderController::class, 'storeOrder'])->name('orders.store');
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder'])->name('orders.update');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateOrderStatus'])->name('orders.updateStatus');
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
    Route::get('/invoices/{id}/export-eway-json', [InvoiceController::class, 'downloadEwayJson'])->name('invoice.exportEwayJson');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'deleteInvoice'])->name('invoice.delete');

    // 7. Purchase Ledger (Raw Materials, Machinery, Tools)
    Route::get('/purchases', [PurchaseController::class, 'purchases'])->name('purchases');
    Route::post('/purchases', [PurchaseController::class, 'storePurchase'])->name('purchases.store');
    Route::put('/purchases/{id}', [PurchaseController::class, 'updatePurchase'])->name('purchases.update');
    Route::delete('/purchases/{id}', [PurchaseController::class, 'deletePurchase'])->name('purchases.delete');
    Route::post('/purchases/{id}/record-payment', [PurchaseController::class, 'recordPurchasePayment'])->name('purchases.record-payment');

    // 8. Employees Directory & Attendance / Payroll
    Route::get('/employees', [EmployeeController::class, 'employees'])->name('employees');
    Route::post('/employees', [EmployeeController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{id}/statement', [EmployeeController::class, 'getEmployeeStatement'])->name('employees.statement');
    Route::put('/employees/{id}', [EmployeeController::class, 'updateEmployee'])->name('employees.update');
    Route::post('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
    Route::delete('/employees/{id}', [EmployeeController::class, 'deleteEmployee'])->name('employees.delete');
    Route::post('/employees/attendance', [EmployeeController::class, 'storeAttendance'])->name('employees.attendance.store');
    Route::get('/employees/attendance/summary', [EmployeeController::class, 'getMonthlySummary'])->name('employees.attendance.summary');
    Route::post('/employees/salary/payment', [EmployeeController::class, 'paySalary'])->name('employees.salary.payment');
    Route::delete('/employees/salary/payment/{id}', [EmployeeController::class, 'deletePayment'])->name('employees.salary.delete');
    Route::post('/employees/advance', [EmployeeController::class, 'storeAdvance'])->name('employees.advance.store');
    Route::delete('/employees/advance/{id}', [EmployeeController::class, 'deleteAdvance'])->name('employees.advance.delete');

    // 9. Operational Expenses
    Route::get('/expenses', [ExpenseController::class, 'expenses'])->name('expenses');
    Route::post('/expenses', [ExpenseController::class, 'logExpense'])->name('expense.store');
    Route::put('/expenses/{id}', [ExpenseController::class, 'updateExpense'])->name('expense.update');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'deleteExpense'])->name('expense.delete');

    // 10. Reports & Export
    Route::get('/reports', [ReportController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // 11. Profile Management
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/business', [ProfileController::class, 'updateBusinessSettings'])->name('profile.business');

    // 12. Backup & Restore System
    Route::middleware('permission:backups_settings_manage')->group(function () {
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/list-json', [BackupController::class, 'listJson'])->name('backup.listJson');
        Route::get('/backup/full', [BackupController::class, 'downloadFull'])->name('backup.full');
        Route::post('/backup/filtered', [BackupController::class, 'downloadFiltered'])->name('backup.filtered');
        Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
        Route::get('/backup/download/{filename}', [BackupController::class, 'downloadFile'])->name('backup.downloadFile');
        Route::delete('/backup/delete/{filename}', [BackupController::class, 'deleteFile'])->name('backup.deleteFile');
        Route::post('/backup/optimize', [BackupController::class, 'optimizeDatabase'])->name('backup.optimize');
        Route::post('/backup/reset-system', [BackupController::class, 'resetSystem'])->name('backup.reset');

        // 13. Unified System Settings & User Access Hub
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/modules', [SettingsController::class, 'updateModuleToggles'])->name('settings.modules');
        Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
        Route::put('/settings/users/{id}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
        Route::post('/settings/users/{id}/approve', [SettingsController::class, 'approveUser'])->name('settings.users.approve');
        Route::post('/settings/users/{id}/toggle-status', [SettingsController::class, 'toggleUserStatus'])->name('settings.users.toggle-status');
        Route::delete('/settings/users/{id}', [SettingsController::class, 'deleteUser'])->name('settings.users.delete');

        Route::post('/settings/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
        Route::post('/settings/roles/toggle-permission', [SettingsController::class, 'toggleRolePermission'])->name('settings.roles.toggle-permission');
        Route::post('/settings/roles/{slug}/toggle-status', [SettingsController::class, 'toggleRoleStatus'])->name('settings.roles.toggle-status');
        Route::delete('/settings/roles/{id}', [SettingsController::class, 'deleteRole'])->name('settings.roles.delete');
        Route::post('/settings/roles/permissions-matrix', [SettingsController::class, 'saveRolePermissionsMatrix'])->name('settings.roles.matrix');
        Route::post('/settings/navigation-modules', [SettingsController::class, 'storeModule'])->name('settings.navigation-modules.store');

        Route::post('/settings/business', [SettingsController::class, 'updateBusinessProfile'])->name('settings.business');
        Route::post('/settings/bank', [SettingsController::class, 'updateBankDefaults'])->name('settings.bank');
        Route::post('/settings/serials', [SettingsController::class, 'updateSerialSettings'])->name('settings.serials');
        Route::post('/settings/financial', [SettingsController::class, 'updateFinancialSettings'])->name('settings.financial');
        Route::post('/settings/financial/toggle-lock', [SettingsController::class, 'toggleFinancialYearLock'])->name('settings.financial.toggle_lock');
        Route::post('/settings/email', [SettingsController::class, 'updateEmailSettings'])->name('settings.email');
        Route::post('/settings/email/test', [SettingsController::class, 'sendTestEmail'])->name('settings.email.test');
        Route::post('/settings/security', [SettingsController::class, 'updateSecuritySettings'])->name('settings.security');
        Route::post('/settings/categories/store', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
        Route::post('/settings/categories/update', [SettingsController::class, 'updateCategory'])->name('settings.categories.update');
        Route::post('/settings/categories/delete', [SettingsController::class, 'deleteCategory'])->name('settings.categories.delete');
        Route::post('/settings/resync-cache', [SettingsController::class, 'resyncCache'])->name('settings.resync');
        Route::post('/settings/prune-system', [SettingsController::class, 'pruneSystemLogs'])->name('settings.prune');
    });

    // 14. Super-Admin User Activity Audit Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'exportCsv'])->name('activity-logs.export');
    Route::post('/activity-logs/clear', [ActivityLogController::class, 'clearLogs'])->name('activity-logs.clear');
});
