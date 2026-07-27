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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->string('item_type')->default('product')->after('invoice_id');
            $table->foreignId('raw_material_id')->nullable()->after('product_id')->constrained('raw_materials')->nullOnDelete();
            $table->string('item_name')->nullable()->after('raw_material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']);
            $table->dropColumn(['item_type', 'raw_material_id', 'item_name']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
