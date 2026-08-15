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
        // 1. purchases
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->nullable();
            $table->string('vendor_name');
            $table->string('purchase_type', 100)->default('raw_material');
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

            $table->index(['purchase_date'], 'idx_purchases_date');
        });

        // 2. payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['received', 'paid']); // 'received' = sales collection, 'paid' = vendor payout
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('client_plants')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'cheque', 'upi', 'cash'])->default('bank_transfer');
            $table->enum('account_type', ['bank', 'cash'])->default('bank');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('payment_number');
            $table->index('invoice_id');
            $table->index('purchase_id');
            $table->index('client_id');
            $table->index('plant_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('purchases');
    }
};
