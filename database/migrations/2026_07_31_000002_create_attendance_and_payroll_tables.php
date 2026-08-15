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
        // 1. attendance_records
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'half_day', 'absent'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_profile_id', 'date']);
            $table->index(['date', 'staff_profile_id'], 'idx_attendance_date_staff');
        });

        // 2. salary_payments
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
            $table->string('month_year', 7); // e.g. "2026-07"
            $table->enum('wage_type', ['fixed', 'per-day'])->default('per-day');
            $table->decimal('rate_amount', 12, 2)->default(0);
            $table->decimal('days_present', 5, 1)->default(0);
            $table->decimal('total_salary', 12, 2)->default(0);
            $table->decimal('advance_deduction', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->default('Cash'); // Cash, Bank Transfer, UPI
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_profile_id', 'month_year']);
            $table->index(['month_year', 'staff_profile_id'], 'idx_payments_month_staff');
        });

        // 3. salary_advances
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

            $table->index(['status', 'advance_date', 'staff_profile_id'], 'idx_advances_status_date_staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('attendance_records');
    }
};
