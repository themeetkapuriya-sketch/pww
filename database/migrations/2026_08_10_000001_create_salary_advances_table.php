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
        if (! Schema::hasTable('salary_advances')) {
            Schema::create('salary_advances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
                $table->date('advance_date');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('payment_method')->default('Cash'); // Cash, Bank Transfer, UPI, Cheque
                $table->enum('status', ['pending', 'deducted'])->default('pending');
                $table->unsignedBigInteger('expense_id')->nullable();
                $table->unsignedBigInteger('salary_payment_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('salary_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_payments', 'advance_deduction')) {
                $table->decimal('advance_deduction', 12, 2)->default(0)->after('total_salary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            if (Schema::hasColumn('salary_payments', 'advance_deduction')) {
                $table->dropColumn('advance_deduction');
            }
        });

        Schema::dropIfExists('salary_advances');
    }
};
