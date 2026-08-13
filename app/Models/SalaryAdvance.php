<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'advance_date',
        'amount',
        'payment_method',
        'status',
        'expense_id',
        'salary_disbursal_id',
        'notes',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function salaryDisbursal()
    {
        return $this->belongsTo(SalaryDisbursal::class, 'salary_disbursal_id');
    }
}
