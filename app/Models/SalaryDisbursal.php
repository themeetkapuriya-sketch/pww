<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDisbursal extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'month_year',
        'wage_type',
        'rate_amount',
        'days_present',
        'total_salary',
        'advance_deduction',
        'status',
        'payment_date',
        'payment_method',
        'expense_id',
        'notes',
    ];

    protected $casts = [
        'rate_amount' => 'decimal:2',
        'days_present' => 'decimal:1',
        'total_salary' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
