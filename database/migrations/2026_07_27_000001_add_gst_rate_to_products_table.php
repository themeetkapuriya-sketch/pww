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
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'gst_rate')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('gst_rate', 5, 2)->default(18.00)->after('current_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'gst_rate')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('gst_rate');
            });
        }
    }
};
