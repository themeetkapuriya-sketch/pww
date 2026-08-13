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
        if (! Schema::hasColumn('invoices', 'sales_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'sales_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['sales_order_id']);
                $table->dropColumn('sales_order_id');
            });
        }
    }
};
