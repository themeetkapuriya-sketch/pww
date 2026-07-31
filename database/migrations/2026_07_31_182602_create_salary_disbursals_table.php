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
        Schema::create('salary_disbursals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
            $table->string('month_year', 7); // e.g. "2026-07"
            $table->enum('wage_type', ['fixed', 'per-day'])->default('per-day');
            $table->decimal('rate_amount', 12, 2)->default(0);
            $table->decimal('days_present', 5, 1)->default(0);
            $table->decimal('total_salary', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->default('Cash'); // Cash, Bank Transfer, UPI
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_profile_id', 'month_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_disbursals');
    }
};
