<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->nullable();
            $table->string('vendor_name');
            $table->enum('purchase_type', ['raw_material', 'machinery', 'supplies'])->default('raw_material');
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 12, 4)->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->decimal('gst_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('paid');
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->date('purchase_date');
            $table->timestamps();
        });

        // Add foreign key constraint to payments.purchase_id
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('purchase_id')->references('id')->on('purchases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
        });

        Schema::dropIfExists('purchases');
    }
};
