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
        // 1. raw_materials
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_name');
            $table->string('material_category', 50)->nullable()->index();
            $table->string('specification', 255)->nullable();
            $table->string('unit'); // e.g. kg, liters, pcs
            $table->decimal('current_stock', 12, 4)->default(0.0000);
            $table->decimal('safety_threshold', 12, 4)->default(0.0000);
            $table->decimal('average_purchase_price', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('sku', 100)->nullable()->unique();
            $table->string('hsn_code', 50)->nullable();
            $table->string('uom', 50)->default('piece'); // 'piece' or 'kg'
            $table->decimal('unit_weight_kg', 8, 3)->default(0.000);
            $table->integer('current_stock')->default(0);
            $table->integer('safety_threshold')->default(10);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->decimal('price_per_kg', 10, 2)->nullable();
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->boolean('alerts_enabled')->default(true);
            $table->timestamps();
        });

        // 3. bill_of_materials (BOM)
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->decimal('required_quantity', 12, 4);
            $table->decimal('waste_percentage', 5, 2)->default(0.00);
            $table->decimal('unit_rate', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'raw_material_id']);
        });

        // 4. production_logs
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity_manufactured');
            $table->integer('quantity_rejected')->default(0);
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->date('production_date');
            $table->timestamps();

            $table->index('product_id');
            $table->index('recorded_by');
            $table->index('production_date');
        });

        // 5. clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Balaji Wafers');
            $table->string('client_email')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('corporate_address')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. client_plants
        Schema::create('client_plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('plant_name'); // e.g. Rajkot, Valsad, Indore
            $table->text('shipping_address')->nullable();
            $table->string('state')->default('Gujarat');
            $table->string('gst_number')->nullable();
            $table->string('email')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('client_id');
        });

        // 7. sales_orders
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('po_number')->nullable();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('plant_id')->nullable()->constrained('client_plants')->onDelete('set null');
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['pending', 'in_production', 'ready_for_dispatch', 'dispatched', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_date', 'status'], 'idx_orders_date_status');
        });

        // 8. sales_order_items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('billing_uom')->default('Pcs');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        // 9. invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('client_plants')->nullOnDelete();
            $table->string('invoice_mode')->default('finished_goods');
            $table->string('custom_client_name')->nullable();
            $table->decimal('custom_gst_rate', 5, 2)->nullable();
            $table->string('custom_buyer_gstin')->nullable();
            $table->string('invoice_number')->unique();
            $table->string('vehicle_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('total_taxable_value', 12, 2)->default(0.00);
            $table->decimal('cgst', 12, 2)->default(0.00);
            $table->decimal('sgst', 12, 2)->default(0.00);
            $table->decimal('igst', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index('plant_id');
            $table->index('invoice_number');
            $table->index(['invoice_date', 'payment_status'], 'idx_invoices_date_status');
        });

        // 10. invoice_items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('item_type')->default('product');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
            $table->string('item_name')->nullable();
            $table->string('billing_uom')->default('Pcs');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('total_price', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('product_id');
            $table->index('raw_material_id');
        });

        // 11. staff_profiles
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('full_name');
            $table->string('mobile_number', 20)->nullable();
            $table->string('wage_type', 50)->default('per-day');
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->decimal('piece_rate_per_unit', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
        });

        // 12. labor_logs
        Schema::create('labor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
            $table->foreignId('production_log_id')->constrained('production_logs')->onDelete('cascade');
            $table->integer('units_completed');
            $table->decimal('calculated_payout', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();

            $table->index('staff_profile_id');
            $table->index('production_log_id');
        });

        // 13. expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_category', 100);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('expense_category');
            $table->index('expense_date');
            $table->index(['expense_date', 'expense_category'], 'idx_expenses_date_cat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('labor_logs');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('client_plants');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('products');
        Schema::dropIfExists('raw_materials');
    }
};
