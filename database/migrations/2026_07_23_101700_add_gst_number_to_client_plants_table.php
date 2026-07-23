<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_plants', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('client_plants', function (Blueprint $table) {
            $table->dropColumn('gst_number');
        });
    }
};
