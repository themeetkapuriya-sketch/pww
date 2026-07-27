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
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->string('billing_uom')->default('Pcs')->after('product_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('billing_uom')->default('Pcs')->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('billing_uom');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('billing_uom');
        });
    }
};
