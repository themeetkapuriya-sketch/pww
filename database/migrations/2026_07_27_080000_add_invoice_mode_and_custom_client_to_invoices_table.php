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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('plant_id')->nullable()->change();
            $table->string('invoice_mode')->default('finished_goods')->after('plant_id');
            $table->string('custom_client_name')->nullable()->after('invoice_mode');
            $table->decimal('custom_gst_rate', 5, 2)->nullable()->after('custom_client_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_mode', 'custom_client_name', 'custom_gst_rate']);
            $table->unsignedBigInteger('plant_id')->nullable(false)->change();
        });
    }
};
