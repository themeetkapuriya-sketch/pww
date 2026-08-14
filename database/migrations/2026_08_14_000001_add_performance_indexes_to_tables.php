<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $addIndexSafely = function (string $tableName, string $indexName, array $columns) {
            if (! Schema::hasTable($tableName)) {
                return;
            }
            try {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Index already exists, ignore
            }
        };

        // 1. salary_advances table indexes
        $addIndexSafely('salary_advances', 'idx_advances_status_date_staff', ['status', 'advance_date', 'staff_profile_id']);

        // 2. salary_payments table indexes
        $addIndexSafely('salary_payments', 'idx_payments_month_staff', ['month_year', 'staff_profile_id']);

        // 3. attendance_records table indexes
        $addIndexSafely('attendance_records', 'idx_attendance_date_staff', ['date', 'staff_profile_id']);

        // 4. invoices table indexes
        $addIndexSafely('invoices', 'idx_invoices_date_status', ['invoice_date', 'payment_status']);

        // 5. purchases table indexes
        $addIndexSafely('purchases', 'idx_purchases_date', ['purchase_date']);

        // 6. expenses table indexes
        $addIndexSafely('expenses', 'idx_expenses_date_cat', ['expense_date', 'expense_category']);

        // 7. sales_orders table indexes
        $addIndexSafely('sales_orders', 'idx_orders_date_status', ['order_date', 'status']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexSafely = function (string $tableName, string $indexName) {
            if (! Schema::hasTable($tableName)) {
                return;
            }
            try {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
                // Index doesn't exist, ignore
            }
        };

        $dropIndexSafely('salary_advances', 'idx_advances_status_date_staff');
        $dropIndexSafely('salary_payments', 'idx_payments_month_staff');
        $dropIndexSafely('attendance_records', 'idx_attendance_date_staff');
        $dropIndexSafely('invoices', 'idx_invoices_date_status');
        $dropIndexSafely('purchases', 'idx_purchases_date');
        $dropIndexSafely('expenses', 'idx_expenses_date_cat');
        $dropIndexSafely('sales_orders', 'idx_orders_date_status');
    }
};
