<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN purchase_type VARCHAR(100) NOT NULL DEFAULT 'raw_material'");
        } catch (\Exception $e) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('purchase_type', 100)->default('raw_material')->change();
            });
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN purchase_type VARCHAR(100) NOT NULL DEFAULT 'raw_material'");
        } catch (\Exception $e) {
            //
        }
    }
};
