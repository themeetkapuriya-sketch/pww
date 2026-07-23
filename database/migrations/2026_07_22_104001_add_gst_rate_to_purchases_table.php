<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->default(18.00)->after('total_amount');
            $table->decimal('gst_amount', 12, 2)->default(0.00)->after('gst_rate');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['gst_rate', 'gst_amount']);
        });
    }
};
